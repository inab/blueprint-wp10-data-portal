<?php
App::uses('Qtl', 'Model');

class Variability extends Qtl {
	private static $BP_VARIABILITY_INDEX = 'wp10qtls_variability';
	private static $BP_VARIABILITY_TYPE = 'qtl_variability';

	public function fetchVariability($cell_type,$qtl_source,$qtl_id) {
		$mustArray = array();
		if($cell_type != null) {
			$mustArray[] = array(
				'term' => array(
					'cell_type' => $cell_type
				)
			);
		}

		if($qtl_source != null) {
			$mustArray[] = array(
				'term' => array(
					'qtl_source' => $qtl_source
				)
			);
		}

		$qtl_id_key = is_array($qtl_id) ? 'terms' : 'term';
		$mustArray[] = 	array(
			$qtl_id_key => array(
				'gene_id' => &$qtl_id
			)
		);

		$searchParams = array(
			'index' => self::$BP_VARIABILITY_INDEX,
			'type' => self::$BP_VARIABILITY_TYPE,
			'size' => 10,
			'body' => array(
				'query' => array(
					'filtered' => array(
						'filter' => array(
							'bool' => array(
								'must' => &$mustArray
							)
						)
					)
				)
			)
		);
		
		$res = $this->client->search($searchParams);

		return $res;
	}

	public function fetchVariabilityChart($cell_type,$qtl_source,$qtl_id) {
		$varRes = $this->fetchVariability($cell_type,$qtl_source,$qtl_id);

		return ($varRes['hits']['total'] > 0) ? base64_decode($varRes['hits']['hits'][0]['_source']['associated_chart']) : null;
	}
}

