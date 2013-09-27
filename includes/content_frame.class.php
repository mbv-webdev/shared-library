<?php namespace mbv;

/**
 * MBV Core Library - ContentFrame Class
 *
 * @package MBV Core Library
 * @subpackage ContentFrame
 * @author Oliver Gärtner <og@mbv-media.com>
 * @license GPL-3.0+
 * @link http://www.mbv-media.com/
 * @copyright 2013 MBV Media
 */

/**
 * Handles the menu part of the sub-content frame menu
 * 
 * @package MBV Core Library
 * @author Oliver Gärtner <og@mbv-media.com>
 */
class ContentFrame {
	/**
	 * Name of the page
	 * 
	 * @since 0.2.0
	 * 
	 * @var string 
	 */
	private $page = '';

	/**
	 * Name of the subpage
	 * 
	 * @since 0.2.0
	 * 
	 * @var string 
	 */
	private $subpage = '';

	/**
	 * Pointer to MBV Core instance
	 * 
	 * @since 0.2.0
	 * 
	 * @var MBVCore 
	 */
	private $core = null;

	/**
	 * Get instance of MBV Core when new Instance is created
	 * 
	 * @since 0.2.0
	 */
	public function __construct() {
		$this->core = \MBVCore::get_instance();
		$this->get_page();
	}

	/**
	 * Return the submenu for the current plugin
	 * 
	 * @since 0.2.0
	 * 
	 * @global array $submenu
	 * @return array
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
	 * Get subpage name
	 * 
	 * @since 0.2.0
	 * 
	 * @return string
	 */
	public function get_subpage() {
		if (empty($this->subpage) && !empty($_GET['subpage'])) {
			$this->subpage = sanitize_key($_GET['subpage']);
		}

		return $this->subpage;
	}

	/**
	 * Get menu link to actual plugin page
	 * 
	 * @since 0.2.0
	 * 
	 * @global array $submenu
	 * @return string
	 */
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
	 * Get page name
	 * 
	 * @since 0.2.0
	 */
	private function get_page() {
		if (empty($this->page) && !empty($_GET['page'])) {
			$this->page = sanitize_key($_GET['page']);
		}
	}
}
?>