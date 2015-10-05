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
	$conditions = array();
	
	// Transform POST into GET
	if(($this->request->is('post') || $this->request->is('put')) && isset($this->data['Filter'])){
		$filter_url = array();
		$filter_url['controller'] = $this->request->params['controller'];
		$filter_url['action'] = $this->request->params['action'];
		// We need to overwrite the page every time we change the parameters
		$filter_url['page'] = 1;
		
		// for each filter we will add a GET parameter for the generated url
		foreach($this->data['Filter'] as $name => $value) {
			if($value) {
				// You might want to sanitize the $value here
				// or even do a urlencode to be sure
				$filter_url[$name] = urlencode($value);
			}
		}
		
		// now that we have generated an url with GET parameters, 
		// we'll redirect to that page
		return $this->redirect($filter_url);
	} else {
		// Inspect all the named parameters to apply the filters
		$andConds = array();
		foreach($this->params['named'] as $param_name => $value) {
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
						$andConds[] = array('term' => array('gid.1' => $value));
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
				$this->request->data['Filter'][$param_name] = $value;
			}
		}
		
		// Generating a query
		$query = array();
		if(! empty($andCond)) {
			$query['query']['filtered']['filter']['bool']['must'] = $andConds;
		}
		
		$res = $this->Qtl->search($this->client,$q = $query);
		$this->set('res',$res);
		$this->set('chromosomes',$this->chromosomes);
	}
    }
}

?>
