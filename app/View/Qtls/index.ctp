<?php
$ENSEMBL_BASE = 'http://jan2013.archive.ensembl.org/Homo_sapiens/';
$BDAP_cell_types = array(
	'CL:0000096',	// mature neutrophil
	'CL:0000560',	// band form neutrophil
	'CL:0011114',	// segmented neutrophil of bone marrow

	'CL:0000576',	// monocyte
	'CL:0000860',	// classical monocyte
	'CL:0002057',	// CD14-positive, CD16-negative classical monocyte

	'CL:0002419',	// mature T cell
	'CL:0000815',	// regulatory T cell
);
$BDAP_visible_charts = array(
	'methyl',
	'exp_g',
	'exp_t',
	'dnase',
	'cseq_broad H3K4me1',
	'cseq_narrow H3K4me1',
	'cseq_broad H3K27ac',
	'cseq_narrow H3K27ac'
);

$BDAP_normal = 'PATO:0000461';


$this->Html->css('blueprint-qtls',array('inline' => false));
$this->Html->script('blueprint-qtls',array('inline' => false));
$this->Paginator->options(array('url' => $this->passedArgs));
?>

<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <h1 class="ui header">WP10 QTLs Search</h1>
    <div class="ui secondary form segment">
	<?php
		echo $this->Form->create();
	?>
        <div class="three fields">
		<div class="field">
		<?php
			$attrs = array('any' => 'Any chromosome');
			foreach ($chromosomes as $chro) {
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
		<div class="ui toggle checkbox">
		<?php
			echo $this->Form->checkbox('all_fdrs');
			echo $this->Form->label('all_fdrs','All FDRs');
		?>
		</div>
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
			$traitAttrs = array(
				'gene' => 'gene',
				'exon' => 'exon',
				'cufflinks' => 'Cufflinks',
				'meth' => 'methylation array',
				'sj' => 'splice junction',
				'psi' => 'percent splice-in',
				'K27AC' => 'H3K27AC ChIP-Seq peaks',
				'K4ME1'=> 'H3K4ME1 ChIP-Seq peaks',
				'sqtls' => 'sQTLseekeR'
			);
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
</div>
<?php
	if(isset($dHandler['hits']) || isset($dHandler['_scroll_id'])):
	//if(isset($dHandler)):
		$res = $ctl->nextBatch($dHandler);
?>
<div class="sixteen wide left aligned column">
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
						'controller' => 'qtls',
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
			endif
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
            <th>Cell Type</th>
            <th>Qtl Source</th>
            <th class="center aligned"><?php echo $this->Paginator->sort('CHR','Coordinates'); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('SNP_pos','SNP'); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('pv','P-value'); ?></th>
            <th class="center aligned"><?php echo $this->Paginator->sort('qv','Q-value'); ?></th>
            <!-- <th>Qtl Id</th> -->
            <th class="center aligned"><?php echo $this->Paginator->sort('gene','Gene'); ?></th>
            <!-- <th><?php echo $this->Paginator->sort('exon_number','Exon #'); ?></th> -->
            <th><?php echo $this->Paginator->sort('ensembl_transcript_id','Ensembl Transcript Id'); ?></th>
		<!--
            <th><?php echo $this->Paginator->sort('histone','Histone'); ?></th>
		-->
		<th><?php echo $this->Paginator->sort('array_probe','Meth probe'); ?></th>
		<th class="center aligned"><i class="info circle icon" data-position="left center"></i></th>
        </thead>
        <tbody>
	<?php
		while(count($res['hits']['hits']) > 0):
			foreach ($res['hits']['hits'] as $h):
	?>
            <tr>
                <td><?php
		$cellType = $h['_source']['cell_type'];
		echo $this->Html->tag('span',$cellType);
		?></td>
                <td><?php
		$qtlSource = $h['_source']['qtl_source'];
		echo $this->Html->tag('span',$qtlSource);
		?></td>
                <td><?php
		$coordinates = $h['_source']['gene_chrom'].':'.$h['_source']['gene_start'].'-'.$h['_source']['gene_end'];
		echo $this->Html->link(
			$coordinates,
			$ENSEMBL_BASE.'Location/View?r=' . $coordinates,
			array(
				'target' => '_blank'
			)
		);
		?></td>
                <td class="center aligned"><?php
		if(isset($h['_source']['snp_id'])) {
			echo $this->Html->link(
				$h['_source']['snp_id'],
				'http://www.ncbi.nlm.nih.gov/SNP/snp_ref.cgi?searchType=adhoc_search&type=rs&rs=' . $h['_source']['snp_id'],
				array(
					'target' => '_blank'
				)
			);
			if(isset($h['_source']['pos'])) {
				echo $this->Html->tag('br');
				echo $this->Html->tag('span','('.$h['_source']['pos'].')',array('class' => 'small-pos'));
			}
		}
		?></td>
                <td><?php if(isset($h['_source']['pv'])) { echo sprintf("%.4G",$h['_source']['pv']); }?></td>
                <td><?php if(isset($h['_source']['qv'])) { echo sprintf("%.4G",$h['_source']['qv']); }?></td>
                <!-- <td><?php
		$qtlId = $h['_source']['gene_id'];
		echo $this->Html->tag('span',$qtlId);
		?></td> -->
                <td><?php
		if(isset($h['_source']['gene_name'])):
			$gene_names = is_array($h['_source']['gene_name']) ? $h['_source']['gene_name'] : array($h['_source']['gene_name']);
			$ensemblGeneIds = is_array($h['_source']['ensemblGeneId']) ? $h['_source']['ensemblGeneId'] : array($h['_source']['ensemblGeneId']);
			
			foreach($ensemblGeneIds as $indexEns => $ensemblGeneId):
				$ensemblGeneIdTrimmed = $ensemblGeneId;
				$ensemblDotPos = strrpos($ensemblGeneIdTrimmed,'.');
				if($ensemblDotPos) {
					$ensemblGeneIdTrimmed = substr($ensemblGeneIdTrimmed,0,$ensemblDotPos);
				}
				/*
				echo $this->Html->link(
					$gene_names[$indexEns],
					'http://blueprint-data.bsc.es/#/?q=gene:' . $ensemblGeneIdTrimmed . '&w=500',
					array(
						'target' => '_blank',
						'data-position' => 'top',
						'class' => 'info circle icon'
					)
				);
				*/
				echo $this->Html->tag(
					'span',
					$gene_names[$indexEns],
					array(
						'data-position' => 'top center',
						'class' => 'plus-info noselect info circle icon'
					)
				);
		?>
			<div class="ui popup">
				<div class="ui list">
			<?php
			echo $this->Html->div('item center aligned',$this->Html->div('ui blue horizontal large label','Gene: '.$gene_names[$indexEns]));
			echo $this->Html->div('item nowrap',$this->Html->div('ui horizontal label','Ensembl Gene Id').$this->Html->tag('span',$ensemblGeneId));
			?>
				</div>
				<div class="ui horizontal list">
					<div class="item">
						<a href="<?php echo 'http://www.genecards.org/cgi-bin/carddisp.pl'.'?'. http_build_query(array('gene' => $ensemblGeneIdTrimmed)); ?>" target="_blank"><?php echo $this->Html->image('GeneCards.png',array('alt' => 'GeneCards','title' => 'Search this gene on GeneCards','class' => 'itemlogo'))?></a>
					</div>
					<div class="item">
						<a href="<?php echo $ENSEMBL_BASE.'Gene/Summary'.'?'. http_build_query(array('db' => 'core','gene' => $ensemblGeneIdTrimmed)); ?>" target="_blank"><?php echo $this->Html->image('EnsEMBL.png',array('alt' => 'EnsEMBL','title' => 'Search this gene on EnsEMBL','class' => 'itemlogo'))?></a>
					</div>
					<?php
						$bdap_query = http_build_query(
							array(
								'q' => 'gene:'.$ensemblGeneIdTrimmed,
								'selectedTab' => $ensemblGeneIdTrimmed,
								'tabs' => array(
									array(
										'id' => $ensemblGeneIdTrimmed,
										'visibleTerms' => $BDAP_cell_types,
										'selectedView' => 'General',
										'initiallyShowMeanSeries' => 'false',
										'visibleCharts' => $BDAP_visible_charts,
										'treeDisplay' => 'compact',
										'filteredDisease' => $BDAP_normal
									)
								)
							)

						);
					?>
					<div class="item">
						<a href="<?php echo 'http://blueprint-data.bsc.es/#!/'.'?'. $bdap_query; ?>" target="_blank"><?php echo $this->Html->image('BDAP-logo.png',array('alt' => 'BLUEPRINT Data Analysis Portal','title' => 'Search this gene on BLUEPRINT Data Analysis Portal','class' => 'itemlogo'))?></a>
					</div>
				</div>
			</div>
		<?php
				echo $this->Html->tag('br');
			endforeach;
		endif;
		?></td>
		<!-- <td><?php if(isset($h['_source']['exonNumber'])) { echo $h['_source']['exonNumber']; }?></td> -->
                <td><?php
		if(isset($h['_source']['ensemblTranscriptId'])) {
			$ensemblTranscriptIds = is_array($h['_source']['ensemblTranscriptId']) ? $h['_source']['ensemblTranscriptId'] : array($h['_source']['ensemblTranscriptId']);
			foreach($ensemblTranscriptIds as $ensemblTranscriptId) {
				echo $this->Html->link(
					$ensemblTranscriptId,
					$ENSEMBL_BASE.'Transcript/Summary?db=core&t=' . $ensemblTranscriptId,
					array(
						'target' => '_blank'
					)
				);
				echo $this->Html->tag('br');
			}
		}
		?></td>
		<!--
                <td><?php if(isset($h['_source']['histone'])) { echo $this->Html->tag('span',$h['_source']['histone']); }?></td>
		-->
                <td><?php
		if(isset($h['_source']['probeId'])) {
			echo $this->Html->link(
				$h['_source']['probeId'],
				'http://genome-euro.ucsc.edu/cgi-bin/hgTracks?clade=mammal&org=Human&db=hg19&position=' . $h['_source']['probeId'],
				array(
					'target' => '_blank'
				)
			);
		}
		?></td>
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
			$qtlId = $h['_source']['gene_id'];
			echo $this->Html->div('item',$this->Html->div('ui horizontal label','Trait Id').$this->Html->tag('span',$qtlId));
			if(isset($h['_source']['pos'])) { echo $this->Html->div('item',$this->Html->div('ui horizonal label','SNP pos').$this->Html->tag('span',sprintf("%u",$h['_source']['pos']))); }
			if(isset($h['_source']['exonNumber'])) { echo $this->Html->div('item',$this->Html->div('ui horizonal label','Exon').$this->Html->tag('span',$h['_source']['exonNumber'])); }
			if(isset($h['_source']['histone'])) { echo $this->Html->div('item',$this->Html->div('ui horizonal label','Histone').$this->Html->tag('span',$h['_source']['histone'])); }
			if(isset($h['_source']['probeId'])) {
				echo $this->Html->div('item',$this->Html->div('ui horizonal label','Probe Id').$this->Html->link(
					$h['_source']['probeId'],
					'http://genome-euro.ucsc.edu/cgi-bin/hgTracks?clade=mammal&org=Human&db=hg19&position=' . $h['_source']['probeId'],
					array(
						'target' => '_blank'
					)
				));
			}
			if(isset($h['_source']['splice'])) {
				$splice = is_array($h['_source']['splice']) ? $h['_source']['splice'] : array($h['_source']['splice']);
				echo $this->Html->div('item',$this->Html->div('ui horizontal label','Splice coords').$this->Html->div('ui bulleted list',join('',array_map(function ($spItem) {
	return $this->Html->div('item',$spItem);
},$splice))));
			}
			$metrics = array();
			if(isset($h['_source']['F'])) {
				$metrics['F'] = $h['_source']['F'];
			}
			if(isset($h['_source']['metrics'])) {
				$metrics += $h['_source']['metrics'];
			}
			if(!empty($metrics)) {
				echo $this->Html->div('item',$this->Html->div('ui label','Other Metrics').
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
			if($qtlSource!='sqtls') {
				echo $this->Html->link(
					$this->Html->tag('i','',array('class' => 'download icon')).' Raw data for this QTL',
					array(
						'controller' => 'qtls',
						'action' => 'bulkqtl',
						'full_base' => true,
						// Now, the parameters for the link
						$cellType,$qtlSource,strtr($qtlId,':','_')
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
