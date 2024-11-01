<?php

class WCD_Plugin_Settings {

    private $wcd_setting;

    public function __construct() {
        $this->wcd_setting = get_option('wcd_setting', '');


        add_action('admin_init', array($this, 'register_settings'));
        if (isset($_GET['page'])) {
            add_action('icl_extra_options_' . $_GET['page'], array(&$this, 'wcd_settings_section'));
        }
        add_action('wp_ajax_wcd_store_ajax', array($this, 'wcd_store_ajax'));
    }

    public function wcd_settings_section() {
        global $sitepress_settings;
        ?>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Country Detector Addon Options', 'wcdp') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <form id="wcd_cs_options" id="wcd_country_switcher_options" action="">
                            <?php wp_nonce_field('wcd_country_switcher_nonce', '_wcd_nonce'); ?>

                            <p>
                                <label><input type="checkbox" name="wcd_show_in_footer" <?php if (isset($this->wcd_setting['show_in_footer']))
                        echo 'checked="checked"' ?> value="1" /> 
                                    <?php _e("Enable Footer Switcher with user country flag", 'wcdp'); ?></label>
                            </p>
							
							<p>
                                <label><input type="radio" id="wcd_redirect" name="wcd_redirect" <?php if (!isset($this->wcd_setting) || empty($this->wcd_setting) )
                                echo 'checked="checked"' ?> value="1" /> 
                                    <?php _e("Disable country redirect  .", 'wcdp'); ?></label>
                            </p>
							
                            <p>
                                <label><input type="radio" id="wcd_redirect" name="wcd_redirect" <?php if (isset($this->wcd_setting['redirect']))
                                echo 'checked="checked"' ?> value="1" /> 
                                    <?php _e("Redirect visitors based on visitor country only if user locale translation exist .", 'wcdp'); ?></label>
                            </p>
							
							
							<p>
                                <label><input type="radio" id="wcd_redirect_always" name="wcd_redirect" <?php if (isset($this->wcd_setting['redirect_always']))
                                echo 'checked="checked"' ?> value="1" /> 
                                    <?php _e("Always redirect visitors based on user country (redirect to home page if user locale translations are missing) ", "wcdp"); ?></label>
                            </p>
							<p style="margin: 1.5em 2em;"> <?php _e("Follow us:", "wcdp"); ?> <a href="http://twitter.com/zantowp"><?php _e("Twitter", "wcdp"); ?></a> &nbsp; <?php _e("Get help:", "wcdp"); ?> <a href="http://zanto.org/support"><?php _e("Support", "wcdp"); ?></a>&nbsp; <?php _e("Get more:", "wcdp"); ?> <a href="http://shop.zanto.org"><?php _e("More Addons", "wcdp"); ?></a></p>

                            <p>
                                <input class="button" name="save" value="<?php echo __('Apply', 'wcdp') ?>" type="submit" />
                                <span class="icl_ajx_response" id="wcd_ajx_response_cso"></span>
                            </p>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <?php
    }

   
    public function register_settings() {
        register_setting('wcd_setting', 'wcd_setting', array($this, 'wcd_validate_settings'));
    }

   
    function wcd_store_ajax() {
        if (isset($_POST['action']) && $_POST['action'] == 'wcd_store_ajax') {
            $nonce = $_POST['_wpnonce'];
            if (wp_verify_nonce($nonce, 'wcd_country_switcher_nonce')) {
                $settings = array();

                if (isset($_POST['showInFooter']))
                    $settings['show_in_footer'] = true;
                if (isset($_POST['redirect'])){
                    $settings['redirect'] = true;
				}elseif (isset($_POST['redirect_always'])){
                    $settings['redirect_always'] = true;
				}
				
                update_option('wcd_setting', $settings);
                echo 7;
            }else {
                echo 0;
            }
            die();
        }
    }

    /**
     * Helper Settings function if you need a setting from the outside.
     * @return boolean is enabled
     */
    public function is_enabled($option) {
        if (!empty($this->wcd_setting[$option]) && isset($this->wcd_setting[$option])) {
            return true;
        }
        return false;
    }

    /**
     * Validate Settings
     * 
     * Filter the submitted data as per your request and return the array
     * 
     * @param array $input
     */
    public function wcd_validate_settings($input) {

        return $input;
    }

}