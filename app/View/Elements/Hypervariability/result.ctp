<?php
$ANALYSIS_NAMES = array(
	'meth'	=> 'methylation arrays',
	'gene'	=> 'gene expression'
);
$CELLTYPE_NAMES = array(
	'neut'	=> 'neutrophils',
	'tcel'	=> 'T cells',
	'mono'	=> 'monocytes'
);
$num_cell_type = count($hypervar['cell_type']);
$analysis_source = $ANALYSIS_NAMES[$hypervar['qtl_source']];;
?>
<div class="ui fullscreen modal" id="<?php echo $hypervar_wid; ?>" style="height: calc(100% - 7em);">
	<i class="close icon"></i>
	<div class="header" style="text-align: center;">
		Hypervariability on <?php
			echo isset($hypervar['gene_name']) ? $hypervar['gene_name'].' ('.$hypervar['qtl_id'].')' : $hypervar['qtl_id'];
		?> in <?php
			foreach($hypervar['cell_type'] as $icell => &$celltype) {
				if($icell > 0) {
					echo (($icell+1) != $num_cell_type)? ', ' : ' and ';
				}
				echo $CELLTYPE_NAMES[$celltype];
			}
		?>, identified using <?php echo $analysis_source; ?>
	</div>
	<div style="display: flex; flex-direction: column;height: calc(100% - 10em);justify-content:space-between; align-items:center;">
		<div class="ui horizontal list" style="text-align:center;">
			<div class="top aligned item">
				<div class="content">
					<div class="header">Cell type(s)</div>
					<?php
					foreach($hypervar['cell_type'] as $icell => &$celltype) {
						if($icell > 0) {
							echo $this->Html->tag('br');
						}
						echo $CELLTYPE_NAMES[$celltype];
					}
					?>
				</div>
			</div>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Source</div>
					<?php echo $analysis_source; ?>
				</div>
			</div>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Hypervariability id</div>
					<?php echo $hypervar['qtl_id']; ?>
				</div>
			</div>
			<?php if(isset($hypervar['gene_name']) || isset($hypervar['ensemblGeneId'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Gene</div>
					<?php
					if(isset($hypervar['gene_name'])) {
						echo $hypervar['gene_name'];
					}
					if(isset($hypervar['ensemblGeneId'])) {
						if(isset($hypervar['gene_name'])) {
							echo ' (';
						}
						
						echo $hypervar['ensemblGeneId'];
						
						if(isset($hypervar['gene_name'])) {
							echo ')';
						}
					}
					?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['probeId'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Probe ID</div>
					<?php echo $hypervar['probeId'];?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['gene_chrom'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Chromosome</div>
					<?php echo $hypervar['gene_chrom']; ?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['arm'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Arm</div>
					<?php echo $hypervar['arm']; ?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['pos'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Position</div>
					<?php echo $hypervar['pos']; ?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['feature'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Feature</div>
					<?php echo $hypervar['feature']; ?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['chromatin_state'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Chromatin state(s)</div>
					<?php
						foreach($hypervar['chromatin_state'] as $iChroState => &$chromatin_state) {
							if($iChroState > 0) {
								echo $this->Html->tag('br');
							}
							echo $CELLTYPE_NAMES[$chromatin_state['cell_type']] .': '.$chromatin_state['state'];
						}
					?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['go_term'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">GO Terms: <?php echo count($hypervar['go_term']); ?></div>
					<?php echo $this->Html->nestedList($hypervar['go_term'],array('style' => 'text-align: left; max-height: 5em; overflow-y:auto;margin-top:0px;'),array(),'ol'); ?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['variability']) && count($hypervar['variability']) > 0): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Variability</div>
					<?php
						foreach($hypervar['variability'] as $iVari => &$variability) {
							if($iVari > 0) {
								echo $this->Html->tag('br');
							}
							echo $variability;
						}
					?>
				</div>
			</div>
			<?php endif;?>
		</div>
		<div style="height: calc(100% - 7em);width: calc(100% - 2em);text-align:center;">
			<?php
			echo $this->Html->image(
				array(
					is_array($hypervar['cell_type']) ? $hypervar['cell_type'][0] : $hypervar['cell_type'],
					$hypervar['qtl_source'],
					$hypervar['qtl_id'],
					'controller' => 'hypervariability',
					'action' => 'chart',
				)
				,
				array(
					"style" => "max-width:100%;max-height:100%;"
				)
			);
			?>
		</div>
		<!--
		<div class="description">
			<div class="ui header">We've auto-chosen a profile image for you.</div>
			<p>We've grabbed the following image from the <a href="https://www.gravatar.com" target="_blank">gravatar</a> image associated with your registered e-mail address.</p>
			<p>Is it okay to use this photo?</p>
		</div>
		-->
	</div>
	<div class="actions">
	<!--
		<div class="ui black deny button">
		Nope
		</div>
	-->
		<div class="ui blue deny right labeled icon button">
			Return
			<i class="reply icon"></i>
		</div>
	</div>
</div>
