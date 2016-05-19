<?php
if(isset($h['probeId'])) {
	echo $this->Html->link(
		$h['probeId'],
		$UCSC_SERVER.'cgi-bin/hgTracks'.'?'.http_build_query(array('clade' => 'mammal','org' => 'Human','db' => $UCSC_genome_ver,'position' => $h['probeId'])),
		array(
			'target' => '_blank'
		)
	);
}
?>
