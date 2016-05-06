<?php
$ANALYSIS_NAMES = array(
	'gene' => 'gene expression',
	'exon' => 'exon',
	'cufflinks' => 'Cufflinks',
	'meth' => 'methylation arrays',
	'sj' => 'splice junction',
	'psi' => 'percent splice-in',
	'K27AC' => 'H3K27AC ChIP-Seq peaks',
	'K4ME1'=> 'H3K4ME1 ChIP-Seq peaks',
	'sqtls' => 'sQTLseekeR'
);

echo $this->Html->tag('span',isset($doLarge) ? $ANALYSIS_NAMES[$h['qtl_source']] : $h['qtl_source']);
?>
