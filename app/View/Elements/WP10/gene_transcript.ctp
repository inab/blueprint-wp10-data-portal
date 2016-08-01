<?php
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
?>
<?php
	if(isset($isTranscript)) {
		if(isset($h['ensemblTranscriptId'])) {
			$showLabel = 'Transcript';
			
			$ensemblIds = is_array($h['ensemblTranscriptId']) ? $h['ensemblTranscriptId'] : array($h['ensemblTranscriptId']);
			$names = &$ensemblIds;
			$showEnsemblId = true;
		}
	} elseif(isset($h['gene_name']) || isset($h['ensemblGeneId'])) {
		$showLabel = 'Gene';
		
		if(isset($h['gene_name'])) {
			$showName = true;
			$names = is_array($h['gene_name']) ? $h['gene_name'] : array($h['gene_name']);
			if(isset($h['ensemblGeneId'])) {
				$ensemblIds = is_array($h['ensemblGeneId']) ? $h['ensemblGeneId'] : array($h['ensemblGeneId']);
				$showEnsemblId = true;
			} else {
				$ensemblIds = &$names;
			}
		} else {
			$ensemblIds = is_array($h['ensemblGeneId']) ? $h['ensemblGeneId'] : array($h['ensemblGeneId']);
			$names = &$ensemblIds;
			$showEnsemblId = true;
		}
	}
	
	if(isset($showLabel)):
		foreach($ensemblIds as $indexEns => &$ensemblId):
			$ensemblIdTrimmed = $ensemblId;
			$ensemblDotPos = strrpos($ensemblIdTrimmed,'.');
			if($ensemblDotPos) {
				$ensemblIdTrimmed = substr($ensemblIdTrimmed,0,$ensemblDotPos);
			}
			/*
			echo $this->Html->link(
				$names[$indexEns],
				'http://blueprint-data.bsc.es/#/?q=gene:' . $ensemblIdTrimmed . '&w=500',
				array(
					'target' => '_blank',
					'data-position' => 'top',
					'class' => 'info circle icon'
				)
			);
			*/
		?>
		<div>
		<?php
			echo $this->Html->tag(
				'span',
				$names[$indexEns],
				array(
					'data-position' => 'top center',
					'class' => 'plus-info noselect info circle icon'
				)
			);
		?>
			<div class="ui popup">
				<div class="ui list">
				<?php
					if(!isset($isTranscript)) {
						echo $this->Html->div('item center aligned',$this->Html->div('ui blue horizontal large label','Gene: '.(isset($showName) ? $names[$indexEns] : '(not recorded)')));
					}
					echo $this->Html->div('item nowrap',$this->Html->div('ui horizontal label','Ensembl '.$showLabel.' Id').$this->Html->tag('span',isset($showEnsemblId) ? $ensemblId : '(not recorded)'));
				?>
				</div>
				<div class="ui horizontal list">
					<?php if(!isset($isTranscript)): ?>
					<div class="item">
						<a href="<?php echo 'http://www.genecards.org/cgi-bin/carddisp.pl'.'?'. http_build_query(array('gene' => $ensemblIdTrimmed)); ?>" target="_blank"><?php echo $this->Html->image('GeneCards.png',array('alt' => 'GeneCards','title' => 'Search this gene on GeneCards','class' => 'itemlogo'))?></a>
					</div>
					<?php endif; ?>
					
					<div class="item">
						<?php if(isset($isTranscript)): ?>
						<a href="<?php echo $ENSEMBL_BASE.'Transcript/Summary'.'?'. http_build_query(array('db' => 'core','t' => $ensemblIdTrimmed)); ?>" target="_blank"><?php echo $this->Html->image('EnsEMBL.png',array('alt' => 'EnsEMBL transcript','title' => 'Search this transcript on EnsEMBL','class' => 'itemlogo'))?></a>
						<?php else: ?>
						<a href="<?php echo $ENSEMBL_BASE.'Gene/Summary'.'?'. http_build_query(array('db' => 'core','gene' => $ensemblIdTrimmed)); ?>" target="_blank"><?php echo $this->Html->image('EnsEMBL.png',array('alt' => 'EnsEMBL gene','title' => 'Search this gene on EnsEMBL','class' => 'itemlogo'))?></a>
						<?php endif; ?>
					</div>
					<?php
						if(isset($isTranscript)) {
							$BDAP_mode = 'transcript';
						} else {
							$BDAP_mode = 'gene';
						}
						$bdap_query = http_build_query(
							array(
								'q' => $BDAP_mode.':'.$ensemblIdTrimmed,
								'selectedTab' => $ensemblIdTrimmed,
								'tabs' => array(
									array(
										'id' => $ensemblIdTrimmed,
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
						<a href="<?php echo 'http://blueprint-data.bsc.es/#!/'.'?'. $bdap_query; ?>" target="_blank"><?php echo $this->Html->image('BDAP-logo.png',array('alt' => 'BLUEPRINT Data Analysis Portal','title' => 'Search this '.$BDAP_mode.' on BLUEPRINT Data Analysis Portal','class' => 'itemlogo'))?></a>
					</div>
					
					<?php if(!isset($isTranscript)): ?>
					<div class="item">
						<a href="<?php echo 'http://www.gtexportal.org/home/gene/'.$names[$indexEns]; ?>" target="_blank"><?php echo $this->Html->image('GTEx_v2_logo_trans.png',array('alt' => 'GTEx gene page','title' => "Show the GTEx gene page",'class' => 'itemlogo'))?></a>
					</div>
					<div class="item">
						<a href="<?php echo 'http://www.gtexportal.org/home/browseEqtls'.'?'.http_build_query(array('location' => $names[$indexEns])); ?>" target="_blank"><?php echo $this->Html->image('GTEx_v2_logo_trans.png',array('alt' => 'GTEx gene eQTL visualizer','title' => "Show this gene on GTEx gene eQTL visualizer",'class' => 'itemlogo'))?></a>
					</div>
					<div class="item">
						<a href="<?php echo 'http://www.gtexportal.org/home/eqtls/byGene'.'?'.http_build_query(array('geneId' => $names[$indexEns], 'tissueName' => 'All')); ?>" target="_blank"><?php echo $this->Html->image('GTEx_v2_logo_trans.png',array('alt' => 'GTEx gene eQTL results','title' => "Show this gene on GTEx gene eQTL",'class' => 'itemlogo'))?></a>
					</div>
					<div class="item">
						<a href="<?php echo $UCSC_SERVER.'cgi-bin/hgGene'.'?'.http_build_query(array('org' => 'human', 'db' => $UCSC_genome_ver,'hgg_gene' => $names[$indexEns])); ?>" target="_blank"><?php echo $this->Html->image('UCSC-Genome-Browser-human.jpg',array('alt' => 'UCSC Genome Browser gene description','title' => "Show this gene's description from UCSC Genome Browser",'class' => 'itemlogo'))?></a>
					</div>
					<div class="item">
						<a href="<?php echo $UCSC_SERVER.'cgi-bin/hgTracks'.'?'.http_build_query(array('org' => 'human', 'db' => $UCSC_genome_ver,'singleSearch' => 'knownCanonical','position' => $names[$indexEns])); ?>" target="_blank"><?php echo $this->Html->image('UCSC-Genome-Browser-human.jpg',array('alt' => 'UCSC Genome Browser canonical transcript','title' => "Show this gene's canonical transcript on UCSC Genome Browser",'class' => 'itemlogo'))?></a>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
<?php
		endforeach;
	endif;
?>
