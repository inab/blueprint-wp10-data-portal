<?php
$ENSEMBL_BASE = 'http://jan2013.archive.ensembl.org/Homo_sapiens/';
$UCSC_SERVER = 'https://genome-euro.ucsc.edu/';
$UCSC_genome_ver = 'hg19';

// This must be given by the controller
//$traitAttrs = array(
//	'gene' => 'RNA-seq data',
//	'meth' => 'methylation arrays',
//);

$this->Html->css('blueprint-qtls',array('inline' => false));
$this->Html->script('blueprint-qtls',array('inline' => false));
$this->Paginator->options(array('url' => $this->passedArgs));
?>

<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <h1 class="ui header">WP10 Hypervariability Search</h1>
    <div class="ui secondary form segment">
	<?php
		echo $this->Form->create();
	?>
        <div class="three fields">
		<div class="field">
		<?php
			$attrs = array('any' => 'Any chromosome');
			foreach ($chromosomes as &$chro) {
				$attrs[$chro] = 'Chromosome '.$chro;
			}
			echo $this->Form->label('chromosome','Chromosome',array('class' => 'normal-style'));
			echo $this->Form->input('chromosome',array('div' => false,'empty' => $attrs['any'], 'options' => $attrs, 'label' => false, 'class' => 'ui search dropdown', 'default' => 'any'));
		?>
		</div>
		<div class="field">
		<?php
			echo $this->Form->label('chromosome_start','Chromosome start',array('class' => 'normal-style'));
			echo $this->Form->input('chromosome_start',array('type' => 'text', 'div' => false,'label' => false, 'pattern' => '\d+', 'placeholder'=>'Start'));
		?>
		</div>
		<div class="field">
		<?php
			echo $this->Form->label('chromosome_end','Chromosome end',array('class' => 'normal-style'));
			echo $this->Form->input('chromosome_end',array('type' => 'text', 'div' => false,'label' => false, 'pattern' => '\d+', 'placeholder'=>'End'));
		?>
		</div>
        </div>
        <div class="three fields">
		<div class="field">
		<?php
			echo $this->Form->label('gene','Gene Name or Id search',array('class' => 'normal-style'));
			echo $this->Form->input('gene',array('type' => 'text', 'div' => false,'label' => false, 'placeholder'=>'Gene name or Ensembl Gene Id'));
		?>
                	<div class="ui checkbox">
			<?php
				echo $this->Form->label('fuzzy_gene_search','Do fuzzy gene name searches');
				echo $this->Form->checkbox('fuzzy_gene_search',array('hiddenField' => false));
			?>
			</div>
		</div>
		<div class="field">
		</div>
		<div class="field">
		<?php
			echo $this->Form->label('array_probe','Methylation probe id',array('class' => 'normal-style'));
			echo $this->Form->input('array_probe',array('type' => 'text', 'div' => false,'label' => false, 'placeholder'=>'Meth probe'));
		?>
		</div>
        </div>
        <div class="three fields">
            <div class="inline field">
            </div>
            <div class="inline field">
		<?php
			$anyCellType = 'any cellular type';
			$cellTypeAttrs = array(
				'tcel' => 'T-Cell',
				'mono' => 'Monocyte',
				'neut' => 'Neutrophil',
			);
			echo $this->Form->label('cell_type','Restrict cellular types',array('class' => 'normal-style'));
			echo $this->Form->select('cell_type',$cellTypeAttrs,array('class' => 'ui fluid search dropdown', 'empty' => $anyCellType, 'label' => false, 'multiple' => true));
		?>
            </div>
            <div class="inline field">
		<?php
			$anyTrait = 'any HVar identification source';
			echo $this->Form->label('hvar_source','Show hypervariabilities found with',array('class' => 'normal-style'));
			echo $this->Form->select('hvar_source',$traitAttrs,array('class' => 'ui fluid search dropdown', 'empty' => $anyTrait, 'label' => false, 'multiple' => true));
		?>
            </div>
        </div>
        <div class="three fields">
            <div class="field">
		<?php
			echo $this->Form->reset("Reset fields",array('type' => 'reset', 'div' => false,'class'=>'ui negative button'));
			echo $this->Form->button($this->Html->tag("i","",array('class' => 'trash outline icon'))."Clear all fields",array('type' => 'button','div' => false,'onclick'=>'clearJQueryForm(this.form)','class'=>'ui secondary right labeled icon button'));
		?>
            </div>
            <div class="inline field">
		<?php
			echo $this->Form->input('results_per_page',array('div'=>false,'options' => $selectableResultsPerPage, 'default' => $defaultResultsPerPage,'label' => false,'style' => 'margin-right: 0.5em;'));
			echo $this->Form->label('results_per_page','Results per page');
		?>
            </div>
	    <div class="field ui right aligned container">
		<?php
			echo $this->Form->button($this->Html->tag("i","",array('class' => 'search icon'))."Search",array('type' => 'submit', 'div' => false,'class'=>'ui primary right labeled icon button submit'));
		?>
	    </div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
<?php
	if(isset($dHandler['hits']) || isset($dHandler['_scroll_id'])):
	//if(isset($dHandler)):
		$res = $dHandler;
?>
<div class="sixteen wide left aligned column">
    <!-- <div style="font-size: 1em; font-size: 1vmin;"> -->
    <div>
	<div class="ui equal width grid">
		<div class="left aligned column">
		<?php
			echo $this->Paginator->first('<<',array('class' => 'paginate first'));
			echo $this->Paginator->prev('<',array('class' => 'paginate prev'),null,array('class' => 'paginate prev hidden'));
			echo $this->Paginator->numbers();
			echo $this->Paginator->next('>',array('class' => 'paginate next'),null,array('class' => 'paginate next hidden'));
			echo $this->Paginator->last('>>',array('class' => 'paginate last'));
		?>
		</div>
		<div class="center aligned column">
		<?php
			echo $this->Paginator->counter('{:count} results, showing from {:start} to {:end}');
			$paginatorInformation = $this->Paginator->params();
			$totalCount = $paginatorInformation['count'];
			if($totalCount > 0):
		?>
			<br/>
			<?php
				echo $this->Html->link(
					$this->Html->tag('i','',array('class' => 'download icon')).'download all as TSV',
					array(
						'controller' => $this->params['controller'],
						'action' => 'download',
						'full_base' => true,
						// Now, the parameters for the link
						'search' => $this->passedArgs['search']
					),
					array(
						'target' => '_blank',
						'style' => 'font-size:0.75em;',
						'confirm' => 'Are you sure you wish to download these results?',
						'escape' => false
					)
				);
			?>
		<?php
			endif;
		?>
		</div>
		<div class="right aligned column">
		<?php
			echo $this->Paginator->counter('Page {:page} of {:pages}');
		?>
		</div>
	</div>
	<table class="ui table">
		<thead>
			<th><?php echo $this->Paginator->sort('cell_type','Cell Type'); ?></th>
			<th><?php echo $this->Paginator->sort('hvar_source','HVar Source'); ?></th>
			<th class="center aligned"><?php echo $this->Paginator->sort('CHR','Coordinates',array('class' => 'nowrap')); ?></th>
			<th><?php echo $this->Paginator->sort('gene','Gene',array('class' => 'nowrap')); ?></th>
			<th class="center aligned"><?php echo $this->Paginator->sort('array_probe','Meth probe',array('class' => 'nowrap')); ?></th>
			<th class="center aligned"><?php echo $this->Paginator->sort('METH_pos','pos',array('class' => 'nowrap')); ?></th>
			<th class="center aligned">QTL?</th>
			<th class="center aligned"><i class="info circle icon" data-position="left center"></i></th>
		</thead>
		<tbody>
		<?php
			$seen_hypervar = array();
			$rowCounter = 0;
			while(count($res['hits']['hits']) > 0):
				$ctl->enrichBatch($res);
				foreach ($res['hits']['hits'] as &$hit):
					$h = &$hit['_source'];
					$rowCounter++;
					//$this->log($h,'debug');
		?>
			<tr>
				<td><?php echo $this->element('WP10/celltype',array('h' => &$h)); ?></td>
				<td><?php echo $this->element('WP10/qtl_source',array('h' => &$h)); ?></td>
				<td><?php echo $this->element('WP10/coordinates',array('h' => &$h,'ENSEMBL_BASE' => &$ENSEMBL_BASE)); ?></td>
				<td><?php echo $this->element('WP10/gene_transcript',array('h' => &$h,'ENSEMBL_BASE' => &$ENSEMBL_BASE,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver)); ?></td>
				<td><?php echo $this->element('WP10/methprobe',array('h' => &$h,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver)); ?></td>
				<td><?php echo $this->element('WP10/pos',array('h' => &$h)); ?></td>
				<td class="center aligned"><?php
					if(isset($h['qtl_id'])) {
						echo $this->Html->link(
							$this->Html->tag('i','',array('class' => 'external icon')),
							array(
								'controller' => 'qtls',
								'action' => 'index',
								'full_base' => true,
								// Now, the parameters for the link
								'search' => array('qtl_id' => $h['qtl_id'],'qtl_source' => $h['qtl_source'],'cell_type' => $h['cell_type'])
							),
							array(
								'target' => '_blank',
								'style' => 'font-size:0.75em;',
								'confirm' => 'Are you sure you open the new window?',
								'escape' => false
							)
						);
					}
				?></td>
				<td class="center aligned"><?php
					$hypervar_wid = "hypervar_".$h['hvar_id'];
					$click_hypervar_wid = "click_hypervar_".$rowCounter;
					echo $this->Html->tag('i','',array('class' => 'link line chart blue icon','id' => $click_hypervar_wid));
					$this->Js->get('#'.$click_hypervar_wid)->event('click',"$('#".$hypervar_wid."').modal('show');");
					echo $this->Js->writeBuffer(); // Write cached scripts
					if(!isset($seen_hypervar[$hypervar_wid])) {
						echo $this->element('Hypervariability/result',array('hypervar' => &$h,'ENSEMBL_BASE' => &$ENSEMBL_BASE,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver,'hypervar_wid' => $hypervar_wid));
						$seen_hypervar[$hypervar_wid] = null;
					}
				?></td>
			</tr>
		<?php
				endforeach;
				$res = $ctl->nextBatch($res);
			endwhile;
		?>
		</tbody>
	</table>
	<div class="ui equal width grid">
		<div class="left aligned column">
		<?php
			echo $this->Paginator->first('<<',array('class' => 'paginate first'));
			echo $this->Paginator->prev('<',array('class' => 'paginate prev'),null,array('class' => 'paginate prev hidden'));
			echo $this->Paginator->numbers();
			echo $this->Paginator->next('>',array('class' => 'paginate next'),null,array('class' => 'paginate next hidden'));
			echo $this->Paginator->last('>>',array('class' => 'paginate last'));
		?>
		</div>
		<div class="center aligned column">
		<?php
			echo $this->Paginator->counter('{:count} results, showing from {:start} to {:end}');
		?>
		</div>
		<div class="right aligned column">
		<?php
			echo $this->Paginator->counter('Page {:page} of {:pages}');
		?>
		</div>
	</div>
    </div>
</div>
<?php endif; ?>
<div class="bibref"><u>Reference</u>: <a href="http://genomebiology.biomedcentral.com/articles/10.1186/s13059-017-1156-8" target="_blank">Ecker S., Chen L., Pancaldi V., Bagger F.O., Fernández J.M., Pau E.C. de S., Juan D., Mann A., Watt S., Casale F.P., et al. (2016) Genome-wide Analysis of Differential Transcriptional and Epigenetic Variability Across Human Immune Cell Types. Genome Biology 2017 18:18.</a><br /><u>DOI</u>: <a href="http://dx.doi.org/10.1186%2Fs13059-017-1156-8" target="_blank">10.1186/s13059-017-1156-8</a></div>
