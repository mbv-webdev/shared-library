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
	 *
	 * @var type 
	 */
	protected $terms = array();

	/**
	 *
	 * @var type 
	 */
	public $language = null;

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

		// Load TinyMCE
		add_action("admin_head", array(&$this, 'load_tiny_mce'));

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
		wp_enqueue_style('thickbox');
		wp_enqueue_style($this->plugin_slug.'-admin-styles', plugins_url('css/admin.css', __FILE__), array(), $this->version);
	}

	/**
	* Register and enqueue admin-specific JavaScript.
	*
	* @since 0.1.0
	*
	* @return null Return early if no settings page is registered.
	*/
	public function enqueue_admin_scripts() {
		wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
		#wp_enqueue_script($this->plugin_slug.'-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'), $this->version);
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

				$this->language = \mbv\Language::get_instance();
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
	public function get_flag($file) {
		return \mbv\Language::get_flags_path().'/'.$file;
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
	 * 
	 */
	public function load_tiny_mce() {
		// true would give you a stripped down version of the editor
		wp_tiny_mce(false);
	}

	public function get_term($option, $term, $default = '') {
		if (empty($this->terms[$option])) {
			$this->terms[$option] = get_option($option);
		}

		$language = get_locale();
		$term_value = $default;

		if (isset($this->terms[$option][$term])) {
			$term_value = $this->terms[$option][$term];

			if (is_array($term_value)) {
				if (isset($term_value[$language])) {
					$term_value = $term_value[$language];
				}
				else {
					$term_value = array_shift($term_value);
				}
			}
		}

		return $term_value;
	}

	/**
	 * 
	 */
	public function display_success_notice($option = '') {
		$message = __('Settings saved.');
		if (!empty($_REQUEST['term'])) {
			$message = $this->get_term($option, urldecode(esc_attr($_REQUEST['term'])), $message);
		}

		if ($message == __('Settings saved.') && !empty($_REQUEST['term'])) {
			$message = __(urldecode(esc_attr($_REQUEST['term'])), $option);

			if ($message === esc_attr($_REQUEST['term'])) {
				$message = ucwords(str_replace('_', ' ', $message));
			}
		}

		if (strpos($message, '%')) {
			$message = $this->sprintfn($message, $_REQUEST);
		}

		echo '
			<div class="updated">
				<p>'.$message.'</p>
			</div>';
	}

	/**
	 * 
	 */
	public function display_fail_notice($option = '') {
		$message = __('Error while saving the changes.');
		if (!empty($_REQUEST['term'])) {
			$message = $this->get_term($option, urldecode(esc_attr($_REQUEST['term'])), $message);
		}

		if ($message == __('Error while saving the changes.') && !empty($_REQUEST['term'])) {
			$message = __(urldecode(esc_attr($_REQUEST['term'])), $option);

			if ($message === esc_attr($_REQUEST['term'])) {
				$message = ucwords(str_replace('_', ' ', $message));
			}
		}

		if (strpos($message, '%')) {
			$message = $this->sprintfn($message, $_REQUEST);
		}

		echo '
			<div class="updated">
				<p>'.$message.'</p>
			</div>';
	}

	/**
	 * version of sprintf for cases where named arguments are desired
	 *
	 * with sprintf: sprintf('second: %2$s ; first: %1$s', '1st', '2nd');
	 *
	 * with sprintfn: sprintfn('second: %second$s ; first: %first$s', array(
	 *  'first' => '1st',
	 *  'second'=> '2nd'
	 * ));
	 *
	 * @param string $format sprintf format string, with any number of named arguments
	 * @param array $args array of [ 'arg_name' => 'arg value', ... ] replacements to be made
	 * @return string|false result of sprintf call, or bool false on error
	 */
	public function sprintfn ($format, array $args = array()) {
		// map of argument names to their corresponding sprintf numeric argument value
		$arg_nums = array_slice(array_flip(array_keys(array(0 => 0) + $args)), 1);

		// find the next named argument. each search starts at the end of the previous replacement.
		for ($pos = 0; preg_match('/(?<=%)([a-zA-Z_]\w*)(?=\$)/', $format, $match, PREG_OFFSET_CAPTURE, $pos);) {
			$arg_pos = $match[0][1];
			$arg_len = strlen($match[0][0]);
			$arg_key = $match[1][0];

			// programmer did not supply a value for the named argument found in the format string
			if (! array_key_exists($arg_key, $arg_nums)) {
				trigger_error("sprintfn(): Missing argument '${arg_key}'");
				#throw new Exception("sprintfn(): Missing argument '${arg_key}'");
				return false;
			}

			// replace the named argument with the corresponding numeric one
			$format = substr_replace($format, $replace = $arg_nums[$arg_key], $arg_pos, $arg_len);
			$pos = $arg_pos + strlen($replace); // skip to end of replacement for next iteration
		}

		$parsed = vsprintf($format, array_values($args));

		return $parsed;
	}

	/**
	 * Support function to make calling parseAllLocales in \mbv\Language easier.
	 *
	 * @since 0.4.0
	 * 
	 * @return array Array of translated strings
	 */
	public function __() {
		return call_user_func_array(array($this->language, 'parse_all_locales'), func_get_args());
	}


	/**
	 * Display the pagination.
	 *
	 * @since 1.8.0
     * @author taken from WP core (see includes/class-wp-list-table.php)
	 * @return string echo the html pagination bar
	 */
	public function pagination( $which, $current, $total_items, $per_page ) {
        $total_pages = ($per_page > 0) ? ceil( $total_items / $per_page ) : 1;

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='%s' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				esc_attr( 'post_paged' ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$output .= "\n<span class='pagination-links'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages )
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		$pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $pagination;
	}
}

MBVCore::get_instance();
?>