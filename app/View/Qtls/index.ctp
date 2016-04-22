<?php
$ENSEMBL_BASE = 'http://jan2013.archive.ensembl.org/Homo_sapiens/';
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
		<?php
			$attrs = array('any' => 'Any chromosome');
			foreach ($chromosomes as $chro) {
				$attrs[$chro] = 'Chromosome '.$chro;
			}
			echo $this->Form->input('chromosome',array('div' => 'field','empty' => $attrs['any'], 'options' => $attrs, 'label' => false, 'class' => 'ui search dropdown', 'default' => 'any'));
			echo $this->Form->input('chromosome_start',array('type' => 'text', 'div' => 'field','label' => false, 'pattern' => '\d+', 'placeholder'=>'Start'));
			echo $this->Form->input('chromosome_end',array('type' => 'text', 'div' => 'field','label' => false, 'pattern' => '\d+', 'placeholder'=>'End'));
		?>
        </div>
        <div class="three fields">
		<?php
			echo $this->Form->input('gene',array('type' => 'text', 'div' => 'field','label' => false, 'placeholder'=>'Gene'));
			echo $this->Form->input('SNP',array('type' => 'text', 'div' => 'field','label' => false, 'pattern' => 'rs.+', 'placeholder'=>'SNP'));
			echo $this->Form->input('array_probe',array('type' => 'text', 'div' => 'field','label' => false, 'placeholder'=>'Meth probe'));
		?>
        </div>
        <div class="two fields">
            <div class="inline field">
		<?php
			echo $this->Form->input('fdr_cutoff',array('type' => 'number','div' => false,'min'=> '0.0', 'max' => '1.0', 'step' => 'any', 'label' => false, 'placeholder'=>'FDR cutoff (e.g. 0.01)'));
		?>
                <div class="ui slider checkbox">
		<?php
			echo $this->Form->checkbox('all_fdrs');
			echo $this->Form->label('all_fdrs','All FDRs');
		?>
                </div>
            </div>
            <div class="inline field">
		Show traits from 
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
			echo $this->Form->select('qtl_source',$traitAttrs,array('class' => 'ui fluid search dropdown', 'empty' => $anyTrait, 'label' => false, 'multiple' => true));
		?>
            </div>
        </div>
        <div class="two fields">
            <div class="field">
		<?php
			echo $this->Form->reset("Reset form to search values",array('div' => false,'class'=>'ui negative button'));
			echo $this->Form->button("Clear all fields",array('type' => 'button','div' => false,'onclick'=>'clearJQueryForm(this.form)','class'=>'ui secondary button'));
		?>
            </div>
            <div class="field">
		<?php
			echo $this->Form->submit("Search",array('div' => false,'class'=>'ui primary button submit'));
		?>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
<?php if(isset($res['hits'])):?>
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
            <th><?php echo $this->Paginator->sort('CHR','Coordinates'); ?></th>
            <th><?php echo $this->Paginator->sort('SNP','SNP'); ?></th>
            <!-- <th><?php echo $this->Paginator->sort('Pos','pos'); ?></th> -->
            <th><?php echo $this->Paginator->sort('pv','P-value'); ?></th>
            <th><?php echo $this->Paginator->sort('qv','Q-value'); ?></th>
            <!-- <th>Qtl Id</th> -->
            <th><?php echo $this->Paginator->sort('gene','Gene'); ?></th>
            <th><?php echo $this->Paginator->sort('ensembl_gene_id','Ensembl Gene Id'); ?></th>
            <!-- <th><?php echo $this->Paginator->sort('exon_number','Exon #'); ?></th> -->
            <th><?php echo $this->Paginator->sort('ensembl_transcript_id','Ensembl Transcript Id'); ?></th>
		<!--
            <th><?php echo $this->Paginator->sort('histone','Histone'); ?></th>
		-->
		<th><?php echo $this->Paginator->sort('array_probe','Meth probe'); ?></th>
		<th><i class="info icon" data-position="left center"></i></th>
        </thead>
        <tbody>
            <?php foreach ($res['hits']['hits'] as $h): ?>
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
                <td><?php
		if(isset($h['_source']['snp_id'])) {	
			echo $this->Html->link(
				$h['_source']['snp_id'],
				'http://www.ncbi.nlm.nih.gov/SNP/snp_ref.cgi?searchType=adhoc_search&type=rs&rs=' . $h['_source']['snp_id'],
				array(
					'target' => '_blank'
				)
			);
		}
		?></td>
                <!-- <td><?php if(isset($h['_source']['pos'])) { echo sprintf("%u",$h['_source']['pos']); }?></td> -->
                <td><?php if(isset($h['_source']['pv'])) { echo sprintf("%.4G",$h['_source']['pv']); }?></td>
                <td><?php if(isset($h['_source']['qv'])) { echo sprintf("%.4G",$h['_source']['qv']); }?></td>
                <!-- <td><?php
		$qtlId = $h['_source']['gene_id'];
		echo $this->Html->tag('span',$qtlId);
		?></td> -->
                <td><?php
		if(isset($h['_source']['gene_name'])) {
			$gene_names = is_array($h['_source']['gene_name']) ? $h['_source']['gene_name'] : array($h['_source']['gene_name']);
			$ensemblGeneIds = is_array($h['_source']['ensemblGeneId']) ? $h['_source']['ensemblGeneId'] : array($h['_source']['ensemblGeneId']);
			
			foreach($ensemblGeneIds as $indexEns => $ensemblGeneId) {
				$ensemblGeneIdTrimmed = $ensemblGeneId;
				$ensemblDotPos = strrpos($ensemblGeneIdTrimmed,'.');
				if($ensemblDotPos) {
					$ensemblGeneIdTrimmed = substr($ensemblGeneIdTrimmed,0,$ensemblDotPos);
				}
				echo $this->Html->link(
					$gene_names[$indexEns],
					'http://blueprint-data.bsc.es/#/?q=gene:' . $ensemblGeneIdTrimmed . '&w=500',
					array(
						'target' => '_blank'
					)
				);
				echo $this->Html->tag('br');
			}
		}
		?></td>
                <td><?php
		if(isset($h['_source']['ensemblGeneId'])) {
			$ensemblGeneIds = is_array($h['_source']['ensemblGeneId']) ? $h['_source']['ensemblGeneId'] : array($h['_source']['ensemblGeneId']);
			foreach($ensemblGeneIds as $ensemblGeneId) {
				echo $this->Html->link(
					$ensemblGeneId,
					$ENSEMBL_BASE.'Gene/Summary?db=core&g=' . $ensemblGeneId,
					array(
						'target' => '_blank'
					)
				);
				echo $this->Html->tag('br');
			}
		}
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
			<i class="link info circle blue icon" data-position="left center"></i>
			<div class="ui flowing popup">
				<div class="ui list">
			<?php
			echo $this->Html->div('item',$this->Html->div('ui blue horizontal large label','Additional data'));
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
            <?php endforeach;?>
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
