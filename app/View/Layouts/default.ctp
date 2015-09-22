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
		echo $this->Html->script('//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js')."\n";
		echo $this->Html->script('../Semantic-UI/dist/semantic.min')."\n";
		echo $this->Html->css('../Semantic-UI/dist/semantic')."\n";
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div class="ui primary inverted menu">
	  <div class="header item">BLUEPRINT WP10</div>
	  <a class="item" href="<?php echo $this->Html->Url(array("controller"=>"qtls","action"=>"index"))?>">Search</a>
	  <div class="right menu">
	    <div class="header item">
	      Support
	    </div>
	    <a class="item">
	      FAQ
	    </a>
	    <a class="item">
	      E-mail Support
	    </a>
	  </div>
	</div>
	<div class="ui page grid">
	<?php echo $this->Session->flash(); ?>
	<?php echo $this->fetch('content'); ?>
	</div>
</body>
<script>
	$(document).ready(function(){
		$('select.dropdown')
		  .dropdown()
		;
		$('.ui.checkbox')
		  .checkbox()
		;
	})
</script>
</html>
