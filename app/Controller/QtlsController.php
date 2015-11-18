<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
	public $components = array('Paginator');
	
	public $chromosomes;
	
	private static $DEFAULT_SORT_CRITERIA = array(
		'CHR' => 'asc',
		'start_position' => 'asc',
		'end_position' => 'asc'
	);
	
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
	
	$res = $this->Paginator->paginate('Qtl');
	
	// Transform POST into GET
	// Inspect all the named parameters to apply the filters
	$filter = array();
	$this->set('res',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
    }
}

?>
