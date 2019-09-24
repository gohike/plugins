<?php
/**
 *
 * @link              https://bitbucket.org/mass_im/hike-support-plugin
 * @since             1.0.0
 * @package           Hike Support
 *
 * @wordpress-plugin
 * Plugin Name:       Hike Support Plugin
 * Plugin URI:        https://bitbucket.org/mass_im/hike-support-plugin
 * Description:       This plugin adds additional security to your website
 * Version:           1.0.0
 * Author:            Stef Winterswijk
 * Author URI:        http://mass.im
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hike-support-plugin
 * Domain Path:       /languages
 */

class WP_PM_User extends WP_User {

  function getEMAIL() {
    return $this->user_email;
  }

}

class WP_PM {

  protected $user;

  function __construct ( WP_PM_User $user = NULL) {
    if ( ! is_null( $user ) && $user->exists() ) $this->user = $user;
  }

  function getUser() {
    return $this->user;
  }

}

function getWPPM() {

  if ( ! did_action('wp_loaded') ) {
    $msg = 'Please call getCurrentUser after wp_loaded is fired.';
    return new WP_Error( 'to_early_for_user', $msg );
  }

  static $wp_pm = NULL;

  if ( is_null( $wp_pm ) ) {
    $wp_pm = new WP_PM( new WP_PM_User( get_current_user_id() ) );
  }

  return $wp_pm;
}


function getCurrentUser() {

  $wppm = getWPPM();

  if ( is_wp_error( $wppm ) ) return $wppm;

  $user = $wppm->getUser();

  if ( $user instanceof WP_PM_User ) return $user;
}

function my_gal_set_login_cookie($dosetcookie) {
    // Only set cookie on wp-login.php page
    return $GLOBALS['pagenow'] == 'wp-login.php';
}
add_filter('gal_set_login_cookie', 'my_gal_set_login_cookie');

add_action( 'wp_loaded', 'getCurrentUser' );

function hike_security_settings_page() { ?>

<div class="wrap">

    <h2><?php _e('Hike security settings', 'cookie-bar'); ?></h2>

    <form method="post" action="options.php" style="border-left:4px solid; border-bottom: 1px solid lightgray; padding: 20px; background: white;">
        <?php settings_fields( 'hike-security-settings' ); ?>
        <?php do_settings_sections( 'hike-security-settings' ); ?>
        <table class="form-table">
            <h2><?php _e('Gravity Forms - Delete Entry Data after Submission', 'hike-security'); ?></h2>
            <tr valign="top">
            <th scope="row"><?php _e('Disable entry deletion', 'hike-security'); ?></th>
            <td><input type="checkbox" name="hike_security_disable" value="1" <?php checked( '1', get_option( 'hike_security_disable' ) ); ?> /></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Exclude form IDs', 'hike-security'); ?></th>
            <td><input type="text" size="100" name="hike_security_forms" value="<?php echo esc_html( get_option('hike_security_forms') ); ?>" /><br><small>Forms to exclude. Example: 1,3,10</small></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

</div>

<?php }

function hike_security_settings() {
    register_setting( 'hike-security-settings', 'hike_security_disable' );
    register_setting( 'hike-security-settings', 'hike_security_forms' );
}
add_action( 'admin_init', 'hike_security_settings' );

if ( class_exists( 'GFCommon' ) && get_option( 'hike_security_disable' ) != 1 ) {

    add_action( 'gform_after_submission', 'remove_form_entry' );
    function remove_form_entry( $entry ) {

        $form_id = get_option( 'hike_security_forms' );
        $form_array = explode(',', $form_id);

        if ( ! in_array( $entry['form_id'], $form_array ) ) {

            GFAPI::delete_entry( $entry['id'] );

        }

    }

}


add_action( 'wp_loaded', function() {

    $current_user = getCurrentUser();

    if ( $current_user instanceof WP_PM_User ) {

        $email = $current_user->getEMAIL();
        $allowed = array('gohike.nl');

        $explodedEmail = explode('@', $email);
        $domain = array_pop($explodedEmail);

        if ( in_array($domain, $allowed) ) {

            function hike_security_menu() {
                add_options_page('Hike security', 'Hike security', 'administrator', 'hike-security-settings', 'hike_security_settings_page', 'dashicons-admin-generic');
            }
            add_action('admin_menu', 'hike_security_menu');

        }

        if ( ! in_array($domain, $allowed) ) {

            /*-----------------------------------------------------------------------------------*/
            /* Hike Support & Contact info block
            /*-----------------------------------------------------------------------------------*/
            function hike_support_widget() {
                if (get_locale() == 'en_US') {
                    echo "<p>For questions about the website you can reach us at:<br><br>Email: <a href='mailto:support@gohike.nl'>support@gohike.nl</a><br>Tel: 030-7371303</p>";
                } elseif (get_locale() == 'nl_NL') {
                    echo "<p>Voor vragen over de website kunt u ons bereiken op:<br><br>Email: <a href='mailto:support@gohike.nl'>support@gohike.nl</a><br>Tel: 030-7371303</p>";
                }
            }
            function add_hike_support_widget() {
                wp_add_dashboard_widget('hike_support_widget', 'Support & Contact info', 'hike_support_widget');
            }
            add_action('wp_dashboard_setup', 'add_hike_support_widget');

            /*-----------------------------------------------------------------------------------*/
            /* Disable theme and plugin editor
            /*-----------------------------------------------------------------------------------*/
            if ( ! defined( 'ABSPATH' ) ){
                exit; // Exit if accessed this file directly
            }

            if( !defined('DISALLOW_FILE_EDIT') ){
                define( 'DISALLOW_FILE_EDIT', true );
            }

            /*-----------------------------------------------------------------------------------*/
            /* Remove Unwanted Admin Menu Items
            /*-----------------------------------------------------------------------------------*/
            function remove_admin_menu_items() {
                $remove_menu_items = array(__('Plugins'));
                global $menu;
                    remove_submenu_page( 'themes.php', 'themes.php' );
                end ($menu);
                while (prev($menu)){
                    $item = explode(' ',$menu[key($menu)][0]);
                    if(in_array($item[0] != NULL?$item[0]:"" , $remove_menu_items)){
                    unset($menu[key($menu)]);}
                }
            }

            add_action('admin_menu', 'remove_admin_menu_items');
            add_filter('acf/settings/show_admin', '__return_false');

            /*-----------------------------------------------------------------------------------*/
            /* Hide update notifications
            /*-----------------------------------------------------------------------------------*/
            function remove_core_updates(){
            global $wp_version;return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,);
            }
            add_filter('pre_site_transient_update_core','remove_core_updates'); //hide updates for WordPress itself
            add_filter('pre_site_transient_update_plugins','remove_core_updates'); //hide updates for all plugins
            add_filter('pre_site_transient_update_themes','remove_core_updates'); //hide updates for all themes

            /*-----------------------------------------------------------------------------------*/
            /* Redirect user to dashboard if accessing plugins.php or themes.php
            /*-----------------------------------------------------------------------------------*/
            if ( $_SERVER['PHP_SELF'] == '/wp-admin/plugins.php' || $_SERVER['PHP_SELF'] == '/wp-admin/themes.php' || $_SERVER['PHP_SELF'] == '/wp-admin/plugin-install.php' ) {
              wp_redirect(admin_url() );
              exit;
            }

            /*-----------------------------------------------------------------------------------*/
            /* Enforce strong passwords
            /*-----------------------------------------------------------------------------------*/
            function slt_strongPasswords( $errors ) {
                $enforce = true;
                if ( $enforce && !$errors->get_error_data("pass") && $_POST["pass1"] && slt_passwordStrength( $_POST["pass1"], $_POST["user_login"] ) != 4 ) {
                        $errors->add( 'pass', __( '<strong>ERROR</strong>: Please make the password a strong one.' ) );
                }
                return $errors;
            }
            add_action( 'user_profile_update_errors', 'slt_strongPasswords', 0, 3 );

            // Check for password strength
            // Copied from JS function in WP core: /wp-admin/js/password-strength-meter.js
            function slt_passwordStrength( $i, $f ) {
                $h = 1; $e = 2; $b = 3; $a = 4; $d = 0; $g = null; $c = null;
                if ( strlen( $i ) < 4 )
                    return $h;
                if ( strtolower( $i ) == strtolower( $f ) )
                    return $e;
                if ( preg_match( "/[0-9]/", $i ) )
                    $d += 10;
                if ( preg_match( "/[a-z]/", $i ) )
                    $d += 26;
                if ( preg_match( "/[A-Z]/", $i ) )
                    $d += 26;
                if ( preg_match( "/[^a-zA-Z0-9]/", $i ) )
                    $d += 31;
                $g = log( pow( $d, strlen( $i ) ) );
                $c = $g / log( 2 );
                if ( $c < 40 )
                    return $e;
                if ( $c < 56 )
                    return $b;
                return $a;
            }

            /*-----------------------------------------------------------------------------------*/
            /* Hide Sucuri notifications
            /*-----------------------------------------------------------------------------------*/
            add_action('admin_head', 'hide_notifications');

            function hide_notifications() {
              echo '<style>
                .sucuriscan-setup-notice {
                  display: none;
                }
                .wrap .error, .wrap .updated {
                    display: none;
                }
              </style>';
            }

        }

    }

}, 30 );

?>
