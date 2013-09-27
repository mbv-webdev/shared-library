<?php namespace mbv;

	/**
	 * 
	 */
	class AdminOverview {
		/**
		 * 
		 * 
		 * @var type 
		 */
		private $core = null;

		/**
		 * 
		 */
		public function __construct() {
			$this->core = \MBVCore::get_instance();

			$this->load_meta_box_content();
		}

		/**
		 * 
		 */
		public function get_plugin_list() {
			$plugin_data = $this->parse_plugin_list_data();

			foreach ($plugin_data as $plugin) {
				echo '
					<div class="installed-plugin">
						<div class="plugin-info-left">
							<strong>'.$plugin['Name'].'</strong>
							<div class="version">'.__('Version').': '.$plugin['Version'].'</div>
						</div>
						<div class="plugin-info-right">
							'.$plugin['Description'].'
						</div>
						<div class="clear"></div>
					</div>';
			}
		}

		/**
		 * 
		 * @param type $plugins
		 * @return type
		 */
		public function check_versions($plugins) {
			return print_r($plugins, true);
		}

		/**
		 * 
		 * @return boolean
		 */
		private function parse_plugin_list_data() {
			$plugin_data = array();
			$registered_plugins = $this->core->get_registered_plugins();

			if (empty($registered_plugins)) {
				return false;
			}

			foreach ($registered_plugins as $plugin_slug => $plugin_path) {
				$plugin_data[$plugin_slug] = array(
					'path' => $plugin_path,
					'slug' => $plugin_slug
				);

				$plugin_info = get_plugin_data($plugin_path);
				$plugin_data[$plugin_slug] = array_merge($plugin_data[$plugin_slug], $plugin_info);
			}

			return $plugin_data;
		}

		/**
		 * 
		 */
		private function load_meta_box_content() {
			add_meta_box('plugin-list', __('List of installed MBV Plugins', 'mbv-core'), array(&$this, 'get_plugin_list'), 'mbv_overview', 'left', 'core');
			add_meta_box('plugin-updates', __('Available Updates', 'mbv-core'), array(&$this, 'check_versions'), 'mbv_overview', 'right', 'core', $this->parse_plugin_list_data());
		}
	}
?>