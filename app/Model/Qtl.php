<?php
App::uses('AppModel', 'Model');

class Qtl extends AppModel {
	public $useTable = false;
	
	private static $BP_INDEX = 'wp10qtls';
	private static $BP_TYPE = 'qtl';
	private static $BP_BULK_INDEX = 'wp10bulkqtls';
	private static $BP_BULK_TYPE = 'bulkqtl';
	
	//private static $fdrFields = array('pv','qv');
	private static $fdrFields = array('FDR');
	
	private static $SORT_CRITERIA = array(
		'cell_type' => array('cell_type'),
		'qtl_source' => array('qtl_source'),
		'qtl_id' => array('gene_id'),
		'SNP' => array('snp_id'),
		'altAF' => array('altAF'),
		'MAF' => array('MAF'),
		'an_group' => array('an_group'),
		'FDR' => array('metrics' => 'FDR'),
		'beta' => array('metrics' => 'beta'),
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
	
	protected $client;
	
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
							$cStartFacetName = (isset($conditions['coordinates_match_snps']) && $conditions['coordinates_match_snps'] > 0) ? 'pos' : 'gene_start';
							$andFilters[] = array(
								'range' => array(
									$cStartFacetName => array(
										'gte' => $value
									)
								)
							);
							break;
						case "chromosome_end":
							$cEndFacetName = (isset($conditions['coordinates_match_snps']) && $conditions['coordinates_match_snps'] > 0) ? 'pos' : 'gene_end';
							$andFilters[] = array(
								'range' => array(
									$cEndFacetName => array(
										'lte' => $value
									)
								)
							);
							break;
						# To be redesigned
						case "gene":
							$geneMultiMatch = array(
								'query' => $value,
								'type' => 'phrase_prefix',
								'fields' => array('gene_name','ensemblGeneId')
							);
							if(isset($conditions['fuzzy_gene_search']) && $conditions['fuzzy_gene_search'] > 0) {
								$geneMultiMatch['fuzziness'] = 'AUTO';
							}

							$andQueries[] = array(
								'multi_match' => $geneMultiMatch
							);
							break;
						case "SNP":
							$andFilters[] = array(
								'bool' => array(
									'should' => array(
										array(
											'term' => array(
												'rsId' => $value
											)
										),
										array(
											'term' => array(
												'snp_id' => $value
											)
										)
									)
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
							if(is_array($value)) {
								$array_value = &$value;
							} else {
								$array_value = [ $value ];
							}
							$andFilters[] = array(
								'terms' => array(
									'qtl_source' => $array_value
								)
							);
							break;
						case "qtl_id":
							$andFilters[] = array(
								'term' => array(
									'gene_id' => $value
								)
							);
							break;
						case "cell_type":
							if(is_array($value)) {
								$array_value = &$value;
							} else {
								$array_value = [ $value ];
							}
							$andFilters[] = array(
								'terms' => array(
									'cell_type' => $array_value
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
					foreach(self::$SORT_CRITERIA[$orderKey] as $key => $attr) {
						if(is_string($key)) {
							$sortKey = $key . '.' . $attr;
						} else {
							$sortKey = $attr;
						}
						$sortCriteria[] = array(
							$sortKey => array(
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
		
		// Over this threshold, scrolled search
		
		return ($limit > 100) ? $this->scrolled_search($q = $query) : $this->search($q = $query,$size = $limit,$offset = ($page - 1)*$limit);
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

	private static $SCROLL_TIME = '30s';
	
	protected function getIndex() {
		return self::$BP_INDEX;
	}
	
	protected function getTypeMapping() {
		return self::$BP_TYPE;
	}
	
	public function scrolled_search($q = null) {
		
		$searchParams = array(
			'scroll' => self::$SCROLL_TIME,
			'index' => $this->getIndex(),
			'type' => $this->getTypeMapping(),
			'size' => 100
		);
		
		if(! empty($q)) {
			$searchParams['body'] = $q;
		}
		
		$res = $this->client->search($searchParams);
		return $res;
	}

	public function next_scrolled_result(&$doc) {
		if(isset($doc['_scroll_id'])) {
			return $this->client->scroll(array(
				'scroll_id' => $doc['_scroll_id'],
				'scroll' => self::$SCROLL_TIME
			));
		} else {
			return array(
				'hits' => array(
					'hits' => array()
				)
			);
		}
	}

	public function search($q = null,$size = 40,$offset = null) {
		
		$searchParams = array(
			'index' => $this->getIndex(),
			'type' => $this->getTypeMapping(),
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
			'index' => $this->getIndex(),
			'type' => $this->getTypeMapping()
		);
		
		if(! empty($q)) {
			$countParams['body'] = $q;
		}
		
		$res = $this->client->count($countParams);
		return $res;
	}
	
	protected $chromosomes;
	
	public function availableChromosomes() {
		if(!isset($this->chromosomes)) {
			$chroQuery = array(
				'aggs' => array(
					'chros' => array(
						'terms' => array(
							'field' => 'gene_chrom',
							'size' => 0
						)
					)
				)
			);
			#$chroJson = '{
			#	"aggs": {
			#		"chros": {
			#			"terms": {
			#				"field": "gene_chrom",
			#				"size": 0
			#			}
			#		}
			#	}
			#}';
			$chromRes = $this->search($q = $chroQuery);
			$chromosomes = array_column($chromRes['aggregations']['chros']['buckets'],'key');
			sort($chromosomes);
			$this->chromosomes = &$chromosomes;
		}
		
		return $this->chromosomes;
	}
	
	public function fetchQTLs($cell_type,$qtl_source,$qtl_id) {
		$mustArray = array();
		if($cell_type != null) {
			$cell_type_search_operand = is_array($cell_type) ? 'terms' : 'term';
			$mustArray[] = array(
				$cell_type_search_operand => array(
					'cell_type' => &$cell_type
				)
			);
		}

		if($qtl_source != null) {
			$qtl_source_search_operand = is_array($qtl_source) ? 'terms' : 'term';
			$mustArray[] = array(
				$qtl_source_search_operand => array(
					'qtl_source' => &$qtl_source
				)
			);
		}
		
		if(is_array($qtl_id)) {
			$qtl_id_keys = &$qtl_id;
		} else {
			$qtl_id_keys = [ $qtl_id ];
		}
		$shouldArray = array(
			array(
				'terms' => array(
					'gene_id' => &$qtl_id_keys
				)
			)
		);
		foreach($qtl_id_keys as &$qtl_id_key) {
			$shouldArray[] = array(
				'prefix' => array(
					'gene_id' => $qtl_id_key
				)
			);
		}
		$mustArray[] = array(
			'bool' => array(
				'should' => &$shouldArray
			)
		);
		
		//array(
		//	'prefix' => array(
		//		'gene_id' => &$qtl_id_keys
		//	)
		//);

		$searchParams = array(
			'index' => self::$BP_INDEX,
			'type' => self::$BP_TYPE,
			'size' => 100000,
			'body' => array(
				'query' => array(
					'filtered' => array(
						'filter' => array(
							'bool' => array(
								'must' => &$mustArray
							)
						)
					)
				)
			)
		);
		
		$res = $this->client->search($searchParams);

		return $res;
	}

	public function fetchBulkQtl($an_group,$cell_type,$qtl_source,$qtl_id) {
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
											'an_group' => $an_group
										)
									),
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
