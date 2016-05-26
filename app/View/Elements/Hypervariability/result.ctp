<?php
$CELLTYPE_NAMES = array(
	'neut'	=> 'neutrophils',
	'tcel'	=> 'T cells',
	'mono'	=> 'monocytes'
);
$IMAGE_POSTFIX = array(
	'gene'	=>	'medium',
	'meth'	=>	'small'
);
$num_cell_type = count($hypervar['cell_type']);

$analysis_source = $this->element('WP10/qtl_source',array('h' => &$hypervar,'doLarge' => true));
?>
<div class="ui fullscreen modal" id="<?php echo $hypervar_wid; ?>" style="height: calc(100% - 7em);">
	<i class="close icon"></i>
	<div class="header" style="text-align: center;">
		Hypervariability on <?php
			echo isset($hypervar['gene_name']) ? $hypervar['gene_name'].' ('.$hypervar['hvar_id'].')' : $hypervar['hvar_id'];
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
					<?php echo $this->element('WP10/celltype',array('h' => &$hypervar,'doLarge' => true)); ?>
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
					<?php echo $hypervar['hvar_id']; ?>
				</div>
			</div>
			<?php if(isset($hypervar['gene_name']) || isset($hypervar['ensemblGeneId'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Gene</div>
					<?php echo $this->element('WP10/gene_transcript',array('h' => &$hypervar,'ENSEMBL_BASE' => &$ENSEMBL_BASE,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver)); ?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['probeId'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Probe ID</div>
					<?php echo $this->element('WP10/methprobe',array('h' => &$hypervar,'UCSC_SERVER' => &$UCSC_SERVER,'UCSC_genome_ver' => &$UCSC_genome_ver)); ?>
				</div>
			</div>
			<?php endif;?>
			<?php if(isset($hypervar['gene_chrom'])): ?>
			<div class="top aligned item">
				<div class="content">
					<div class="header">Chromosome</div>
					<?php echo $this->element('WP10/coordinates',array('h' => &$hypervar,'ENSEMBL_BASE' => &$ENSEMBL_BASE)); ?>
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
					<?php echo $this->element('WP10/pos',array('h' => &$hypervar)); ?>
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
					<div class="header">Variability associated with</div>
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
			$imagePostFix = array_key_exists($hypervar['qtl_source'],$IMAGE_POSTFIX) ? '_'.$IMAGE_POSTFIX[$hypervar['qtl_source']] : '';
			$imageRoute = Router::url(
				array(
					is_array($hypervar['cell_type']) ? $hypervar['cell_type'][0] : $hypervar['cell_type'],
					$hypervar['qtl_source'],
					$hypervar['hvar_id'].$imagePostFix,
					'controller' => 'hypervariability',
					'action' => 'chart',
				)
			);
			echo $this->Html->div('div-image','',array("style" => "background-image: url('$imageRoute');"));
			/*
			echo $this->Html->image(
				array(
					is_array($hypervar['cell_type']) ? $hypervar['cell_type'][0] : $hypervar['cell_type'],
					$hypervar['qtl_source'],
					$hypervar['hvar_id'],
					'controller' => 'hypervariability',
					'action' => 'chart',
				)
				,
				array(
					"style" => "max-width:100%;max-height:100%;"
				)
			);
			*/
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
		<?php
		echo $this->Html->tag('br');
		echo $this->Html->link(
			$this->Html->tag('i','',array('class' => 'download icon')).'download full resolution chart',
			array(
				is_array($hypervar['cell_type']) ? $hypervar['cell_type'][0] : $hypervar['cell_type'],
				$hypervar['qtl_source'],
				$hypervar['hvar_id'],
				'controller' => 'hypervariability',
				'action' => 'chart',
			)
			,
			array(
				'target' => '_blank',
				'style' => 'font-size:0.75em;',
				'confirm' => 'Are you sure you wish to download this chart?',
				'escape' => false
			)
		);
		?>
		<div class="ui blue deny right labeled icon button">
			Return
			<i class="reply icon"></i>
		</div>
	</div>
</div>
