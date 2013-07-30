<?php
/**
* MBV Core Library
*
* @package shared-library
* @author Oliver Gärtner <og@mbv-media.com>
* @license GPL-3.0+
* @link http://www.mbv-media.com/
* @copyright 2013 MBV Media
*
* @wordpress-plugin
* Plugin Name: MBV Core Library
* Plugin URI: http://www.mbv-media.com/WP-Plugins/
* Description: 
* Version: 0.4.0
* Author: MBV Media | Oliver Gärtner
* Author URI: http://www.mbv-media.com/
* License: GPL-3.0+
* License URI: http://www.gnu.org/licenses/gpl-3.0.txt
* Text Domain: mbv-core
* Domain Path: /languages
*/

/**
* 
*
* @package shared-library
* @author Oliver Gärtner <og@mbv-media.com>
*/
class MBVCore {
	/**
	* Plugin version, used for cache-busting of style and script file references.
	*
	* @since 0.1.0
	*
	* @var string
	*/
	protected $version = '0.1.0';

	/**
	* Unique identifier for your plugin.
	*
	* Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	* match the Text Domain file header in the main plugin file.
	*
	* @since 0.1.0
	*
	* @var string
	*/
	protected $plugin_slug = 'mbv-core';

	/**
	* Instance of this class.
	*
	* @since 0.1.0
	*
	* @var object
	*/
	protected static $instance = null;

	/**
	* Slug of the plugin screen.
	*
	* @since 0.1.0
	*
	* @var string
	*/
	protected $plugin_screen_hook_suffix = null;

	/**
	* Slug of the plugin screen.
	*
	* @since 0.2.0
	*
	* @var string
	*/
	protected $registered_plugins = array();

	/**
	* Initialize the plugin by setting localization, filters, and administration functions.
	*
	* @since 0.1.0
	*/
	private function __construct() {
		define('MBV_CORE_DIR', dirname(__FILE__));
		define('MBV_CORE_URL', str_replace(WPMU_PLUGIN_DIR, WPMU_PLUGIN_URL, MBV_CORE_DIR));

		// Load plugin text domain
		add_action('init', array(&$this, 'load_core_textdomain'));

		// Add the options page and menu item.
		add_action('admin_menu', array(&$this, 'add_core_admin_menu'));
		add_action('admin_menu', array(&$this, 'add_core_settings_menu'), 999);

		// Load admin style sheet and JavaScript.
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_scripts'));
	}

	/**
	* Return an instance of this class.
	*
	* @since 0.1.0
	*
	* @return MBVCore A single instance of this class.
	*/
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if (null == self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	* Load the plugin text domain for translation.
	*
	* @since 0.1.0
	*/
	public function load_core_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo');
		load_muplugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)).'/languages/');
	}

	/**
	* Register and enqueue admin-specific style sheet.
	*
	* @since 0.1.0
	*
	* @return null Return early if no settings page is registered.
	*/
	public function enqueue_admin_styles() {
		/*if (!isset($this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($screen->id == $this->plugin_screen_hook_suffix) {*/
			wp_enqueue_style($this->plugin_slug.'-admin-styles', plugins_url('css/admin.css', __FILE__), array(), $this->version);
		//}
	}

	/**
	* Register and enqueue admin-specific JavaScript.
	*
	* @since 0.1.0
	*
	* @return null Return early if no settings page is registered.
	*/
	public function enqueue_admin_scripts() {
		if (!isset($this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($screen->id == $this->plugin_screen_hook_suffix) {
			#wp_enqueue_script($this->plugin_slug.'-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'), $this->version);
		}
	}

	/**
	* Register the administration menu for this plugin into the WordPress Dashboard menu.
	*
	* @since 0.1.0
	*/
	public function add_core_admin_menu() {
		$this->plugin_screen_hook_suffix = add_menu_page(
			__('MBV Plugin Collection', $this->plugin_slug),
			__('MBV', $this->plugin_slug),
			'read',
			$this->plugin_slug,
			array(&$this, 'display_core_admin_page'),
			MBV_CORE_URL.'/images/mbv-icon.png'
		);

		add_submenu_page(
			$this->plugin_slug,
			__('MBV Plugin Collection', $this->plugin_slug),
			__('MBV Plugins', $this->plugin_slug),
			'read',
			$this->plugin_slug
		);
	}
	
	/**
	* Register the administration menu for this plugin into the WordPress Dashboard menu.
	*
	* @since 0.1.0
	*/
	public function add_core_settings_menu() {
		add_submenu_page(
			$this->plugin_slug,
			__('Settings', $this->plugin_slug),
			__('Settings', $this->plugin_slug),
			'read',
			$this->plugin_slug.'-settings',
			array(&$this, 'display_core_admin_page')
		);
	}

	/**
	* Render the admin pages for this plugin.
	*
	* @since 0.1.0
	*/
	public function display_core_admin_page() {
  		switch (sanitize_file_name($_GET['page'])) {
			case 'mbv-core-settings':
				// Load settings view
				include_once('views/settings.php');
				break;

			default:
				// Load admin overview dependency class
				include_once('includes/overview.class.php');
				// Load admin view
				include_once('views/overview.php');
		}
	}

	/**
	* Render the settings page for this plugin.
	*
	* @since 0.1
	*/
	public function display_plugin_admin_page($plugin_path) {
		$parent_slug = '';
		// Get original page slug
		if (isset($_GET['page'])) {
			$parent_slug = sanitize_key($_GET['page']);
		}

		// Get slug for subpage
		$menu_slug = '';
		if (isset($_GET['subpage'])) {
			$menu_slug = sanitize_key($_GET['subpage']);
		}

		// Place HTML for sub-content capability around the plugin's HTML
		$this->include_pre_content();

		if (empty($menu_slug)) {
			if (file_exists($plugin_path)) {
				include_once($plugin_path);
			}
			else {
				throw new Exception(__('This plugin view does not exist.', $this->plugin_slug), 1001);
			}
		}
		else {
			// Load callbacks set using add_submenu_page
			$hookname = get_plugin_page_hookname($menu_slug, $parent_slug);
			do_action($hookname);
		}

		$this->include_post_content();
	}

	/**
	 * Load global, backend or frontend dependencies
	 *
	 * @since 0.1.0
	 * 
	 * @param type $which
	 */
	public function load_dependencies($which = 'page') {
		switch ($which) {
			case 'admin':
				require_once(MBV_CORE_DIR.'/includes/language.class.php');
				require_once(MBV_CORE_DIR.'/includes/adminpage/adminpage.class.php');
				break;

			default:
				// For pages
		}
	}

	/**
	 * Register a plugin on the core library, necessary to determine if there are any dependencies
	 *
	 * @since 0.1.0
	 * 
	 * @param type $plugin_slug
	 * @param type $plugin_path
	 */
	public function register_plugin($plugin_slug, $plugin_path) {
		if (file_exists($plugin_path) && is_file($plugin_path)) {
			$this->registered_plugins[$plugin_slug] = $plugin_path;
		}
	}

	/**
	 * Get array of registered plugins. Necessary for deletion when there are no more dependencies
	 * for the MBV Core Library
	 *
	 * @since 0.4.0
	 * 
	 * @return array
	 */
	public function get_registered_plugins() {
		return $this->registered_plugins;
	}

	/**
	 * Get the slug of the core plugin
	 *
	 * @since 0.4.0
	 * 
	 * @return string
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Include files from the core library
	 *
	 * @since 0.4.0
	 * 
	 * @param string $file_name
	 */
	public function include_file($file_name) {
		if (file_exists(MBV_CORE_DIR.'/includes/'.$file_name.'.class.php')) {
			// Load include
			include_once(MBV_CORE_DIR.'/includes/'.$file_name.'.class.php');
		}

		if (file_exists(MBV_CORE_DIR.'/views/'.$file_name.'.php')) {
			// Load view
			include_once(MBV_CORE_DIR.'/views/'.$file_name.'.php');
		}
	}

	/**
	 * Include pre-content HTML to build the sub-menu and container for each plugin's content
	 *
	 * @since 0.4.0
	 */
	public function include_pre_content() {
		$this->include_file('content_frame');
	}

	/**
	 * Include post-content HTML, e.g. to close tags after the plugin's content
	 *
	 * @since 0.4.0
	 */
	public function include_post_content() {
		$this->include_file('content_footer');
	}

	/**
	 * Support function to make calling parseAllLocales in \mbv\Language easier.
	 *
	 * @since 0.4.0
	 * 
	 * @return array Array of translated strings
	 */
	public function _m() {
		return call_user_func_array(array('\mbv\Language', 'parseAllLocales'), func_get_args());
	}
}

MBVCore::get_instance();
?>