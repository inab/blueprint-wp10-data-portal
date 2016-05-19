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

$ANALYSIS_NAMES_HVAR = array(
	'gene' => 'RNA-seq data',
	'meth' => 'methylation arrays'
);

echo $this->Html->tag('span',isset($doLarge) ? ( isset($h['hvar_id']) ? $ANALYSIS_NAMES_HVAR[$h['qtl_source']] : $ANALYSIS_NAMES[$h['qtl_source']]) : $h['qtl_source']);
?>
