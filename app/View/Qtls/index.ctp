<div style="width:100%; text-align:center;">

<?php
// <h2>BLUEPRINT WP10 QTLs Data Portal is offline for several days due database maintentance<br />Sorry for the inconvenience</h2>
$ENSEMBL_BASE = 'http://jan2013.archive.ensembl.org/Homo_sapiens/';
$UCSC_SERVER = 'https://genome-euro.ucsc.edu/';
$UCSC_genome_ver = 'hg19';

$traitAttrs = array(
	'gene' => 'gene',
//	'exon' => 'exon',
//	'cufflinks' => 'Cufflinks',
	'meth' => 'methylation array',
//	'sj' => 'splice junction',
	'psi' => 'percent splice-in',
	'K27AC' => 'H3K27AC ChIP-Seq peaks',
	'K4ME1'=> 'H3K4ME1 ChIP-Seq peaks',
//	'sqtls' => 'sQTLseekeR'
);

$this->Html->css('blueprint-qtls',array('inline' => false));
$this->Html->script('blueprint-qtls',array('inline' => false));
$this->Paginator->options(array('url' => $this->passedArgs));
?>

<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <h1 class="ui header">WP10 QTLs Search</h1>
    <?php if(!isset($this->passedArgs['search']) || !isset($this->passedArgs['search']['qtl_id']) || strlen($this->passedArgs['search']['qtl_id']) == 0): ?>
    <div class="ui secondary form segment">
	<?php
		echo $this->Form->create();
		echo $this->Form->hidden('qtl_id');
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
                	<div class="ui checkbox">
			<?php
				echo $this->Form->label('coordinates_match_snps','Match SNPs positions');
				echo $this->Form->checkbox('coordinates_match_snps',array('hiddenField' => false));
			?>
			</div>
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
		<?php
			echo $this->Form->label('SNP','dbSNP rsid search',array('class' => 'normal-style'));
			echo $this->Form->input('SNP',array('type' => 'text', 'div' => false,'label' => false, 'pattern' => 'rs.+', 'placeholder'=>'SNP'));
		?>
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
	    	<div>
		<?php
			echo $this->Form->label('fdr_cutoff','Cut-off on FDR values',array('class' => 'normal-style'));
			echo $this->Form->input('fdr_cutoff',array('type' => 'number','div' => false,'lang' => 'en','min'=> '0.0', 'max' => '1.0', 'step' => 'any', 'label' => false, 'placeholder'=>'FDR cutoff (e.g. 0.01, 1e-8)'));
		?>
		</div>
		<!--
		<div class="ui toggle checkbox">
		<?php
			echo $this->Form->checkbox('all_fdrs');
			echo $this->Form->label('all_fdrs','All FDRs');
		?>
		</div>
		-->
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
			$anyTrait = 'any QTL identification source';
			echo $this->Form->label('qtl_source','Show traits from',array('class' => 'normal-style'));
			echo $this->Form->select('qtl_source',$traitAttrs,array('class' => 'ui fluid search dropdown', 'empty' => $anyTrait, 'label' => false, 'multiple' => true));
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
    <?php endif; ?>
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
            <th><?php echo $this->Paginator->sort('an_group','Analysis Group'); ?></th>
            <th><?php echo $this->Paginator->sort('cell_type','Cell Type'); ?></th>
            <th><?php echo $this->Paginator->sort('qtl_source','Qtl Source'); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('CHR','Coordinates',array('class' => 'nowrap')); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('SNP_pos','SNP',array('class' => 'nowrap')); ?></th>
	    <th class="center aligned"><?php echo $this->Paginator->sort('altAF','Alt AF',array('class' => 'nowrap')); ?></th>
	    <th class="center aligned"><?php echo $this->Paginator->sort('MAF','MAF',array('class' => 'nowrap')); ?></th>
	    <th class="center aligned"><?php echo $this->Paginator->sort('SNP_pos','pos',array('class' => 'nowrap')); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('pv','P-value (Bonferroni)'); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('FDR','FDR',array('class' => 'nowrap')); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('beta','Beta',array('class' => 'nowrap')); ?></th>
	    <th class="center aligned">HVar?</th>
            <th class="center aligned"><?php echo $this->Paginator->sort('gene','Gene',array('class' => 'nowrap')); ?></th>
            <!-- <th><?php echo $this->Paginator->sort('exon_number','Exon #'); ?></th> -->
		<!--
            <th><?php echo $this->Paginator->sort('ensembl_transcript_id','Transcript',array('class' => 'nowrap')); ?></th>
            <th><?php echo $this->Paginator->sort('histone','Histone'); ?></th>
		-->
		<th><?php echo $this->Paginator->sort('array_probe','Meth probe',array('class' => 'nowrap')); ?></th>
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
                <td><?php if(isset($h['an_group'])) { echo $h['an_group']; }?></td>
                <td><?php echo $this->element('WP10/celltype',array('h' => &$h)); ?></td>
                <td><?php echo $this->element('WP10/qtl_source',array('h' => &$h)); ?></td>
                <td><?php echo $this->element('WP10/coordinates',array('h' => &$h,'ENSEMBL_BASE' => &$ENSEMBL_BASE)); ?></td>
                <td class="center aligned"><?php echo $this->element('WP10/snp',array('h' => &$h,'ENSEMBL_BASE' => &$ENSEMBL_BASE)); ?></td>
                <td><?php if(isset($h['altAF'])) { echo sprintf("%.4G",$h['altAF']); }?></td>
		<td><?php
		if(isset($h['dbSnpRef'])) {
			$dbSnpRefs = $h['dbSnpRef'];
			$dbSnpAlts = $h['dbSnpAlt'];
			$MAFs = $h['MAF'];
		} else {
			$dbSnpRefs = array(null);
			$dbSnpAlts = array(null);
			$MAFs = array(null);
		}
		
		foreach($MAFs as &$MAF) {
			echo $this->Html->tag('div',$MAF!==null ? $MAF : 'NA');
		}
		?></td>
		<td><?php echo $this->element('WP10/pos',array('h' => &$h)); ?></td>
                <td><?php if(isset($h['pv'])) { echo sprintf("%.4G",$h['pv']); }?></td>
                <td><?php if(isset($h['metrics']) && isset($h['metrics']['FDR'])) { echo sprintf("%.4G",$h['metrics']['FDR']); }?></td>
                <td><?php if(isset($h['metrics']) && isset($h['metrics']['beta'])) { echo sprintf("%.4G",$h['metrics']['beta']); }?></td>
		<td class="center aligned"><?php
			if(isset($h['variability'])) {
				$hypervar_wid = "hypervar_".$h['variability']['hvar_id'];
				$click_hypervar_wid = "click_hypervar_".$rowCounter;
				echo $this->Html->tag('i','',array('class' => 'link line chart blue icon','id' => $click_hypervar_wid));
				$this->Js->get('#'.$click_hypervar_wid)->event('click',"$('#".$hypervar_wid."').modal('show');");
				echo $this->Js->writeBuffer(); // Write cached scripts
				if(!isset($seen_hypervar[$hypervar_wid])) {
					echo $this->element('Hypervariability/result',array('hypervar' => &$h['variability'],'ENSEMBL_BASE' => &$ENSEMBL_BASE,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver,'hypervar_wid' => $hypervar_wid));
					$seen_hypervar[$hypervar_wid] = null;
				}
			}
		?></td>
                <td><?php echo $this->element('WP10/gene_transcript',array('h' => &$h,'ENSEMBL_BASE' => &$ENSEMBL_BASE,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver)); ?></td>
		<!--
                <td><?php echo $this->element('WP10/gene_transcript',array('h' => &$h,'ENSEMBL_BASE' => &$ENSEMBL_BASE,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver,'isTranscript' => true)); ?></td>
		-->
                <td><?php echo $this->element('WP10/methprobe',array('h' => &$h,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver)); ?></td>
		<td>
			<!--
			<i class="link info circle blue icon" data-position="left center"></i>
			<span class="link info plus-info icon" data-position="left center">+&nbsp;info</span>
			-->
			<span class="plus-info noselect info circle icon" data-position="left center">+&nbsp;info</span>
			<div class="ui flowing popup">
				<div class="ui list">
			<?php
			echo $this->Html->div('item center aligned',$this->Html->div('ui blue horizontal large label','Additional data'));
			$qtlId = $h['gene_id'];
			echo $this->Html->div('item',$this->Html->div('ui horizontal label','Trait Id').$this->Html->tag('span',$qtlId));
			if(isset($h['exonNumber'])) { echo $this->Html->div('item',$this->Html->div('ui horizonal label','Exon').$this->Html->tag('span',$h['exonNumber'])); }
			if(isset($h['histone'])) { echo $this->Html->div('item',$this->Html->div('ui horizonal label','Histone').$this->Html->tag('span',$h['histone'])); }
			if(isset($h['probeId'])) {
				echo $this->Html->div('item',$this->Html->div('ui horizonal label','Probe Id').$this->Html->link(
					$h['probeId'],
					$UCSC_SERVER.'cgi-bin/hgTracks?clade=mammal&org=Human&db='.$UCSC_genome_ver.'&position=' . $h['probeId'],
					array(
						'target' => '_blank'
					)
				));
			}
			if(isset($h['splice'])) {
				$splice = is_array($h['splice']) ? $h['splice'] : array($h['splice']);
				echo $this->Html->div('item',$this->Html->div('ui horizontal label','Splice coords').$this->Html->div('ui bulleted list',join('',array_map(function ($spItem) {
	return $this->Html->div('item',$spItem);
},$splice))));
			}
			$metrics = array();
			if(isset($h['metrics'])) {
				$metrics += $h['metrics'];
			}
			if(! isset($metrics['pv']) && isset($h['pv'])) {
				$metrics['pv'] = $h['pv'];
			}
			if(isset($h['qv'])) {
				$metrics['qv'] = $h['qv'];
			}
			if(isset($h['F'])) {
				$metrics['F'] = $h['F'];
			}
			if(!empty($metrics)) {
				echo $this->Html->div('item',$this->Html->div('ui label','All Metrics').
					'<table class="ui definition table">'.$this->Html->tableCells(
						array_map(function($key,$val) {
								return array($key,sprintf("%.6G",$val));
							},
							array_keys($metrics),
							array_values($metrics)
						)
					).'</table>'
				);
			}
			if($h['qtl_source']!='sqtls') {
				echo $this->Html->link(
					$this->Html->tag('i','',array('class' => 'download icon')).' Raw data for this QTL',
					array(
						'controller' => $this->params['controller'],
						'action' => 'bulkqtl',
						'full_base' => true,
						// Now, the parameters for the link
						$h['an_group'],$h['cell_type'],$h['qtl_source'],strtr($qtlId,':','_')
					),
					array(
						'target' => '_blank',
						'escape' => false
					)
				);
			}
			?>
				</div>
			</div>
		</td>
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
<div>Supporting data is available at <a href="ftp://ftp.ebi.ac.uk/pub/databases/blueprint/blueprint_Epivar/" target="_blank">ftp://ftp.ebi.ac.uk/pub/databases/blueprint/blueprint_Epivar/</a></div>
</div>
<div class="bibref"><u>Reference</u>: <a href="http://www.cell.com/cell/abstract/S0092-8674(16)31446-5" target="_blank">Chen L., Ge B., Casale F.P., Vasquez L., Kwan T., Garrido-Martín D., Watt S., Yan Y., Kundu K., Ecker S., et al. (2016) Genetic Drivers of Epigenetic and Transcriptional Variation in Human Immune Cells. Cell, 167, 1398–1414.e24</a><br /><u>DOI</u>: <a href="http://dx.doi.org/10.1016%2Fj.cell.2016.10.026" target="_blank">10.1016/j.cell.2016.10.026</a></div>
