<?php
App::uses('AppModel', 'Model');

class Qtl extends AppModel {

    public $useTable = false;

    public function search($c,$q = null){

        $searchParams['index'] = 'meqtls';
        $searchParams['type']  = 'meqtls';
        $searchParams['size']  = 40;
        $res = $c->search($searchParams);
        return $res;
    }

}
?>