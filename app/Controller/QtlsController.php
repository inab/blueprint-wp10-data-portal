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
				"cardinality": {
					"field": "CHR"
				}
			}
		}
        }';
        $chromRes = $this->Qtl->search($this->client,$q = '');
        $chromosomes = array();
        foreach ($chromRes['aggregations']['chros']['buckets'] as $eachChro) {
		$chromosomes[] = $eachChro['key'];
	}
        $this->chromosomes = $chromosomes;
    }

    public function index(){

        $res = $this->Qtl->search($this->client,$q = null);
        $this->set('res',$res);
        $this->set('chromosomes',$this->chromosomes);
    }
}

?>
