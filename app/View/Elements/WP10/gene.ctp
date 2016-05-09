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

$UCSC_SERVER = 'https://genome-euro.ucsc.edu';
$UCSC_genome = 'hg19';
?>
<?php
	if(isset($h['gene_name']) || isset($h['ensemblGeneId'])):
		if(isset($h['gene_name'])) {
			$showGeneName = true;
			$gene_names = is_array($h['gene_name']) ? $h['gene_name'] : array($h['gene_name']);
			if(isset($h['ensemblGeneId'])) {
				$ensemblGeneIds = is_array($h['ensemblGeneId']) ? $h['ensemblGeneId'] : array($h['ensemblGeneId']);
				$showEnsemblId = true;
			} else {
				$ensemblGeneIds = &$gene_names;
			}
		} else {
			$ensemblGeneIds = is_array($h['ensemblGeneId']) ? $h['ensemblGeneId'] : array($h['ensemblGeneId']);
			$gene_names = &$ensemblGeneIds;
			$showEnsemblId = true;
		}
			
		foreach($ensemblGeneIds as $indexEns => &$ensemblGeneId):
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
		?>
		<div>
		<?php
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
					echo $this->Html->div('item center aligned',$this->Html->div('ui blue horizontal large label','Gene: '.(isset($showGeneName) ? $gene_names[$indexEns] : '(not recorded)')));
					echo $this->Html->div('item nowrap',$this->Html->div('ui horizontal label','Ensembl Gene Id').$this->Html->tag('span',isset($showEnsemblId) ? $ensemblGeneId : '(not recorded)'));
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
					<div class="item">
						<a href="<?php echo $UCSC_SERVER.'/cgi-bin/hgGene'.'?'.http_build_query(array('org' => 'human', 'db' => $UCSC_genome,'hgg_gene' => $gene_names[$indexEns])); ?>" target="_blank"><?php echo $this->Html->image('UCSC-Genome-Browser-human.jpg',array('alt' => 'UCSC Genome Browser gene description','title' => "Show this gene's description from UCSC Genome Browser",'class' => 'itemlogo'))?></a>
					</div>
					<div class="item">
						<a href="<?php echo $UCSC_SERVER.'/cgi-bin/hgTracks'.'?'.http_build_query(array('org' => 'human', 'db' => 'hg19','singleSearch' => 'knownCanonical','position' => $gene_names[$indexEns])); ?>" target="_blank"><?php echo $this->Html->image('UCSC-Genome-Browser-human.jpg',array('alt' => 'UCSC Genome Browser canonical transcript','title' => "Show this gene's canonical transcript on UCSC Genome Browser",'class' => 'itemlogo'))?></a>
					</div>
				</div>
			</div>
		</div>
<?php
		endforeach;
	endif;
?>
