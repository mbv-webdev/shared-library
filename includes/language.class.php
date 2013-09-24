<?php namespace mbv;

	/**
	* MBV Core Library - Language Class
	*
	* @package MBV Core Library
	* @author Oliver Gärtner <og@mbv-media.com>
	* @license GPL-3.0+
	* @link http://www.mbv-media.com/
	* @copyright 2013 MBV Media
	*/

	/**
	 * Class for language and localization functions
	 * 
	 * @package MBV Core Library
	 * @author Oliver Gärtner <og@mbv-media.com>
	 */
	class Language {
		/**
		* Instance of this class.
		*
		* @since 0.1.0
		*
		* @var object
		*/
		protected static $instance = null;

		/**
		* 
		*
		* @since 0.2.0
		*
		* @var string
		*/
		protected $flags_path = '';

		/**
		* 
		*
		* @since 0.2.0
		*
		* @var string
		*/
		protected $language_base = '';

		/**
		* 
		*
		* @since 0.2.0
		*
		* @var string
		*/
		protected $language_path = '/languages';

		/**
		 *
		 * @var type 
		 */
		protected $terms = array();

		/**
		* 
		*
		* @since 0.2.0
		*
		* @var string
		*/
		protected $locale = WPLANG;

		/**
		 * 
		 */
		private function __construct() {
			add_filter('locale', array(&$this, 'set_new_locale'));
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
		 * 
		 * @global object $polylang
		 * @return \stdClass
		 */
		public function get_language_list() {
			global $polylang;

			if ($polylang !== null) {
				// Get languages from polylang
				$language_list = $polylang->get_languages_list();
			}
			else {
				// Or set the Wordpress locale as only language
				$language_list = array(
					0 => new \stdClass()
				);

				$language_list[0]->description = WPLANG;
			}

			return $language_list;
		}

		/**
		 * 
		 * @return type
		 */
		public function get_current_language() {
			if (!empty($_POST['language'])) {
				$language = esc_attr($_POST['language']);
			}
			elseif (function_exists('pll_current_language')) {
				$language = pll_current_language('locale');
			}
			else {
				$language = get_locale();
			}

			return $language;
		}

		/**
		 * 
		 * @return type
		 */
		public function get_flags_path() {
			if (empty($this->flags_path)) {
				switch (1) {
					case function_exists('pll_current_language'):
						$this->flags_path = WP_PLUGIN_URL.'/polylang/flags';
						break;

					default:
						$this->flags_path = MBV_CORE_URL.'/images/flags';
				}
			}

			return $this->flags_path;
		}

		private function fetch_terms($textdomain = 'default') {
			$language_list = self::get_language_list();

			// Get original locale to reset it later on
			$original_locale = get_locale();

			foreach ($language_list as $language_info) {
				// Skip, if we already got this locale loaded
				if (!empty($this->terms[$textdomain][$this->locale])) {
					continue;
				}

				// What we need is the description (e.g. en_US)
				$this->locale = $language_info->description;

				unload_textdomain($textdomain);

				if ($textdomain == 'default') {
					load_default_textdomain();
				}
				else {
					$language_file = '/'.$textdomain.'-'.$this->locale.'.mo';

					load_textdomain($textdomain, $this->language_base.$this->language_path.$language_file);
					load_plugin_textdomain($textdomain, false, plugin_basename($this->language_base));
				}

				$this->terms[$textdomain][$this->locale] = get_translations_for_domain($textdomain);
			}

			// Reset locale to default value
			$this->locale = $original_locale;
			unload_textdomain($textdomain);

			if ($textdomain == 'default') {
				load_default_textdomain();
			}
			else {
				$language_file = '/'.$textdomain.'-'.$this->locale.'.mo';

				load_textdomain($textdomain, $this->language_base.$this->language_path.$language_file);
				load_plugin_textdomain($textdomain, false, plugin_basename($this->language_base));
			}
		}

		/**
		 * 
		 * 
		 * @param type $base_path
		 */
		public function set_language_base($base_path) {
			if (is_string($base_path)) {
				$this->language_base = $base_path;
			}
		}

		/**
		 * 
		 * 
		 * @param type $base_path
		 */
		public function set_language_folder($language_path) {
			if (is_string($base_path)) {
				$this->language_path = $language_path;
			}
		}

		/**
		 * Translate one string into all currently available languages. Useful for default values
		 * of forms with switchable language fields, for example Newsletter error messages, etc.
		 * Automatically applies vsprintf if more than the two standard parameters for the string
		 * and the textdomain are passed to the function.
		 * 
		 * @global object $polylang Global reference to the $polylang plugin variable
		 * @global string $locale Current Wordpress locale
		 * @param string $l10n_value String to be translated
		 * @param string $textdomain Textdomain for current string
		 * @return array Array with all translated strings
		 */
		public function parse_all_locales($l10n_value = null, $textdomain = 'default') {
			$real_args  = array();
			$translated = array();

			// The first two entries of the arguments array have to be removed as they
			// are already available as normal arguments and shouldn't be parsed later on
			if (func_num_args() > 2) {
				$real_args = func_get_args();
				array_splice($real_args, 0, 2);
			}

			if (empty($textdomain)) {
				$textdomain = 'default';
			}

			$this->fetch_terms($textdomain);

			if (!empty($real_args)) {
				foreach ($this->terms[$textdomain] as $language => $current_terms) {
					// If there are more than the first two arguments prepare to parse with vsprintf
					$parsed_args = array();

					foreach ($real_args as $arg) {
						if (!is_array($arg)) {
							$parsed_args[] = $arg;
							continue;
						}

						// Translate sub strings
						// Sub strings need to be array, with the second parameter being the textdomain
						// or an empty string for default
						list($l10n_sub, $subdomain) = $arg;
						if (empty($subdomain)) {
							$subdomain = 'default';
						}

						if ($subdomain != $textdomain) {
							if (empty($this->terms[$subdomain])) {
								$this->fetch_terms($subdomain);
							}

							$sub_terms = $this->terms[$subdomain][$language];
							$parsed_args[] = $sub_terms->translate($l10n_sub);
						}
						else {
							$parsed_args[] = $current_terms->translate($l10n_sub);
						}
					}

					$translated[$language] = vsprintf($current_terms->translate($l10n_value), $parsed_args);
				}
			}
			else {
				foreach ($this->terms[$textdomain] as $language => $current_terms) {
					$translated[$language] = $current_terms->translate($l10n_value);
				}
			}

			return $translated;
		}

		public function set_new_locale() {
			remove_filter('locale', array(&$this, 'set_new_locale'));

			return $this->locale;
		}
	}
?>
