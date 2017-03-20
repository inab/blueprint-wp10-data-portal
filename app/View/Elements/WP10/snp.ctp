<?php
if(isset($h['snp_id'])) {
	if(isset($h['rsId'])) {
		$rsIds = &$h['rsId'];
	} else {
		$rsIds = array(&$h['snp_id']);
	}
	if(isset($h['dbSnpRef'])) {
		$dbSnpRefs = &$h['dbSnpRef'];
		$dbSnpAlts = &$h['dbSnpAlt'];
		$MAFs = &$h['MAF'];
	} else {
		$dbSnpRefs = array(null);
		$dbSnpAlts = array(null);
		$MAFs = array(null);
	}
	foreach($rsIds as $indexRsId => &$rsId):
		$dbSnpRef = &$dbSnpRefs[$indexRsId];
		$dbSnpAlt = &$dbSnpAlts[$indexRsId];
		$MAF = &$MAFs[$indexRsId];
?>
	<div>
<?php		
		echo $this->Html->tag(
			'span',
			$rsId,
			array(
				'data-position' => 'top center',
				'class' => 'plus-info noselect info circle icon'
			)
		);
?>
		<div class="ui popup">
			<div class="ui list left aligned">
<?php
	#if(substr($rsId,0,2) == 'rs') {
	#	$rsStr = $this->Html->link(
	#		$rsId,
	#		'http://www.ncbi.nlm.nih.gov/SNP/snp_ref.cgi'.'?'.http_build_query(
	#			array(
	#				'searchType' => 'adhoc_search',
	#				'type' => 'rs',
	#				'rs' => $rsId
	#			)
	#		),
	#		array(
	#			'target' => '_blank'
	#		)
	#	);
	#} else {
		$rsStr = $this->Html->tag('span',$rsId);
	#}

	echo $this->Html->div('item center aligned',$this->Html->div('ui blue horizontal large label','SNP Id: '.$rsStr));
	echo $this->Html->div('item nowrap',$this->Html->div('ui horizontal label','WP10 snp_id').$this->Html->tag('span',$h['snp_id']));
	echo $this->Html->div('item nowrap',$this->Html->div('ui horizontal label','WP10 Ref / Alt').$this->Html->tag('span',$h['snpRef'].' / '.$h['snpAlt']));
	echo $this->Html->div('item nowrap',$this->Html->div('ui horizontal label','WP10 Alt AF').$this->Html->tag('span',$h['altAF']));
	if($dbSnpRef !== null) {
		echo $this->Html->div('item',$this->Html->div('ui horizontal label','dbSNP Ref / Alt').$this->Html->tag('span',$dbSnpRef.' / '.$dbSnpAlt));
		echo $this->Html->div('item',$this->Html->div('ui horizontal label','dbSNP MAF<br><span style="font-size: 50%">(from dbSNP)</span>').$this->Html->tag('span',$MAF===null ? 'NA' : $MAF));
	}
?>
			</div>
			<div class="ui horizontal list">
				<div class="item">
					<a href="<?php echo 'http://www.ncbi.nlm.nih.gov/SNP/snp_ref.cgi'.'?'. http_build_query(array('searchType' => 'adhoc_search','type' => 'rs','rs' => $rsId)); ?>" target="_blank"><?php echo $this->Html->image('dbSNP-custom-logo.png',array('alt' => 'dbSNP','title' => 'Search this SNP on dbSNP','class' => 'itemlogo'))?></a>
				</div>
				<div class="item">
					<a href="<?php echo 'http://www.broadinstitute.org/mammals/haploreg/detail_v4.1.php'.'?'. http_build_query(array('query' => '','id' => $rsId)); ?>" target="_blank"><?php echo $this->Html->image('HaploReg-custom-logo.png',array('alt' => 'HaploReg v4.1','title' => 'Search this SNP on HaploReg v4.1','class' => 'itemlogo'))?></a>
				</div>
				<div class="item">
					<a href="<?php echo 'http://ensembl.org/Homo_sapiens/Variation/Explore'.'?'. http_build_query(array('v' => $rsId)); ?>" target="_blank"><?php echo $this->Html->image('EnsEMBL.png',array('alt' => 'EnsEMBL Variation','title' => 'Search this SNP on EnsEMBL Variation','class' => 'itemlogo'))?></a>
				</div>
				<div class="item">
					<a href="<?php echo 'http://www.gtexportal.org/home/eqtls/bySnp'.'?'.http_build_query(array('snpId' => $rsId, 'tissueName' => 'All')); ?>" target="_blank"><?php echo $this->Html->image('GTEx_v2_logo_trans.png',array('alt' => 'GTEx SNP eQTL results','title' => "Show this SNP on GTEx SNP eQTL",'class' => 'itemlogo'))?></a>
				</div>
			</div>
		</div>
	</div>
<?php
	endforeach;
}
?>
