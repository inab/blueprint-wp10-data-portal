<?php
if(isset($h['hvar_id']) && !isset($useGeneCoords)) {
	if(isset($h['pos'])) {
		$gene_start = $h['pos'];
		$gene_end = $h['pos'];
	} elseif(isset($h['gene_start'])) {
		$gene_start = $h['gene_start'];
		$gene_end = $h['gene_end'];
	}
} elseif(isset($h['gene_start'])) {
	$gene_start = $h['gene_start'];
	$gene_end = $h['gene_end'];
} elseif(isset($h['pos'])) {
	$gene_start = $h['pos'];
	$gene_end = $h['pos'];
}

if(isset($gene_start)) {
	$coordinates = $h['gene_chrom'].':'.$gene_start.'-'.$gene_end;
	$coordinates_str = $h['gene_chrom'].':'.number_format($gene_start).'-'.number_format($gene_end);
	echo $this->Html->link(
		$coordinates_str,
		$ENSEMBL_BASE.'Location/View'.'?'.http_build_query(array('r' => $coordinates)),
		array(
			'target' => '_blank'
		)
	);
} else {
	echo $this->Html->tag('span',$h['gene_chrom']);
}
?>
