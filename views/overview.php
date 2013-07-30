<?php
	$admin_core = new \mbv\AdminOverview();
?>
<div class="wrap mbv-core-wrap">
	<?php screen_icon('mbv-overview'); ?>
	<h2><?php _e('MBV Plugins Overview', 'mbv-core') ?></h2>
	<div id="dashboard-widgets-container" class="mbv-overview">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="post-body">
				<div id="dashboard-widgets-main-content">
					<div class="postbox-container" id="main-container" style="width:75%;">
						<?php do_meta_boxes('mbv_overview', 'left', ''); ?>
					</div>
					<div class="postbox-container" id="side-container" style="width:24%;">
						<?php do_meta_boxes('mbv_overview', 'right', ''); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
