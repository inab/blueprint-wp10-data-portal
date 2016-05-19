<?php
App::uses('Qtl', 'Model');

class Hypervariability extends Qtl {
	private static $BP_VARIABILITY_INDEX = 'wp10qtls_variability';
	private static $BP_VARIABILITY_TYPE = 'qtl_variability';
	
	private static $SORT_CRITERIA = array(
		'cell_type' => array('cell_type'),
		'hvar_source' => array('qtl_source'),
		'id' => array('hvar_id'),
		'CHR' => array('gene_chrom','gene_start','gene_end'),
		//'CHR' => array('gene_chrom',array('pos','gene_start'),'gene_end'),
		'METH_pos' => array('gene_chrom','pos'),
		'gene' => array('gene_name'),
		'ensembl_gene_id' => array('ensemblGeneId'),
		'array_probe' => array('probeId')
	);
	
	public function SortKeys() {
		return array_keys(self::$SORT_CRITERIA);
	}
	
	protected function getIndex() {
		return self::$BP_VARIABILITY_INDEX;
	}
	
	protected function getTypeMapping() {
		return self::$BP_VARIABILITY_TYPE;
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
								'bool' => array(
									'should' => array(
										'range' => array(
											'pos' => array(
												'gte' => $value
											)
										),
										'range' => array(
											'gene_start' => array(
												'gte' => $value
											)
										)
									)
								)
							);
							break;
						case "chromosome_end":
							$andFilters[] = array(
								'bool' => array(
									'should' => array(
										'range' => array(
											'pos' => array(
												'lte' => $value
											)
										),
										'range' => array(
											'gene_end' => array(
												'lte' => $value
											)
										)
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
						case "array_probe":
							$andFilters[] = array(
								'term' => array(
									'probeId' => $value
								)
							);
							break;
						case "hvar_source":
							$andFilters[] = array(
								'terms' => array(
									'qtl_source' => $value
								)
							);
							break;
						case "cell_type":
							$andFilters[] = array(
								'terms' => array(
									'cell_type' => $value
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
		}
		
		$sortCriteria = array();
		$scriptFields = array();
		if(! empty($order)) {
			foreach($order as $orderKey => $orderCriteria) {
				if(array_key_exists($orderKey,self::$SORT_CRITERIA)) {
					foreach(self::$SORT_CRITERIA[$orderKey] as $key => $attr) {
						if(is_string($key)) {
							$sortKey = $key . '.' . $attr;
						} else {
							$sortKey = &$attr;
						}
						
						// This idea does not work
						//if(is_array($sortKey)) {
						//	$newField = join('_',$sortKey);
						//	$scriptFields[] = array(
						//		$newField => array(
						//			'script' => 'knownKeys = (keys in doc); for(key: knownKeys) { return doc[key].value; }; nil; }',
						//			'params' => array(
						//				'keys' => $sortKey
						//			)
						//		)
						//	);
						//	$sortKey = $newField;
						//}
						
						$sortCriteria[] = array(
							$sortKey => array(
								'order' => $orderCriteria,
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
				$query['query']['filtered']['filter'] = &$andFilters[0];
			} else {
				$query['query']['filtered']['filter']['bool']['must'] = &$andFilters;
			}
		}
		
		if(! empty($andQueries)) {
			if(count($andQueries)==1) {
				$query['query']['filtered']['query'] = &$andQueries[0];
			} else {
				$query['query']['filtered']['query']['bool']['must'] = &$andQueries;
			}
		}
		
		if(! empty($scriptFields)) {
			$query['script_fields'] = &$scriptFields;
		}
		
		if(! empty($sortCriteria)) {
			$query['sort'] = &$sortCriteria;
		}
		
		return $query;
	}
	
	public function fetchVariability($cell_type,$hvar_source,$hvar_id) {
		$mustArray = array();
		if($cell_type != null) {
			$mustArray[] = array(
				'term' => array(
					'cell_type' => $cell_type
				)
			);
		}

		if($hvar_source != null) {
			$mustArray[] = array(
				'term' => array(
					'qtl_source' => $hvar_source
				)
			);
		}

		$hvar_id_key = is_array($hvar_id) ? 'terms' : 'term';
		$mustArray[] = 	array(
			$hvar_id_key => array(
				'hvar_id' => &$hvar_id
			)
		);

		$searchParams = array(
			'index' => self::$BP_VARIABILITY_INDEX,
			'type' => self::$BP_VARIABILITY_TYPE,
			'size' => 10*(is_array($hvar_id) ? count($hvar_id) : 1),
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

	public function fetchVariabilityChart($cell_type,$hvar_source,$hvar_id) {
		$varRes = $this->fetchVariability($cell_type,$hvar_source,$hvar_id);

		return ($varRes['hits']['total'] > 0) ? base64_decode($varRes['hits']['hits'][0]['_source']['associated_chart']) : null;
	}
}

