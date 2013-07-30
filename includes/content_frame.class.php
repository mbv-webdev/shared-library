<?php namespace mbv;

	/**
	 * 
	 */
	class ContentFrame {
		/**
		 *
		 * @var string 
		 */
		private $page = '';

		/**
		 *
		 * @var string 
		 */
		private $subpage = '';

		/**
		 *
		 * @var MBVCore 
		 */
		private $core = null;

		/**
		 * 
		 */
		public function __construct() {
			$this->core = \MBVCore::get_instance();
			$this->get_page();
		}

		/**
		 * 
		 * @global array $submenu
		 * @return type
		 */
		public function get_submenu_data() {
			global $submenu;

			$submenu_data = array();

			if (isset($submenu[$this->page])) {
				$submenu_data = $submenu[$this->page];

				foreach ($submenu_data as &$single_data) {
					$single_data = array_flip($single_data);

					$index = 0;
					$keys  = array(
						'menu_name',
						'capability',
						'slug',
						'page_title'
					);

					foreach ($single_data as &$key_to_change) {
						$key_to_change = $keys[$index];

						++$index;
					}

					$single_data = array_flip($single_data);
					$single_data['link'] = '<a href="?page='.$this->page.'&subpage='.$single_data['slug'].'">'
												.$single_data['menu_name'].
											'</a>';
				}
			}

			return $submenu_data;
		}

		/**
		 * 
		 * @return type
		 */
		public function get_subpage() {
			if (empty($this->subpage) && !empty($_GET['subpage'])) {
				$this->subpage = sanitize_key($_GET['subpage']);
			}

			return $this->subpage;
		}

		
		public function get_mainmenu_link() {
			global $submenu;

			$menu_name = '';
			$menu_slug = '';

			$mbv_menu = $submenu[$this->core->get_plugin_slug()];
			foreach ($mbv_menu as $check_this) {
				if (in_array($this->page, $check_this)) {
					list($menu_name, , $menu_slug, ) = $check_this;
					break;
				}
			}

			return '<a href="?page='.$menu_slug.'">'
						.$menu_name.
					'</a>';
		}

		/**
		 * 
		 */
		private function get_page() {
			if (empty($this->page) && !empty($_GET['page'])) {
				$this->page = sanitize_key($_GET['page']);
			}
		}
	}
?>