<?php
$ENSEMBL_BASE = 'http://jan2013.archive.ensembl.org/Homo_sapiens/';
$this->Html->css('blueprint-qtls',array('inline' => false));
$this->Html->script('blueprint-qtls',array('inline' => false));
$this->Paginator->options(array('url' => $this->passedArgs));
?>

<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <h1 class="ui header">Search</h1>
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
                <div class="ui slider checkbox">
		<?php
			echo $this->Form->checkbox('rna_qtls');
			echo $this->Form->label('rna_qtls','RNA based QTLs');
		?>
                </div>
                <div class="ui slider checkbox">
		<?php
			echo $this->Form->checkbox('meth_qtls');
			echo $this->Form->label('meth_qtls','Meth QTLs');
		?>
                </div>
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
            <th><?php echo $this->Paginator->sort('Pos','pos'); ?></th>
            <th><?php echo $this->Paginator->sort('pv','P-value'); ?></th>
            <th><?php echo $this->Paginator->sort('qv','Q-value'); ?></th>
            <!-- <th>Qtl Id</th> -->
            <th><?php echo $this->Paginator->sort('gene','Gene'); ?></th>
            <th><?php echo $this->Paginator->sort('ensembl_gene_id','Ensembl Gene Id'); ?></th>
            <th><?php echo $this->Paginator->sort('exon_number','Exon #'); ?></th>
            <th><?php echo $this->Paginator->sort('ensembl_transcript_id','Ensembl Transcript Id'); ?></th>
            <th><?php echo $this->Paginator->sort('histone','Histone'); ?></th>
            <th><?php echo $this->Paginator->sort('array_probe','Meth probe'); ?></th>
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
                <td><?php if(isset($h['_source']['pos'])) { echo sprintf("%u",$h['_source']['pos']); }?></td>
                <td><?php if(isset($h['_source']['pv'])) { echo sprintf("%.4G",$h['_source']['pv']); }?></td>
                <td><?php if(isset($h['_source']['qv'])) { echo sprintf("%.4G",$h['_source']['qv']); }?></td>
                <!-- <td><?php
		$qtlId = $h['_source']['gene_id'];
		echo $this->Html->tag('span',$qtlId);
		?></td> -->
                <td><?php
		if(isset($h['_source']['gene_name'])) {
			echo $this->Html->link(
				$h['_source']['gene_name'],
				'http://blueprint-data.bsc.es/#/?q=gene:' . $h['_source']['ensemblGeneId'] . '&w=500',
				array(
					'target' => '_blank'
				)
			);
		}
		?></td>
                <td><?php
		if(isset($h['_source']['ensemblGeneId'])) {
			echo $this->Html->link(
				$h['_source']['ensemblGeneId'],
				$ENSEMBL_BASE.'Gene/Summary?db=core&g=' . $h['_source']['ensemblGeneId'],
				array(
					'target' => '_blank'
				)
			);
		}
		?></td>
		<td><?php if(isset($h['_source']['exonNumber'])) { echo $h['_source']['exonNumber']; }?></td>
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
                <td><?php if(isset($h['_source']['histone'])) { echo $this->Html->tag('span',$h['_source']['histone']); }?></td>
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
