<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <h1 class="ui header">Search</h1>
    <div class="ui secondary form segment">
	<?php
		echo $this->Form->create();
	?>
        <div class="three fields">
            <div class="field">
                <select name="chromosome" class="ui search dropdown">
			<?php
				echo $this->Html->tag('option','Any chromosome',array('value' => '','selected' => 'selected'));
				foreach ($chromosomes as $chro) {
					echo $this->Html->tag('option','Chromosome '.$chro,array('value' => $chro));
				}
			?>
                </select>
            </div>
            <div class="field"><input type="text" pattern="\d+" name="chromosome_start" placeholder="Start"></div>
            <div class="field"><input type="text" pattern="\d+" name="chromosome_end" placeholder="End"></div>
        </div>
        <div class="three fields">
            <div class="field"><input type="text" name="gene" placeholder="Gene"></div>
            <div class="field"><input type="text" pattern="rs.+" name="SNP" placeholder="SNP"></div>
            <div class="field"><input type="text" name="array_probe" placeholder="Meth probe"></div>
        </div>
        <div class="two fields">
            <div class="inline field">
		<input type="number" min="0.0" max="1.0" name="fdr_cutoff" placeholder="FDR cutoff (e.g. 0.01)">
                &nbsp;
                <div class="ui slider checkbox">
                  <input type="checkbox">
                  <label>RNA based QTLs</label>
                </div>
                <label></label>
                <div class="ui slider checkbox">
                  <input type="checkbox">
                  <label>Meth QTLs</label>
                </div>
                <label></label>
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
            <th>Monocyte</th>
            <th>Neutrophil</th>
            <th>T-cell</th>
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
