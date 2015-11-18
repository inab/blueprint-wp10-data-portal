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
		<br />
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
            <div class="inline field">
		<?php
			echo $this->Form->submit("Search",array('div' => false,'class'=>'ui primary button submit'));
			echo $this->Form->reset("Reset",array('div' => false,'class'=>'ui secondary button'));
		?>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <table class="ui table">
        <thead>
            <th>SNP</th>
            <th>Meth probe</th>
            <th>Gene</th>
            <th>Ensembl Id</th>
            <th>Monocyte FDR</th>
            <th>Neutrophil FDR</th>
            <th>T-cell FDR</th>
        </thead>
        <tbody>
            <?php foreach ($res['hits']['hits'] as $h):?>
            <tr>
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
			'http://jan2013.archive.ensembl.org/Homo_sapiens/Gene/Summary?db=core&g=' . $h['_source']['ensembl_gene_id'],
			array(
				'target' => '_blank'
			)
		);?></td>
                <td><?php echo $h['_source']['mon.fdr'];?></td>
                <td><?php echo $h['_source']['neu.fdr'];?></td>
                <td><?php echo $h['_source']['tcl.fdr'];?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>
