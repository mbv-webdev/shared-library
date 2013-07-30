<?php
	$content_frame = new \mbv\ContentFrame();
	$submenu_data = $content_frame->get_submenu_data();
?>
<nav class="subcontent-menu">
	<ul>
		<li<?=($content_frame->get_subpage() == '' ? ' class="active"' : '')?>><?=$content_frame->get_mainmenu_link()?></li>
		<?php foreach ($submenu_data as $submenu) { ?>
			<li<?=($content_frame->get_subpage() == $submenu['slug'] ? ' class="active"' : '')?>><?=$submenu['link']?></li>
		<?php } ?>
	</ul>
</nav>
<div class="fullwidth-subcontent">
