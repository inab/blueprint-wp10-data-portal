<?php
$CELLTYPE_NAMES = array(
	'neut'	=> 'neutrophils',
	'tcel'	=> 'T cells',
	'mono'	=> 'monocytes'
);
//echo $this->Html->tag('span',$CELLTYPE_NAMES[$h['cell_type']]);
if(is_array($h['cell_type'])) {
	$cell_types = &$h['cell_type'];
} else {
	$cell_types = array(&$h['cell_type']);
}
foreach($cell_types as $icell => &$cell_type) {
	if($icell > 0) {
		echo $this->Html->tag('br');
	}
	echo $this->Html->tag('span',isset($doLarge) ? $CELLTYPE_NAMES[$cell_type] : $cell_type);
}
?>
