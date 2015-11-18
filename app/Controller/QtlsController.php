<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
    public $client;
    public $chromosomes;
	
	private static $fdrFields = array('mon.fdr','neu.fdr','tcl.fdr');
	private static $DEFAULT_SORT_CRITERIA = array(
		array(
			'CHR' => array(
				'order' => 'asc'
			)
		),
		array(
			'start_position' => array(
				'order' => 'asc'
			)
		),
		array(
			'end_position' => array(
				'order' => 'asc'
			)
		)
	);
	
    public function beforeFilter(){
        parent::beforeFilter();
	$elasticsearchConfig = array();
	
	$hosts = Configure::read('elasticsearch');
	if($hosts !== null) {
		if(!is_array($hosts)) {
			$hosts = array($hosts);
		}
		$elasticsearchConfig['hosts'] = $hosts;
	}
        $this->client = new Elasticsearch\Client($elasticsearchConfig);
        $chroJson = '{
		"aggs": {
			"chros": {
				"terms": {
					"field": "CHR",
					"size": 0
				}
			}
		}
        }';
        $chromRes = $this->Qtl->search($this->client,$q = $chroJson);
        $chromosomes = array_column($chromRes['aggregations']['chros']['buckets'],'key');
	sort($chromosomes);
        $this->chromosomes = $chromosomes;
    }

    public function index(){
	if(isset($this->request->params['named']) && count($this->request->params['named']) > 0) {
		$params = $this->request->params['named'];
	} else if(isset($this->request->data['Qtl'])) {
		$params = $this->request->data['Qtl'];
	} else {
		$params = $this->request->data;
	}
	
	// Transform POST into GET
	// Inspect all the named parameters to apply the filters
	$filter = array();
	
	$andFilters = array();
	$andQueries = array();
	$sortCriteria = array();
	foreach($params as $param_name => $value) {
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
			$filter[$param_name] = $value;
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
	
	$sortCriteria = self::$DEFAULT_SORT_CRITERIA;
	
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
	
	$this->log($query,'debug');	
	$res = $this->Qtl->search($this->client,$q = $query);
	$this->set('res',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
    }
}

?>
