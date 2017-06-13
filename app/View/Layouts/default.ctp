<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = _('BLUEPRINT WP10 data portal');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $this->fetch('title'); ?>
	</title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->script('/libs/jquery/jquery.min')."\n";
		echo $this->Html->script('/libs/Semantic-UI/dist/semantic.min')."\n";
		echo $this->Html->css('/libs/Semantic-UI/dist/semantic.min')."\n";
		echo $this->Html->css('blueprint-wp10')."\n";
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body style="overflow-x: auto;">
	<div class="ui primary inverted menu">
	  <div class="header item"><a class="blueprint-link" href="http://www.blueprint-epigenome.eu/" target="_blank"><?php echo $this->Html->image('logoblueprint.png',array('alt' => 'BLUEPRINT','class' => 'blueprint-logo'))?></a> WP10 Data Portal</div>
	  <a class="item" href="<?php echo $this->Html->Url(array("controller"=>"qtls","action"=>"index"))?>">QTLs Search</a>
	  <a class="item" href="<?php echo $this->Html->Url(array("controller"=>"hypervariability","action"=>"index"))?>">HVar Search</a>
	  <div class="right menu">
	    <div class="header item">
	      Support
	    </div>
	    <a class="item" style="visibility:hidden;" href="<?php echo $this->Html->Url(array("controller"=>"pages","action"=>"faqs"))?>" target="_blank">
	      FAQs
	    </a>
	    <a class="item" href="<?php echo $this->Html->Url(array("controller"=>"pages","action"=>"related"))?>" target="_blank">
	      Related data
	    </a>
	    <a class="item" href="mailto:bp-wp10-portal@lists.cnio.es?subject=BLUEPRINT WP10 Data Portal Support">
	      E-mail Support
	    </a>
	  </div>
	</div>
	<div class="ui page grid">
	<?php echo $this->Flash->render(); ?>
	<?php echo $this->fetch('content'); ?>
	</div>
	<div class="ui center aligned container footer">
		<div class="ui three column grid">
			<div class="column centered">
				<a href="http://ihec-epigenomes.org/"><?php echo $this->Html->image('ihec_logo.png',array('alt' => 'IHEC','class' => 'logo'))?></a>
			</div>
			<div class="column centered">
				<a href="http://www.blueprint-epigenome.eu/"><?php echo $this->Html->image('logoblueprint.png',array('alt' => 'BLUEPRINT','class' => 'logo'))?></a>
			</div>
			<div class="column centered">
				<a href="http://cordis.europa.eu/project/rcn/99677_en.html" target="_blank"><?php echo $this->Html->image('5000200-commission-cl.jpg',array('class' => 'logo'))?></a>
			</div>
		</div>
		<div class="ui text container">This project has received funding from the European Union’s 7<sup>th</sup> Framework Programme for research, technological development and demonstration under <a href="http://cordis.europa.eu/project/rcn/99677_en.html" target="_blank">grant agreement no 282510</a></div>
		<div class="ui two column grid">
			<div class="column">
				<p>This site has been developed and is hosted at <a href="http://www.cnio.es/">CNIO</a></p>
			</div>
			<div class="column">
				<p>BLUEPRINT Epigenome WP10 &copy; 2015-2016</p>
			</div>
		</div>
	</div>
</body>
<?php
echo $this->Html->scriptBlock("
	$(document).ready(function(){
		$('select.dropdown')
		  .dropdown()
		;
		$('.ui.checkbox')
		  .checkbox()
		;
	})
");
?>
</html>
