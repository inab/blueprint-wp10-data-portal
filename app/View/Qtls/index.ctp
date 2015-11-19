<?php
$ENSEMBL_BASE = 'http://jan2013.archive.ensembl.org/Homo_sapiens/';
$this->Html->css('blueprint-qtls',array('inline' => false));
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
			$attrs = array();
			foreach ($chromosomes as $chro) {
				$attrs[$chro] = 'Chromosome '.$chro;
			}
			echo $this->Form->input('chromosome',array('div' => 'field','empty' => 'Any chromosome', 'options' => $attrs, 'label' => false, 'class' => 'ui search dropdown', 'default' => ''));
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
			echo $this->Form->reset("Reset",array('div' => false,'class'=>'ui secondary button'));
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
            <th><?php echo $this->Paginator->sort('CHR','Coordinates'); ?></th>
            <th><?php echo $this->Paginator->sort('SNP','SNP'); ?></th>
            <th><?php echo $this->Paginator->sort('meth.probe','Meth probe'); ?></th>
            <th><?php echo $this->Paginator->sort('gid.1','Gene'); ?></th>
            <th><?php echo $this->Paginator->sort('ensembl_gene_id','Ensembl Id'); ?></th>
            <th><?php echo $this->Paginator->sort('mon.fdr','Monocyte FDR'); ?></th>
            <th><?php echo $this->Paginator->sort('neu.fdr','Neutrophil FDR'); ?></th>
            <th><?php echo $this->Paginator->sort('tcl.fdr','T-cell FDR'); ?></th>
        </thead>
        <tbody>
            <?php foreach ($res['hits']['hits'] as $h):?>
            <tr>
                <td><?php
		$coordinates = $h['_source']['CHR'].':'.$h['_source']['start_position'].'-'.$h['_source']['end_position'];
		echo $this->Html->link(
			$coordinates,
			$ENSEMBL_BASE.'Location/View?r=' . $coordinates,
			array(
				'target' => '_blank'
			)
		);
		?></td>
                <td><?php echo $this->Html->link(
			$h['_source']['SNP'],
			'http://www.ncbi.nlm.nih.gov/SNP/snp_ref.cgi?searchType=adhoc_search&type=rs&rs=' . $h['_source']['SNP'],
			array(
				'target' => '_blank'
			)
		);?></td>
                <td><?php echo $this->Html->link(
			$h['_source']['meth.probe'],
			'http://genome-euro.ucsc.edu/cgi-bin/hgTracks?clade=mammal&org=Human&db=hg19&position=' . $h['_source']['meth.probe'],
			array(
				'target' => '_blank'
			)
		);?></td>
                <td><?php echo $this->Html->link(
			$h['_source']['gid.1'],
			'http://blueprint-data.bsc.es/#/?q=gene:' . $h['_source']['ensembl_gene_id'] . '&w=500',
			array(
				'target' => '_blank'
			)
		);?></td>
                <td><?php echo $this->Html->link(
			$h['_source']['ensembl_gene_id'],
			$ENSEMBL_BASE.'Gene/Summary?db=core&g=' . $h['_source']['ensembl_gene_id'],
			array(
				'target' => '_blank'
			)
		);?></td>
                <td><?php if(isset($h['_source']['mon.fdr'])) { echo sprintf("%.4G",$h['_source']['mon.fdr']); }?></td>
                <td><?php if(isset($h['_source']['neu.fdr'])) { echo sprintf("%.4G",$h['_source']['neu.fdr']); }?></td>
                <td><?php if(isset($h['_source']['tcl.fdr'])) { echo sprintf("%.4G",$h['_source']['tcl.fdr']); }?></td>
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
