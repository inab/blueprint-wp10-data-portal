<?php
App::uses('AppModel', 'Model');

class Qtl extends AppModel {

    public $useTable = false;

    public function search($c,$q = null){

	$searchParams = array(
		'index' => 'meqtls',
		'type' => 'meqtls',
		'size' => 40
	);
	
	if(! is_null($q)) {
		$searchParams['body'] = $q;
	}
	
        $res = $c->search($searchParams);
        return $res;
    }

}
?>
