<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
    public $client; 

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
    }

    public function index(){

        $res = $this->Qtl->search($this->client,$q = null);
        $this->set('res',$res);
    }
}

?>
