<?php
App::uses('AppController','Controller');

class HypervariabilityController extends AppController
{
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
	$this->Hypervariability->setupClient($elasticsearchConfig);
    }

	// Based on http://blog.ekini.net/2012/10/10/cakephp-2-x-csv-file-download-from-a-database-query/
	public function chart($cell_type = null,$qtl_source = null,$qtl_id = null) {
		$qtl_id = strtr($qtl_id,'_',':');
		if(!$cell_type || !$qtl_source || !$qtl_id) {
			throw new NotFoundException(__('Invalid query parameters'));
		}

		$chartData = $this->Hypervariability->fetchVariabilityChart($cell_type,$qtl_source,$qtl_id);
		if($chartData === null) {
			throw new NotFoundException(__('Variability chart not available / not found'));
		}
		
		$filename = "variability_chart_${qtl_id}.png";
		$this->autoRender = false;
		$this->response->type('image/png');
		//$this->response->download($filename);
		$this->response->body($chartData);
		
		//$png_file = fopen('php://output', 'w');
		//header('Content-type: image/png');
		//header('Content-Disposition: attachment; filename="'.$filename.'"');
                //
		//fwrite($png_file,$chartData);
                //
		//fclose($png_file);
                //
		//$this->layout = false;
		//$this->render(false);
		//return false;
	}
}

?>
