<?php

// adapted from wpml browser redirect code/

class WPML_Country_Redirect {

    static function init() {
        if (!is_admin() && !isset($_GET['redirect_to']) && !preg_match('#wp-login\.php$#', preg_replace("@\?(.*)$@", '', $_SERVER['REQUEST_URI']))) {
            add_action('wp_print_scripts', array('WPML_Country_Redirect', 'scripts'));
        }
    }

    static function scripts() {
        global $sitepress, $sitepress_settings, $wcd_plugin_base;

        $args['skip_missing'] = isset($wcd_plugin_base->settings['redirect_always'])?0:1;

        // Build multi language urls array
        $languages = $sitepress->get_ls_languages($args);
        $language_urls = array();
        $redirect_lang = false;
        foreach ($languages as $language) {
            if (strtolower($wcd_plugin_base->country_code) == strtolower(substr($language['tag'], 3, 2))) {
                $redirect_lang = $language['language_code'];
            }
			
            $language_urls[$language['language_code']] = $language['url'];
        }
		
		$redirect_lang = apply_filters('wcd_redirect_lang',$redirect_lang, $languages);

        if ($redirect_lang) {
            // Enque javascripts
            wp_enqueue_script('jquery.cookie', ICL_PLUGIN_URL . '/res/js/jquery.cookie.js', array('jquery'), ICL_SITEPRESS_VERSION);
            wp_enqueue_script('wpml-country-redirect', WCDP_URL . '/js/country-redirect.js', array('jquery', 'jquery.cookie'), ICL_SITEPRESS_VERSION);

            // Cookie parameters
            $http_host = $_SERVER['HTTP_HOST'] == 'localhost' ? '' : $_SERVER['HTTP_HOST'];
            $cookie = array(
                'name' => '_icl_country_lang_js',
                'domain' => (defined('COOKIE_DOMAIN') && COOKIE_DOMAIN ? COOKIE_DOMAIN : $http_host),
                'path' => (defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/'),
                'expiration' => 24 //well, assumption is that this person will stay in this country the next 24hrs
            );

            // Send params to javascript
            $params = array(
                'pageLanguage' => defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_bloginfo('language'),
                'languageUrls' => $language_urls,
                'country_lang' => $redirect_lang,
                'cookie' => $cookie
            );
            wp_localize_script('wpml-country-redirect', 'wpml_country_redirect_params', $params);
        }
    }

}

add_action('init', array('WPML_Country_Redirect', 'init'));
?>
