<?php
if(isset($h['probeId'])) {
	echo $this->Html->link(
		$h['probeId'],
		'http://genome-euro.ucsc.edu/cgi-bin/hgTracks'.'?'.http_build_query(array('clade' => 'mammal','org' => 'Human','db' => 'hg19','position' => $h['probeId'])),
		array(
			'target' => '_blank'
		)
	);
}
?>
