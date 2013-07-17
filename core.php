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
* Version: 0.1.0
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
	* Initialize the plugin by setting localization, filters, and administration functions.
	*
	* @since 0.1.0
	*/
	private function __construct() {
		define('MBV_CORE_DIR', dirname(__FILE__));
		define('MBV_CORE_URL', str_replace(WPMU_PLUGIN_DIR, WPMU_PLUGIN_URL, MBV_CORE_DIR));

		// Load plugin text domain
		add_action('init', array(&$this, 'load_plugin_textdomain'));

		// Add the options page and menu item.
		add_action('admin_menu', array(&$this, 'add_plugin_admin_menu'));

		// Load admin style sheet and JavaScript.
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_styles'));
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_scripts'));
	}

	/**
	* Return an instance of this class.
	*
	* @since 0.1.0
	*
	* @return object A single instance of this class.
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
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo');
		load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)).'/lang/');
	}

	/**
	* Register and enqueue admin-specific style sheet.
	*
	* @since 0.1.0
	*
	* @return null Return early if no settings page is registered.
	*/
	public function enqueue_admin_styles() {
		if (!isset($this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($screen->id == $this->plugin_screen_hook_suffix) {
			#wp_enqueue_style($this->plugin_slug.'-admin-styles', plugins_url('css/admin.css', __FILE__), array(), $this->version);
		}
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
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_menu_page(
			__('MBV Plugin Collection', $this->plugin_slug),
			__('MBV', $this->plugin_slug),
			'read',
			$this->plugin_slug,
			array(&$this, 'display_plugin_admin_page'),
			MBV_CORE_URL.'/images/mbv-icon.png'
		);

		add_submenu_page(
			$this->plugin_slug,
			__('Settings', $this->plugin_slug),
			__('Settings', $this->plugin_slug),
			'read',
			$this->plugin_slug.'-settings',
			array(&$this, 'display_plugin_settings_page')
		);
	}

	/**
	* Render the overview page for this plugin.
	*
	* @since 0.1.0
	*/
	public function display_plugin_admin_page() {
		#include_once('views/admin.php');
	}
	
	/**
	* Render the settings page for this plugin.
	*
	* @since 0.1.0
	*/
	public function display_plugin_settings_page() {
		#include_once('views/settings.php');
	}
}

$mbv_core = MBVCore::get_instance();
define('MBV_CORE', $mbv_core);
?>