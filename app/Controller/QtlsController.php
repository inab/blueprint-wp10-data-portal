<?php
class QtlsController extends AppController
{
    public $client; 

    public function beforeFilter(){
        parent::beforeFilter();
        $this->client = new Elasticsearch\Client();
    }

    public function index(){

        $res = $this->Qtl->search($this->client,$q = null);
        $this->set('res',$res);
    }
}

?>