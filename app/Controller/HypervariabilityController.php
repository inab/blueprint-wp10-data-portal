<?php
App::uses('AppController','Controller');

class HypervariabilityController extends AppController
{
	public $uses = array('Hypervariability','Qtl');
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
	
	private static $IMAGE_SCALES = array(
		'small'	=>	0.25,
		'medium'	=>	0.5,
		'large'	=>	0.75,
		'raw'	=>	1
	);
	
	private static $TRAIT_ATTRS = array(
		'gene' => 'RNA-seq data',
		'meth' => 'methylation arrays',
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
		$this->Qtl->setupClient($elasticsearchConfig);
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
		$this->set('traitAttrs',self::$TRAIT_ATTRS);
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
		# Step 1: gather all the hvar_id and create a hash
		$hvar_hash = array();
		$anyData = false;
		foreach ($res['hits']['hits'] as &$hit) {
			$h = &$hit['_source'];
			
			# This is needed to reconciliate lack of ensembl subversion
			$hvar_id_val = $h['hvar_id'];
			$hvar_id_sep = strrpos($hvar_id_val,'.');
			if($hvar_id_sep !== false) {
				$hvar_id_val = substr($hvar_id_val,0,$hvar_id_sep);
			}
			
			if(!isset($hvar_hash[$hvar_id_val])) {
				$hvar_hash[$hvar_id_val] = array();
			}

			$hvar_id = &$hvar_hash[$hvar_id_val];

			if(!isset($hvar_id[$h['qtl_source']])) {
				$hvar_id[$h['qtl_source']] = array();
			}
			$qtl_source = &$hvar_id[$h['qtl_source']];
			
			if(is_array($h['cell_type'])) {
				$cell_types = &$h['cell_type'];
			} else {
				$cell_types = [ $h['cell_type'] ];
			}
			
			foreach($cell_types as &$cell_type_name) {
				$qtl_source[$cell_type_name] = &$h;
			}

			$anyData = true;
		}

		//$this->log(array_keys($hvar_hash),'debug');
		
		if($anyData) {
			# Step 2: fetch them from the variation index
			$qtlData = $this->Qtl->fetchQTLs(null, array_keys(self::$TRAIT_ATTRS), array_keys($hvar_hash));
			
			# Step 3: merge!
			if(count($qtlData['hits']['hits']) > 0) {
				foreach($qtlData['hits']['hits'] as &$var) {
					$v = &$var['_source'];
					
					# This is needed to reconciliate lack of ensembl subversion
					$qtl_id_val = $v['gene_id'];
					$qtl_id_sep = strrpos($qtl_id_val,'.');
					if($qtl_id_sep !== false) {
						$qtl_id_val = substr($qtl_id_val,0,$qtl_id_sep);
					}
					
					if(isset($hvar_hash[$qtl_id_val])) {
						$gene_id = &$hvar_hash[$qtl_id_val];
						if(isset($gene_id[$v['qtl_source']])) {
							$qtl_source = &$gene_id[$v['qtl_source']];
							if(isset($qtl_source[$v['cell_type']])) {
								$qtl_source[$v['cell_type']]['qtl_id'] = $v['gene_id'];
							}
						}
					}
				}
			}
		}
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
		if(!$cell_type || !$qtl_source || !$hvar_id) {
			throw new NotFoundException(__('Invalid query parameters'));
		}
		
		$underPos = strrpos($hvar_id,'_');
		if($underPos!==false) {
			$imgCommand = substr($hvar_id,$underPos+1);
			
			if(array_key_exists($imgCommand,self::$IMAGE_SCALES)) {
				$hvar_id = substr(strtr($hvar_id,'_',':'),0,$underPos);
				$imgScale = self::$IMAGE_SCALES[$imgCommand];
			} else {
				throw new NotFoundException(__('Hypervariability chart not available / not found'));
			}
		}
		
		$chartData = $this->Hypervariability->fetchVariabilityChart($cell_type,$qtl_source,$hvar_id);
		if($chartData === null) {
			throw new NotFoundException(__('Hypervariability chart not available / not found'));
		}
		
		$filename = "variability_chart_${hvar_id}";
		if(isset($imgCommand)) {
			$filename .= "_" . $imgCommand;
			
			if($imgScale!==1) {
				$imagick = new Imagick();
				$imagick->readImageBlob($chartData);
				$imagick->scaleImage(round($imagick->getImageWidth() * $imgScale),round($imagick->getImageHeight() * $imgScale),true);
				$chartData = $imagick->getImageBlob();
			}
		}
		$filename .= ".png";
		$this->autoRender = false;
		$this->response->type('image/png');
		$this->response->cache('-1 minute', '+7 days');
		$this->response->sharable(true,3600);
		$this->response->expires('+7 days');
		if(!isset($imgCommand)) {
			$this->response->download($filename);
		}
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
