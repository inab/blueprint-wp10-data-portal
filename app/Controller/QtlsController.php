<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
	public $components = array('Paginator');
	
	private $chromosomes;
	
	private $sortKeys;
	
	private static $DEFAULT_SORT_CRITERIA = array('CHR' => 'asc');
	
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
	$this->Qtl->setupClient($elasticsearchConfig);
	
	// We get this only once!
	$this->sortKeys = $this->Qtl->SortKeys();
	
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
        $chromRes = $this->Qtl->search($q = $chroJson);
        $chromosomes = array_column($chromRes['aggregations']['chros']['buckets'],'key');
	sort($chromosomes);
        $this->chromosomes = $chromosomes;
    }
	
    public function index() {
	$params = null;
	if(isset($this->request->data['Qtl'])) {
		$this->redirect(array('search'=> $this->request->data['Qtl'], 'page' => 1));
	}
	if(empty($this->passedArgs['search']) && isset($this->request->data['Qtl'])) {
		$params = $this->request->data['Qtl'];
		unset($this->passedArgs['page']);
		$this->passedArgs['search'] = $params;
	}
	if(empty($this->request->data) && isset($this->passedArgs['search'])) {
		$params = $this->passedArgs['search'];
		$this->request->data['Qtl'] = $params;
	}
	
	/*
	if(isset($this->request->params['named']) && count($this->request->params['named']) > 0) {
		$params = $this->request->params['named'];
	} else if(isset($this->request->data['Qtl'])) {
		$params = $this->request->data['Qtl'];
	} else {
		$params = $this->request->data;
	}
	*/

	$this->Paginator->settings = array(
	//	'conditions' => $params,
		'fields' => null,
		'order' => self::$DEFAULT_SORT_CRITERIA,
		'limit' => 25
	);
	
	$res = $this->Paginator->paginate('Qtl',$params,$this->sortKeys);
	
	// Transform POST into GET
	// Inspect all the named parameters to apply the filters
	$filter = array();
	$this->set('res',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
    }
}

?>
