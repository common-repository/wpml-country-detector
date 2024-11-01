<?php
/**
 * Plugin Name: WPML Country Detector
 * Description: Detects the user country and shows the right user flag. Has options to redirect user to their country pages basing on the geographical part in the language locale
 * Plugin URI: http://shop.zanto.org
 * Author: Ayebare Mucunguzi Brooks
 * Author URI: http://zanto.org
 * Version: 0.2
 * Text Domain: wcdp
 * License: GPL2

  /**
 * Get some constants ready for paths when your plugin grows 
 * 
 */
define('WCDP_VERSION', '0.2');
define('WCDP_PATH', dirname(__FILE__));
define('WCDP_PATH_INCLUDES', dirname(__FILE__) . '/inc');
define('WCDP_FOLDER', basename(WCDP_PATH));
define('WCDP_URL', plugins_url() . '/' . WCDP_FOLDER);
define('WCDP_URL_INCLUDES', WCDP_URL . '/inc');

/**
 * 
 * The plugin base class - the root of all WP goods!
 * 
 * @author nofearinc
 *
 */
class WCD_Plugin_Base {

    private $user_ip = '';

    /**
     * 
     * Assign everything as a call from within the constructor
     */
    function __construct() {

        global $sitepress_settings, $geo_data;
        $this->user_ip = $this->get_user_ip();
        //$this->user_ip = "2.16.7.255" //for testing
        if (!class_exists('GeoIP')) {
            include(WCDP_PATH_INCLUDES . "/geoip.inc");
        }
        $geo_data = geoip_open(WCDP_PATH_INCLUDES . "/GeoIP.dat", GEOIP_STANDARD);

        $this->country_code = geoip_country_code_by_addr($geo_data, $this->user_ip);
        $this->country_name = geoip_country_name_by_addr($geo_data, $this->user_ip);
        geoip_close($geo_data);
        $this->settings = get_option('wcd_setting', '');
        // add scripts and styles only available in admin
        add_action('admin_enqueue_scripts', array($this, 'wcd_add_admin_JS'));
        add_action('init', array(&$this, 'init'));

        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, 'wcd_on_activate_callback');
        register_deactivation_hook(__FILE__, 'wcd_on_deactivate_callback');

        // Translation-ready
        add_action('plugins_loaded', array($this, 'wcd_add_textdomain'));
		add_filter('plugin_row_meta', array($this, 'plugin_support_link'), 10, 2 );

        // Add earlier execution as it needs to occur before admin page display
        add_action('admin_init', array($this, 'wcd_register_settings'), 5);
        
		if(isset($this->settings['redirect']) || isset($this->settings['redirect_always'])){
		      add_action('wp_enqueue_scripts', array($this,'remove_unwanted_scripts'),15);
              require_once WCDP_PATH_INCLUDES . '/country-redirect.php';    
        }
    }
    
	
    function get_user_ip() {

            foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
                if (array_key_exists($key, $_SERVER) === true) {
                    foreach (explode(',', $_SERVER[$key]) as $ip) {
                        $ip = trim($ip); // just to be safe

                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                            return $ip;
                        }
                    }
                }
            }
    }

    function init() {
        global $icl_language_switcher, $sitepress_settings;
        $this->wpml_settings = $sitepress_settings;
		if(isset($this->settings['show_in_footer'])){ 
		    add_action('wp_footer', array(&$this, 'language_selector_footer'), 19);
            add_action('wp_head', array(&$icl_language_switcher, 'language_selector_footer_style'));
		}
        add_action('wcdp_lang_switcher', array($this, 'lang_switcher'));
    }

    /**
     *
     * Adding JavaScript scripts for the admin pages only
     *
     * Loading existing scripts from wp-includes or adding custom ones
     *
     */
    function wcd_add_admin_JS() {
        wp_enqueue_script('jquery');
        wp_register_script('samplescript-admin', plugins_url('/js/samplescript-admin.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('samplescript-admin');
    }
	
	function remove_unwanted_scripts(){
	    wp_deregister_script('wpml-browser-redirect');
	}
	
	function plugin_support_link($links, $file) {
            if ($file == WCDP_FOLDER.'/wpml-country-detector.php'){
                return array_merge($links, 
				array( sprintf('<a href="http://zanto.org/support">%s</a>', __('Support','Zanto')) ),
				array( sprintf('<a href="http://shop.zanto.org">%s</a>', __('Addons','Zanto')) )
				);
            }
            return $links;
        }

    /**
     * 
     *  widget langswitcher
     *   
     */
    function lang_switcher() {
        // Mobile or auto
        global $sitepress;

        if ($this->wpml_settings['icl_lang_sel_type'] == 'list') {
            $this->widget_list();
        }

        $active_languages = $sitepress->get_ls_languages();

        foreach ($active_languages as $k => $al) {
            if ($al['active'] == 1) {
                $main_language = $al;
                unset($active_languages[$k]);
                break;
            }
        }


        global $w_this_lang;


        if ($w_this_lang['code'] == 'all') {
            $main_language['native_name'] = __('All languages', 'wcdp');
        }

        
        ?>
        <div id="lang_sel"<?php if ($this->wpml_settings['icl_lang_sel_type'] == 'list')
            echo ' style="display:none;"'; ?> <?php if ($sitepress->is_rtl()): ?>class="icl_rtl"<?php endif; ?> >
            <ul>
                <li><a href="#" class="lang_sel_sel icl-<?php echo $w_this_lang['code'] ?>">
                        <?php if ($this->get_flag_url()): ?>                
                            <img class="iclflag" src="<?php echo $this->get_flag_url() ?>" alt="<?php echo $this->country_name ?>"  title="<?php echo $this->country_name ?>" />                                
                            &nbsp;<?php
            endif;

            if ($this->wpml_settings['icl_lso_native_lang']) {
                $lang_native = $main_language['native_name'];
            } else {
                $lang_native = false;
            }
            if ($this->wpml_settings['icl_lso_display_lang']) {
                $lang_translated = $main_language['translated_name'];
            } else {
                $lang_translated = false;
            }

            $lang_native_hidden = false;
            $lang_translated_hidden = false;


            echo icl_disp_language($lang_native, $lang_translated, $lang_native_hidden, $lang_translated_hidden);

            if (!isset($ie_ver) || $ie_ver > 6):
                            ?></a><?php endif; ?>
                    <?php if (!empty($active_languages)): ?>
                            <?php if (isset($ie_ver) && $ie_ver <= 6): ?><table><tr><td><?php endif ?>            
                                    <ul>
                                        <?php $active_languages_ordered = $sitepress->order_languages($active_languages); ?>
                                        <?php foreach ($active_languages_ordered as $lang): ?>
                                            <li class="icl-<?php echo $lang['language_code'] ?>">          
                                                <a rel="alternate" hreflang="<?php echo $lang['language_code'] ?>" href="<?php echo apply_filters('WPML_filter_link', $lang['url'], $lang) ?>">

                                                    <?php
                                                    if ($this->wpml_settings['icl_lso_native_lang']) {
                                                        $lang_native = $lang['native_name'];
                                                    } else {
                                                        $lang_native = false;
                                                    }
                                                    if ($this->wpml_settings['icl_lso_display_lang']) {
                                                        $lang_translated = $lang['translated_name'];
                                                    } else {
                                                        $lang_translated = false;
                                                    }

                                                    echo icl_disp_language($lang_native, $lang_translated, $lang_native_hidden, $lang_translated_hidden);
                                                    ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>            
                                    <?php if (isset($ie_ver) && $ie_ver <= 6): ?></td></tr></table></a><?php endif ?> 
                    <?php endif; ?>
                </li>
            </ul>    
        </div><?php
        }

        function widget_list() {
            global $sitepress, $w_this_lang;
			
            if ($w_this_lang['code'] == 'all') {
                $main_language['native_name'] = __('All languages', 'wcdp');
            }
            $active_languages = icl_get_languages('skip_missing=0');
            //print_r($active_languages);
            if (empty($active_languages))
                return;
				
			foreach ($active_languages as $k => $al) {
            if ($al['active'] == 1) {
                $main_language = $al; 
                unset($active_languages[$k]);
                break;
            }
			}
			array_unshift($active_languages, $main_language);
        
                    ?>

        <div id="lang_sel_list"<?php if (empty($this->wpml_settings['icl_lang_sel_type']) || $this->wpml_settings['icl_lang_sel_type'] == 'dropdown')
            echo ' style="display:none;"'; ?> class="lang_sel_list_<?php echo $this->wpml_settings['icl_lang_sel_orientation'] ?>">           
            <ul>
                <?php foreach ($active_languages as $index=>$lang): ?>

                    <li class="icl-<?php echo $lang['language_code'] ?>">          
                        <a href="<?php echo apply_filters('WPML_filter_link', $lang['url'], $lang) ?>"<?php
            if ($lang['language_code'] == $sitepress->get_current_language())
                echo ' class="lang_sel_sel"'; else
                echo ' class="lang_sel_other"';
                    ?>>
                               <?php if ($this->get_flag_url() && !$index): ?>                
                                <img class="iclflag" src="<?php echo $this->get_flag_url() ?>" alt="<?php echo $this->country_name ?>" title="<?php echo $this->country_name ?>" />&nbsp;                    
                            <?php endif; ?>
                            <?php
                            if ($this->wpml_settings['icl_lso_native_lang']) {
                                $lang_native = $lang['native_name'];
                            } else {
                                $lang_native = false;
                            }
                            if ($this->wpml_settings['icl_lso_display_lang']) {
                                $lang_translated = $lang['translated_name'];
                            } else {
                                $lang_translated = false;
                            }

                            echo @icl_disp_language($lang_native, $lang_translated, $lang_native_hidden, $lang_translated_hidden);
                            ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    function language_selector_footer() {

        $languages = icl_get_languages('skip_missing=0');

        if (!empty($languages)) {
		
		
		foreach ($languages as $k => $al) {
            if ($al['active'] == 1) {
                $main_language = $al; 
                unset($languages[$k]);
                break;
            }
		}
			array_unshift($languages, $main_language);
			
            echo '
                <div id="lang_sel_footer">
     
                    <ul>
                    ';
			if ($this->get_flag_url()){
				echo'<li class="wpc-footer-flag"><img class="iclflag" src="' . $this->get_flag_url() . '" alt="' . $this->country_name . ' "  title="' . $this->country_name . '" /></li>';
			}
			
            foreach ($languages as $lang) {

                $alt_title_lang = $this->wpml_settings['icl_lso_native_lang'] ? esc_attr($lang['native_name']) : esc_attr($lang['translated_name']);

                echo '    <li>';
				
                echo '<a rel="alternate" hreflang="' . $lang['language_code'] . '" href="' . apply_filters('WPML_filter_link', $lang['url'], $lang) . '"';
                
				if ($lang['active'])
                    echo ' class="lang_sel_sel"';
                echo '>';

                if ($this->wpml_settings['icl_lso_native_lang']) {
                    $lang_native = $lang['native_name'];
                } else {
                    $lang_native = false;
                }
                if ($this->wpml_settings['icl_lso_display_lang']) {
                    $lang_translated = $lang['translated_name'];
                } else {
                    $lang_translated = false;
                }
                $lang_native_hidden = false;
                $lang_translated_hidden = false;

                echo icl_disp_language($lang_native, $lang_translated, $lang_native_hidden, $lang_translated_hidden);
                echo '</a>';
                echo '</li>
                    ';
            }
            echo '</ul>
                </div>';
        }
    }

    function get_flag_url() {
        if ($this->country_code) {
            $flag_url = ICL_PLUGIN_URL . '/res/flags/' . $this->country_code . '.png';
        } else {
            $flag_url = false;
        }
        return apply_filters('wcd_flag_url', $flag_url, $this->country_code);
    }

    /**
     * Initialize the Settings class
     * 
     * Register a settings section with a field for a secure WordPress admin option creation.
     * 
     */
    function wcd_register_settings() {
        require_once( WCDP_PATH . '/wcd-plugin-settings.class.php' );
        new WCD_Plugin_Settings();
    }

    /**
     * Add textdomain for plugin
     */
    function wcd_add_textdomain() {
        load_plugin_textdomain('mudbase', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }

}

class Language_Switcher_Widget extends WP_Widget {

    function __construct() {
        $widget_ops = array(
            'classname' => 'wcdp_ls_widget_class',
            'description' => __('Country language Switcher.', 'wcdp')
        );
        $this->WP_Widget('wcdp_multilingual_ls', __('WPML Country Detector Switcher', 'wcdp'), $widget_ops);
    }

    function form($instance) {
        $defaults = array(
            'title' => __('Choose Language', 'wcdp'),
        );
        global $wcdp_ls_types;



        $instance = wp_parse_args((array) $instance, $defaults);
        $title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function widget($args, $instance) {
        extract($args);
        echo $before_widget;
        $title = apply_filters('widget_title', $instance['title']);
        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        do_action('wcdp_lang_switcher');

        echo $after_widget;
    }

}

function wcdp_widgets_init() {
    register_widget('Language_Switcher_Widget');
}

add_action('widgets_init', 'wcdp_widgets_init');

/**
 * Register activation hook
 *
 */
function wcd_on_activate_callback() {
    // do something on activation
}

/**
 * Register deactivation hook
 *
 */
function wcd_on_deactivate_callback() {
    // do something when deactivated
}

// Initialize everything
$wcd_plugin_base = new WCD_Plugin_Base();
