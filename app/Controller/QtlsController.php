<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
	public $components = array('Paginator');
	
    public $client;
    public $chromosomes;
	
	public $paginate = array(
		'limit' => 25
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

	$this->Paginator->settings = array(
		'conditions' => $params,
		'order' => self::$DEFAULT_SORT_CRITERIA,
		'limit' => 25
	);
	
	$this->log($this->Paginator->paginate('Qtl'),'debug');
	
	// Transform POST into GET
	// Inspect all the named parameters to apply the filters
	$filter = array();
	
	$query = $this->Qtl->esQueryBuilder($conditions = $params, $fields = null, $order = Qtl::$DEFAULT_SORT_CRITERIA);
	
	$this->log($query,'debug');	
	$res = $this->Qtl->search($this->client,$q = $query);
	$this->set('res',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
    }
}

?>
