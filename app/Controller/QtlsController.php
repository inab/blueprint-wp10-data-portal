<?php
class QtlsController extends AppController
{
	public $helpers = array('Html', 'Form');
	public $components = array('Paginator');
	
	private $chromosomes;
	
	private $sortKeys;
	
	private static $DEFAULT_SORT_CRITERIA = array('CHR' => 'asc');
	
	public $paginate = array(
		'limit' => 25
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
	
	/*
	if(isset($this->request->params['named']) && count($this->request->params['named']) > 0) {
		$params = $this->request->params['named'];
	} else if(isset($this->request->data['Qtl'])) {
		$params = $this->request->data['Qtl'];
	} else {
		$params = $this->request->data;
	}
	*/

	$this->Paginator->settings = array(
	//	'conditions' => $params,
		'fields' => null,
		'order' => self::$DEFAULT_SORT_CRITERIA,
		'limit' => 25
	);
	
	$res = $this->Paginator->paginate('Qtl',$params,$this->sortKeys);
	
	// Transform POST into GET
	// Inspect all the named parameters to apply the filters
	$filter = array();
	$this->set('res',$res);
	$this->set('chromosomes',$this->chromosomes);
	$this->set('filter',$filter);
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
