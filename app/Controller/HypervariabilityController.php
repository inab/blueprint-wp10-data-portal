<?php
App::uses('AppController','Controller');

class HypervariabilityController extends AppController
{
	public $helpers = array('Html', 'Form', 'Js');
	public $components = array('Paginator');
	
	private $sortKeys;
	
	private static $DEFAULT_SORT_CRITERIA = array('CHR' => 'asc');
	
	private static $DEFAULT_RESULTS_PER_PAGE = '25';

	private static $SELECTABLE_RESULTS_PER_PAGE = array(
		'25' => '25',
		'50' => '50',
		'100' => '100',
		'all' => 'all'
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
		$this->Hypervariability->setupClient($elasticsearchConfig);
		// We get this only once!
		$this->sortKeys = $this->Hypervariability->SortKeys();
	}
	
	public function index() {
		$params = null;
		//$this->log('Enter','debug');
		if(isset($this->request->data['Hypervariability'])) {
			$this->redirect(array('search'=> $this->request->data['Hypervariability'], 'page' => 1));
		}
		if(empty($this->passedArgs['search']) && isset($this->request->data['Hypervariability'])) {
			$params = $this->request->data['Hypervariability'];
			unset($this->passedArgs['page']);
			$this->passedArgs['search'] = $params;
		}
		if(empty($this->request->data) && isset($this->passedArgs['search'])) {
			$params = $this->passedArgs['search'];
			$this->request->data['Hypervariability'] = $params;
		}
		$res = array();
		
		if(isset($params)) {
			$results_per_page = (isset($params['results_per_page']) && isset(self::$SELECTABLE_RESULTS_PER_PAGE[$params['results_per_page']])) ? $params['results_per_page'] : self::$DEFAULT_RESULTS_PER_PAGE;
			/*
			if(isset($this->request->params['named']) && count($this->request->params['named']) > 0) {
				$params = $this->request->params['named'];
			} else if(isset($this->request->data['Qtl'])) {
				$params = $this->request->data['Qtl'];
			} else {
				$params = $this->request->data;
			}
			*/
			
			$pagSettings = array(
			//	'conditions' => $params,
				'fields' => null,
				'order' => self::$DEFAULT_SORT_CRITERIA,
			);
			if($results_per_page == 'all') {
				$pagSettings['limit'] = PHP_INT_MAX;
				$pagSettings['maxLimit'] = PHP_INT_MAX;
			} else {
				$pagSettings['limit'] = $results_per_page;
			}
			$this->Paginator->settings = $pagSettings;
			
			try {
				$res = $this->Paginator->paginate('Hypervariability',$params,$this->sortKeys);
			} catch (NotFoundException $e) {
				// In case of a page outside the limits, let's redirect to the first page
				$this->redirect(array('search'=> $this->request->data['Hypervariability'], 'page' => 1));
			}
		}
		
		// Transform POST into GET
		// Inspect all the named parameters to apply the filters
		$filter = array();
		$this->set('dHandler',$res);
		$this->set('chromosomes',$this->Hypervariability->availableChromosomes());
		$this->set('filter',$filter);
		$this->set('selectableResultsPerPage',self::$SELECTABLE_RESULTS_PER_PAGE);
		$this->set('defaultResultsPerPage',self::$DEFAULT_RESULTS_PER_PAGE);
		$this->set('ctl',$this);
	}
	
	public function nextBatch(&$res) {
		return $this->Hypervariability->next_scrolled_result($res);
	}
	
	public function enrichBatch(&$res) {
		// Just now, a no-op
	}
	
	public function download() {
		$params = null;
		//$this->log('Enter','debug');
		if(isset($this->request->data['Hypervariability'])) {
			$this->redirect(array('search'=> $this->request->data['Hypervariability'], 'page' => 1));
		}
		if(empty($this->passedArgs['search']) && isset($this->request->data['Hypervariability'])) {
			$params = $this->request->data['Hypervariability'];
			unset($this->passedArgs['page']);
			$this->passedArgs['search'] = $params;
		}
		if(empty($this->request->data) && isset($this->passedArgs['search'])) {
			$params = $this->passedArgs['search'];
			$this->request->data['Hypervariability'] = $params;
		}
		$res = array();
		
		if(isset($params)) {
			// Let's fetch all the data, by pagination
			$dHandler = $this->Hypervariability->paginate($params,null,null,PHP_INT_MAX);
			if(isset($dHandler['hits']) || isset($dHandler['_scroll_id'])) {
				$res = $dHandler;
				if($res['hits']['total'] == 0) {
					throw new NotFoundException(__('Hypervariability query not found'));
				}

				$csv_file = fopen('php://output', 'w');
				$datetime = new DateTime();
				$filename = "blueprint_wp10_hvar_query_result_".$datetime->format('c').".tsv";
				header('Content-type: text/tab-separated-values');
				header('Content-Disposition: attachment; filename="'.$filename.'"');

				$header_row = array('# '.'Cell type(s)','hvar_source','hvar_id','Probe ID','Chr','Arm','Location','Ensembl ID','HGNC symbol','Feature','Chromatin state(s)','Variability','GO term(s)');
				fputs($csv_file,implode("\t",$header_row)."\n");
				
				$touchdown = 0;
				while(count($res['hits']['hits']) > 0) {
					foreach ($res['hits']['hits'] as &$hit) {
						$h = &$hit['_source'];
						$qtl_line = implode("\t",array(
							is_array($h['cell_type']) ? implode(";", $h['cell_type']): $h['cell_type'],
							$h['qtl_source'],
							$h['hvar_id'],
							isset($h['probeId']) ? $h['probeId'] : '',
							$h['gene_chrom'],
							isset($h['arm']) ? $h['arm'] : '',
							isset($h['pos']) ? $h['pos'] : '',
							isset($h['ensemblGeneId']) ? (is_array($h['ensemblGeneId']) ? implode(";",$h['ensemblGeneId']) : $h['ensemblGeneId']) : '',
							isset($h['gene_name']) ? (is_array($h['gene_name']) ? implode(";",$h['gene_name']) : $h['gene_name']) : '',
							isset($h['feature']) ? $h['feature'] : '',
							isset($h['chromatin_state']) ? implode(';',array_map(function(&$chromatin_state) { return $chromatin_state['cell_type'].':'.$chromatin_state['state']; },$h['chromatin_state'])) : '',
							isset($h['variability']) ? (is_array($h['variability']) ? implode(";",$h['variability']) : $h['variability']) : '',
							isset($h['go_term']) ? (is_array($h['go_term']) ? implode(";",$h['go_term']) : $h['go_term']) : ''
						));

						fputs($csv_file,$qtl_line."\n");

						$touchdown++;
						if($touchdown>=10) {
							set_time_limit(30);
						}
					}
					$res = $this->nextBatch($res);
				}

				fclose($csv_file);

				$this->layout = false;
				$this->render(false);
				return false;
			} else {
				throw new NotFoundException(__('Hypervariability query not found'));
			}
		} else {
			throw new NotFoundException(__('Hypervariability query not found'));
		}
	}
	
	// Based on http://blog.ekini.net/2012/10/10/cakephp-2-x-csv-file-download-from-a-database-query/
	public function chart($cell_type = null,$qtl_source = null,$hvar_id = null) {
		$hvar_id = strtr($hvar_id,'_',':');
		if(!$cell_type || !$qtl_source || !$hvar_id) {
			throw new NotFoundException(__('Invalid query parameters'));
		}

		$chartData = $this->Hypervariability->fetchVariabilityChart($cell_type,$qtl_source,$hvar_id);
		if($chartData === null) {
			throw new NotFoundException(__('Hypervariability chart not available / not found'));
		}
		
		$filename = "variability_chart_${hvar_id}.png";
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
