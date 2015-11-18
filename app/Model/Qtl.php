<?php
App::uses('AppModel', 'Model');

class Qtl extends AppModel {
	public $useTable = false;
	
	private static $BP_INDEX = 'meqtls';
	private static $BP_TYPE = 'meqtls';
	
	public function search($c,$q = null,$size = 40,$offset = null) {
		
		$searchParams = array(
			'index' => self::$BP_INDEX,
			'type' => self::$BP_TYPE,
			'size' => $size
		);
		
		if(! empty($offset) && $offset > 0) {
			$searchParams['from'] = $offset;
		}
		
		if(! empty($q)) {
			$searchParams['body'] = $q;
		}
		
		$res = $c->search($searchParams);
		return $res;
	}

	public function count($c,$q = null) {
		
		$countParams = array(
			'index' => self::$BP_INDEX,
			'type' => self::$BP_TYPE
		);
		
		if(! empty($q)) {
			$searchParams['body'] = $q;
		}
		
		$res = $c->count($searchParams);
		return $res;
	}
}
?>
