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
	 * Class for static language and localization functions
	 * 
	 * @package MBV Core Library
	 * @author Oliver Gärtner <og@mbv-media.com>
	 */
	class Language {
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
		public static function parseAllLocales($l10n_value = null, $textdomain = '') {
			global $polylang, $locale;

			$real_args  = array();
			$translated = array();

			// The first two entries of the arguments array have to be removed as they
			// are already available as normal arguments and shouldn't be parsed later on
			if (func_num_args() > 2) {
				$real_args = func_get_args();
				array_splice($real_args, 0, 2);
			}

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

			// Get original locale to reset it later on
			$original_locale = get_locale();
			foreach ($language_list as $language_info) {
				// What we need is the description (e.g. en_US)
				$locale = $language_info->description;

				if (!empty($real_args)) {
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
							$subdomain = '';
						}

						$parsed_args[] = __($l10n_sub, $subdomain);
					}

					$translated[get_locale()] = vsprintf(__($l10n_value, $textdomain), $parsed_args);
				}
				else {
					$translated[get_locale()] = __($l10n_value, $textdomain);
				}
			}

			// Reset locale to default value
			$locale = $original_locale;

			return $translated;
		}
	}
?>
