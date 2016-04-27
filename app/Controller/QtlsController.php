<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
	public $components = array('Paginator');
	
	private $chromosomes;
	
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
	$this->Qtl->setupClient($elasticsearchConfig);
	
	// We get this only once!
	$this->sortKeys = $this->Qtl->SortKeys();
	
        $chroJson = '{
		"aggs": {
			"chros": {
				"terms": {
					"field": "gene_chrom",
					"size": 0
				}
			}
		}
        }';
        $chromRes = $this->Qtl->search($q = $chroJson);
        $chromosomes = array_column($chromRes['aggregations']['chros']['buckets'],'key');
	sort($chromosomes);
        $this->chromosomes = $chromosomes;
    }

    public function index() {
	$params = null;
	//$this->log('Enter','debug');
	if(isset($this->request->data['Qtl'])) {
		$this->redirect(array('search'=> $this->request->data['Qtl'], 'page' => 1));
	}
	if(empty($this->passedArgs['search']) && isset($this->request->data['Qtl'])) {
		$params = $this->request->data['Qtl'];
		unset($this->passedArgs['page']);
		$this->passedArgs['search'] = $params;
	}
	if(empty($this->request->data) && isset($this->passedArgs['search'])) {
		$params = $this->passedArgs['search'];
		$this->request->data['Qtl'] = $params;
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
			$res = $this->Paginator->paginate('Qtl',$params,$this->sortKeys);
		} catch (NotFoundException $e) {
			// In case of a page outside the limits, let's redirect to the first page
			$this->redirect(array('search'=> $this->request->data['Qtl'], 'page' => 1));
		}
	}
	
	// Transform POST into GET
	// Inspect all the named parameters to apply the filters
	$filter = array();
	$this->set('dHandler',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
	$this->set('selectableResultsPerPage',self::$SELECTABLE_RESULTS_PER_PAGE);
	$this->set('defaultResultsPerPage',self::$DEFAULT_RESULTS_PER_PAGE);
	$this->set('ctl',$this);
    }
	
	public function nextBatch($res) {
		return $this->Qtl->next_scrolled_result($res);
	}

	public function download() {
		$params = null;
		//$this->log('Enter','debug');
		if(isset($this->request->data['Qtl'])) {
			$this->redirect(array('search'=> $this->request->data['Qtl'], 'page' => 1));
		}
		if(empty($this->passedArgs['search']) && isset($this->request->data['Qtl'])) {
			$params = $this->request->data['Qtl'];
			unset($this->passedArgs['page']);
			$this->passedArgs['search'] = $params;
		}
		if(empty($this->request->data) && isset($this->passedArgs['search'])) {
			$params = $this->passedArgs['search'];
			$this->request->data['Qtl'] = $params;
		}
		$res = array();
		
		if(isset($params)) {
			// Let's fetch all the data, by pagination
			$dHandler = $this->Qtl->paginate($params,null,null,PHP_INT_MAX);
			if(isset($dHandler['hits']) || isset($dHandler['_scroll_id'])) {
				$res = $dHandler;
				if($res['hits']['total'] == 0) {
					throw new NotFoundException(__('QTL query not found'));
				}

				$csv_file = fopen('php://output', 'w');
				$datetime = new DateTime();
				$filename = "blueprint_wp10_query_result_".$datetime->format('c').".tsv";
				header('Content-type: text/tab-separated-values');
				header('Content-Disposition: attachment; filename="'.$filename.'"');

				$header_row = array('# '.'cell_type','qtl_source','qtl_id','chromosome','chromosome_start','chromosome_end','snp_id','pos','rs_id(s)','REF(s)','ALT(s)','MAF(s)','p-bonferroni','q-value(FDR)','overlapped gene(s)','overlapped EnsEMBL Gene Id(s)','overlapped EnsEMBL Transcript Id(s)','exon_number','methylation_probe_id','histone','splice junctions','F','metrics');
				fputs($csv_file,implode("\t",$header_row)."\n");
				
				$touchdown = 0;
				while(count($res['hits']['hits']) > 0) {
					foreach ($res['hits']['hits'] as $hit) {
						$h = $hit['_source'];
						if(isset($h['rsId'])) {
							$rsIds = implode(";",$h['rsId']);
							if(isset($h['dbSnpRef'])) {
								$REFs = implode(";",$h['dbSnpRef']);
								$ALTs = implode(";",$h['dbSnpAlt']);
								$MAFs = implode(";",$h['MAF']);
							} else {
								$REFs = '';
								$ALTs = '';
								$MAFs = '';
							}
						} else {
							$rsIds = substr($h['rsId'],0,2) == 'rs' ? $h['rsId'] : '';
							$REFs = '';
							$ALTs = '';
							$MAFs = '';
						}
						$qtl_line = implode("\t",array(
							$h['cell_type'],
							$h['qtl_source'],
							$h['gene_id'],
							$h['gene_chrom'],
							$h['gene_start'],
							$h['gene_end'],
							$h['snp_id'],
							isset($h['pos']) ? $h['pos'] : '',
							$rsIds,
							$REFs,
							$ALTs,
							$MAFs,
							$h['pv'],
							$h['qv'],
							isset($h['gene_name']) ? (is_array($h['gene_name']) ? implode(";",$h['gene_name']) : $h['gene_name']) : '',
							isset($h['ensemblGeneId']) ? (is_array($h['ensemblGeneId']) ? implode(";",$h['ensemblGeneId']) : $h['ensemblGeneId']) : '',
							isset($h['ensemblTranscriptId']) ? (is_array($h['ensemblTranscriptId']) ? implode(";",$h['ensemblTranscriptId']) : $h['ensemblTranscriptId']) : '',
							isset($h['exonNumber']) ? $h['exonNumber'] : '',
							isset($h['probeId']) ? $h['probeId'] : '',
							isset($h['histone']) ? $h['histone'] : '',
							isset($h['splice']) ? (is_array($h['splice']) ? implode(";",$h['splice']) : $h['splice']) : '',
							isset($h['F']) ? isset($h['F']) : '',
							json_encode($h['metrics'])
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
				throw new NotFoundException(__('QTL query not found'));
			}
		} else {
			throw new NotFoundException(__('QTL query not found'));
		}
	}

	// Based on http://blog.ekini.net/2012/10/10/cakephp-2-x-csv-file-download-from-a-database-query/
	public function bulkqtl($cell_type = null,$qtl_source = null,$qtl_id = null) {
		$qtl_id = strtr($qtl_id,'_',':');
		if(!$cell_type || !$qtl_source || !$qtl_id) {
			throw new NotFoundException(__('Invalid query parameters'));
		}

		$bulkQtlRes = $this->Qtl->fetchBulkQtl($cell_type,$qtl_source,$qtl_id);
		if($bulkQtlRes['hits']['total'] == 0) {
			throw new NotFoundException(__('QTL not found'));
		}

		$csv_file = fopen('php://output', 'w');
		$filename = "${cell_type}_${qtl_source}_${qtl_id}.tsv";
		header('Content-type: text/tab-separated-values');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		$header_row = array('# '.'cell_type','qtl_source','qtl_id','rsid','pos','p_value','beta','p-bonferroni','q-value(FDR)');
		fputs($csv_file,implode("\t",$header_row)."\n");
		
		# Here we print the data from the database by chunks
		foreach($bulkQtlRes['hits']['hits'] as $hit) {
			$read_cell_type = $hit['_source']['cell_type'];
			$read_qtl_source = $hit['_source']['qtl_source'];
			$read_qtl_id = $hit['_source']['gene_id'];
			$bulk_qtl_prefix = implode("\t",array($read_cell_type,$read_qtl_source,$read_qtl_id))."\t";
			foreach(explode("\n",$hit['_source']['qtl_data']) as $line) {
				if(strlen($line) > 0) {
					$line = str_replace('\t',"\t",$line);
					fputs($csv_file,$bulk_qtl_prefix.$line."\n");
				}
			}
		}

		fclose($csv_file);

		$this->layout = false;
		$this->render(false);
		return false;
	}
}

?>
