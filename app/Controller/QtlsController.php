<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
    public $client;
    public $chromosomes;

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
        $chromosomes = array();
        foreach ($chromRes['aggregations']['chros']['buckets'] as $eachChro) {
		$chromosomes[] = $eachChro['key'];
	}
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
	$andConds = array();
	foreach($params as $param_name => $value) {
		// Don't apply the default named parameters used for pagination
		if(!empty($value)) {
			switch($param_name) {
				case "chromosome":
					$andConds[] = array('term' => array('CHR' => $value));
					break;
				case "chromosome_start":
					$andConds[] = array('range' => array('start_position' => array('gte' => $value)));
					break;
				case "chromosome_end":
					$andConds[] = array('range' => array('end_position' => array('lte' => $value)));
					break;
				case "gene":
					$andConds[] = array('term' => array('UCSC_RefGene_Name' => $value));
					break;
				case "SNP":
					$andConds[] = array('term' => array('SNP' => $value));
					break;
				case "array_probe":
					$andConds[] = array('term' => array('meth.probe' => $value));
					break;
				case "fdr_cutoff":
					$andConds[] = array('range' => array('mon.fdr' => array('lte' => $value)));
					$andConds[] = array('range' => array('neu.fdr' => array('lte' => $value)));
					$andConds[] = array('range' => array('tcl.fdr' => array('lte' => $value)));
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

	// Generating a query
	$query = array();
	if(! empty($andConds)) {
		$query['query']['filtered']['filter']['bool']['must'] = $andConds;
	}

	$this->log($query,'debug');	
	$res = $this->Qtl->search($this->client,$q = $query);
	$this->set('res',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
    }
}

?>
