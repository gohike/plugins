<?php

/**
 * Plugin Name: Google Apps Login Premium
 * Plugin URI: http://wp-glogin.com/
 * Description: Simple secure login and user management for Wordpress through your Google Apps domain (uses secure OAuth2, and MFA if enabled)
 * Version: 3.2.1
 * Author: Dan Lester
 * Author URI: http://wp-glogin.com/
 * License: Premium Paid per WordPress site and Google Apps domain
 * Network: true
 * Text Domain: google-apps-login
 * Domain Path: /lang
 * 
 * Do not copy, modify, or redistribute without authorization from author Lesterland Ltd (contact@wp-glogin.com)
 * 
 * You need to have purchased a license to install this software on one website, to be used in 
 * conjunction with a Google Apps domain containing the number of users you specified when you
 * purchased this software.
 * 
 * You are not authorized to use, modify, or distribute this software beyond the single site license that you
 * have purchased.
 * 
 * You must not remove or alter any copyright notices on any and all copies of this software.
 * 
 * This software is NOT licensed under one of the public "open source" licenses you may be used to on the web.
 * 
 * For full license details, and to understand your rights, please refer to the agreement you made when you purchased it 
 * from our website at https://wp-glogin.com/
 * 
 * THIS SOFTWARE IS SUPPLIED "AS-IS" AND THE LIABILITY OF THE AUTHOR IS STRICTLY LIMITED TO THE PURCHASE PRICE YOU PAID 
 * FOR YOUR LICENSE.
 * 
 * Please report violations to contact@wp-glogin.com
 * 
 * Copyright Lesterland Ltd, registered company in the UK number 08553880
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (class_exists('commercial_google_apps_login')) {
	global $gal_core_already_exists;
	$gal_core_already_exists = true;
}
else {
	require_once( plugin_dir_path( __FILE__ ) . '/core/commercial_google_apps_login.php' );
}

class premium_google_apps_login extends commercial_google_apps_login {
	
	protected $PLUGIN_VERSION = '3.2.1';
	protected $WPGLOGIN_GA_STORE_URL = 'http://wp-glogin.com';
	protected $WPGLOGIN_GA_ITEM_NAME = 'Google Apps Login for WordPress Premium';
	protected $WPGLOGIN_GA_ITEM_ID = 149;

	// Singleton
	private static $instance = null;
	
	public static function get_instance() {
		if (null == self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	

	// AUX FUNCTIONS
	
	protected function get_eddsl_optname() {
		return 'eddsl_gal_premium_ls';
	}
	
	public function my_plugin_basename() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			$basename = basename(dirname(__FILE__)).'/'.basename(__FILE__);
		}
		return $basename;
	}
	
	protected function my_plugin_url() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			return plugins_url().'/'.basename(dirname(__FILE__)).'/';
		}
		// Normal case (non symlink)
		return plugin_dir_url( __FILE__ );
	}
	
}

// Global accessor function to singleton
function galpremiumGoogleAppsLogin() {
	return premium_google_apps_login::get_instance();
}

// Initialise at least once
galpremiumGoogleAppsLogin();

if (!function_exists('GoogleAppsLogin')) {
	function GoogleAppsLogin() {
		return galpremiumGoogleAppsLogin();
	}
}

?>
