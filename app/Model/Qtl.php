<?php
App::uses('AppModel', 'Model');

class Qtl extends AppModel {
	public $useTable = false;
	
	private static $BP_INDEX = 'meqtls';
	private static $BP_TYPE = 'meqtls';
	
	private static $fdrFields = array('mon.fdr','neu.fdr','tcl.fdr');
	private static $DEFAULT_SORT_CRITERIA = array(
		'CHR' => 'asc',
		'start_position' => 'asc',
		'end_position' => 'asc'
	);
	
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
							$andFilters[] = array(
								'term' => array(
									'CHR' => $value
								)
							);
							break;
						case "chromosome_start":
							$andFilters[] = array(
								'range' => array(
									'start_position' => array(
										'gte' => $value
									)
								)
							);
							break;
						case "chromosome_end":
							$andFilters[] = array(
								'range' => array(
									'end_position' => array(
										'lte' => $value
									)
								)
							);
							break;
						case "gene":
							$andQueries[] = array(
								'match' => array(
									'UCSC_RefGene_Name' => $value
								)
							);
							break;
						case "SNP":
							$andFilters[] = array(
								'term' => array(
									'SNP' => $value
								)
							);
							break;
						case "array_probe":
							$andFilters[] = array(
								'term' => array(
									'meth.probe' => $value
								)
							);
							break;
						case "fdr_cutoff":
							$fdr_cutoff = $value;
							break;
						case "all_fdrs":
							$all_fdrs = $value;
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
				$sortCriteria[] = array(
					$orderKey => array(
						'order' => $orderCriteria
					)
				);
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
		
		return search($this->client,$q = $query,$size = $limit,$offset = ($page - 1)*$limit);
	}
	
	public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$query = $this->esQueryBuilder($conditions = $conditions);
		$result = count($this->client,$q = $query);
		
		return $result['count'];
	}
	
	public function search($c,$q = null,$size = 40,$offset = null) {
		
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
		
		$res = $c->search($searchParams);
		return $res;
	}

	public function count($c,$q = null) {
		
		$countParams = array(
			'index' => self::$BP_INDEX,
			'type' => self::$BP_TYPE
		);
		
		if(! empty($q)) {
			$searchParams['body'] = $q;
		}
		
		$res = $c->count($searchParams);
		return $res;
	}
}
?>
