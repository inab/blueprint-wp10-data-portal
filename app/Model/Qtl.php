<?php
App::uses('AppModel', 'Model');

class Qtl extends AppModel {
	public $useTable = false;
	
	private static $BP_INDEX = 'wp10qtls';
	private static $BP_TYPE = 'qtl';
	private static $BP_BULK_INDEX = 'wp10bulkqtls';
	private static $BP_BULK_TYPE = 'bulkqtl';
	
	private static $fdrFields = array('pv','qv');
	
	private static $SORT_CRITERIA = array(
		'cell_type' => array('cell_type'),
		'qtl_source' => array('qtl_source'),
		'id' => array('gene_id'),
		'SNP' => array('snp_id'),
		'CHR' => array('gene_chrom','gene_start','gene_end'),
		'SNP_pos' => array('gene_chrom','pos'),
		'gene' => array('gene_name'),
		'ensembl_gene_id' => array('ensemblGeneId'),
		'ensembl_transcript_id' => array('ensemblTranscriptId'),
		'exon_number' => array('gene_chrom','gene_start','gene_end','exonNumber'),
		'histone' => array('gene_chrom','gene_start','gene_end','histone'),
		'array_probe' => array('probeId'),
		'pv' => array('pv'),
		'qv' => array('qv'),
		'F' => array('F')
	);
	
	private $client;
	
	public function SortKeys() {
		return array_keys(self::$SORT_CRITERIA);
	}
	
	protected function esQueryBuilder($conditions = null,$fields = null,$order = null) {
		$andFilters = array();
		$andQueries = array();
		$sortCriteria = array();
		
		if(! empty($conditions)) {
			foreach($conditions as $param_name => $value) {
				// Don't apply the default named parameters used for pagination
				if(!empty($value)) {
					switch($param_name) {
						case "chromosome":
							if($value != 'any') {
								$andFilters[] = array(
									'term' => array(
										'gene_chrom' => $value
									)
								);
							}
							break;
						case "chromosome_start":
							$andFilters[] = array(
								'range' => array(
									'gene_start' => array(
										'gte' => $value
									)
								)
							);
							break;
						case "chromosome_end":
							$andFilters[] = array(
								'range' => array(
									'gene_end' => array(
										'lte' => $value
									)
								)
							);
							break;
						# To be redesigned
						case "gene":
							$andQueries[] = array(
								'multi_match' => array(
									'query' => $value,
									'type' => 'phrase_prefix',
									'fields' => array('gene_name','ensemblGeneId')
								)
							);
							break;
						case "SNP":
							$andFilters[] = array(
								'term' => array(
									'snp_id' => $value
								)
							);
							break;
						case "array_probe":
							$andFilters[] = array(
								'term' => array(
									'probeId' => $value
								)
							);
							break;
						case "fdr_cutoff":
							$fdr_cutoff = $value;
							break;
						case "all_fdrs":
							$all_fdrs = $value;
							break;
						case "qtl_source":
							$andFilters[] = array(
								'terms' => array(
									'qtl_source' => $value
								)
							);
							break;
						default:
							if(!in_array($param_name, array('page','sort','direction','limit'))){
								// You may use a switch here to make special filters
								// like "between dates", "greater than", etc
								if($param_name == "search"){
								} else {
								}
							}
					}
					#$filter[$param_name] = $value;
				}
			}
			
			if(isset($fdr_cutoff) && strlen($fdr_cutoff)>0) {
				// At least, one of the fields should exist
				$cutoffFilters = array();
				$cutoffFiltersRange = array();
				
				$cutoffFields = array();
				
				foreach (self::$fdrFields as $fdrField) {
					$cutoffFields[] = array(
						'exists' => array(
							'field' => $fdrField
						)
					);
					
					$cutoffFilterRange = array(
						'range' => array(
							$fdrField => array(
								'lte' => $fdr_cutoff
							)
						)
					);
					
					$cutoffFiltersRange[] = $cutoffFilterRange;
					
					$cutoffFilters[] = array(
						'bool' => array(
							'should' => array(
								array(
									'missing' => array(
										'field' => $fdrField
									)
								),
								$cutoffFilterRange
							)
						)
					);
				}
				
				array_push($andFilters, array(
					'bool' => array(
						'should' => $cutoffFields
					)
				));
				
				if(isset($all_fdrs) && $all_fdrs > 0) {
					foreach($cutoffFilters as $cutoffFilter) {
						$andFilters[] = $cutoffFilter;
					}
				} else {
					$andFilters[] = array(
						'bool' => array(
							'should' => $cutoffFiltersRange
						)
					);
				}
			}
		}
		
		$sortCriteria = array();
		if(! empty($order)) {
			foreach($order as $orderKey => $orderCriteria) {
				if(array_key_exists($orderKey,self::$SORT_CRITERIA)) {
					foreach(self::$SORT_CRITERIA[$orderKey] as $attr) {
						$sortCriteria[] = array(
							$attr => array(
								'order' => $orderCriteria
							)
						);
					}
				}
			}
		}
		
		// Generating a query
		$query = array();
		if(! empty($andFilters)) {
			if(count($andFilters)==1) {
				$query['query']['filtered']['filter'] = $andFilters[0];
			} else {
				$query['query']['filtered']['filter']['bool']['must'] = $andFilters;
			}
		}
		
		if(! empty($andQueries)) {
			if(count($andQueries)==1) {
				$query['query']['filtered']['query'] = $andQueries[0];
			} else {
				$query['query']['filtered']['query']['bool']['must'] = $andQueries;
			}
		}
		
		if(! empty($sortCriteria)) {
			$query['sort'] = $sortCriteria;
		}
		
		return $query;
	}
	
	public function paginate($conditions, $fields, $order, $limit, $page = 1, $recursive = null, $extra = array()) {
		$query = $this->esQueryBuilder($conditions = $conditions, $fields = $fields, $order = $order);
		
		//$this->log($query,'debug');
		
		return $this->search($q = $query,$size = $limit,$offset = ($page - 1)*$limit);
	}
	
	public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$query = $this->esQueryBuilder($conditions = $conditions);
		$result = $this->count($q = $query);
		
		return $result['count'];
	}
	
	public function setupClient($elasticsearchConfig) {
		//$this->client = new Elasticsearch\Client($elasticsearchConfig);
		$this->client = Elasticsearch\ClientBuilder::fromConfig($elasticsearchConfig);
	}

	public function search($q = null,$size = 40,$offset = null) {
		
		$searchParams = array(
			'index' => self::$BP_INDEX,
			'type' => self::$BP_TYPE,
			'size' => $size
		);
		
		if(! empty($offset) && $offset > 0) {
			$searchParams['from'] = $offset;
		}
		
		if(! empty($q)) {
			$searchParams['body'] = $q;
		}
		
		$res = $this->client->search($searchParams);
		return $res;
	}

	public function count($q = null) {
		
		$countParams = array(
			'index' => self::$BP_INDEX,
			'type' => self::$BP_TYPE
		);
		
		if(! empty($q)) {
			$countParams['body'] = $q;
		}
		
		$res = $this->client->count($countParams);
		return $res;
	}

	public function fetchBulkQtl($cell_type,$qtl_source,$qtl_id) {
		$searchParams = array(
			'index' => self::$BP_BULK_INDEX,
			'type' => self::$BP_BULK_TYPE,
			'size' => 10,
			'body' => array(
				'query' => array(
					'filtered' => array(
						'filter' => array(
							'bool' => array(
								'must' => array(
									array(
										'term' => array(
											'cell_type' => $cell_type
										)
									),
									array(
										'term' => array(
											'qtl_source' => $qtl_source
										)
									),
									array(
										'term' => array(
											'gene_id' => $qtl_id
										)
									)
								)
							)
						)
					)
				)
			)
		);
		
		$res = $this->client->search($searchParams);

		return $res;
	}
}
?>
