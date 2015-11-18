<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
    public $client;
    public $chromosomes;
	
	private static $fdrFields = array('mon.fdr','neu.fdr','tcl.fdr');
	private static function __fdrAddShould(array &$cummul_array,array &$value) {
		foreach($value['bool']['should'][1] as $should) {
			$cummul_array[] = $should;
		}
		
		return $cummul_array;
	}
	
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
		
		$cutoffFields = array();
		
		foreach (self::$fdrFields as $fdrField) {
			$cutoffFields[] = array(
				'exists' => array(
					'field' => $fdrField
				)
			);
			
			$cutoffFilters[] = array(
				'bool' => array(
					'should' => array(
						array(
							'missing' => array(
								'field' => $fdrField
							)
						),
						array(
							'range' => array(
								$fdrField => array(
									'lte' => $fdr_cutoff
								)
							)
						)
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
			$shouldFDR = array_reduce($cutoffFilters, 'self::__fdrAddShould', array());
			$andFilters[] = array(
				'bool' => array(
					'should' => $shouldFDR
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
	
	$this->log($query,'debug');	
	$res = $this->Qtl->search($this->client,$q = $query);
	$this->set('res',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
    }
}

?>
