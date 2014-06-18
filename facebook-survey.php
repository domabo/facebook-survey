<?php
/*
Plugin Name: Facebook Survey Module
Plugin URI: http://www.github.com/domabo/facebook-survey
Description: Use Facebook to authenticate user for suryey
Version: 0.1
Author: Domabo
Author URI: http://www.github.com/domabo
License: GPLv2 or later
*/

class fsm_Plugin {
	private static $fsm_instance;

	private function __construct() {
		$this->constants(); // Defines any constants used in the plugin
		$this->init();   // Sets up all the actions and filters
	}

	public static function getInstance() {
		if ( !self::$fsm_instance ) {
			self::$fsm_instance = new fsm_Plugin();
		}

		return self::$fsm_instance;
	}

	private function constants() {
		define( 'fsm_VERSION', '1.0' );
	}

	private function init() {
		// Register the options with the settings API
		add_action( 'admin_init', array( $this, 'fsm_register_settings' ) );

		// Add the menu page
		add_action( 'admin_menu', array( $this, 'fsm_setup_admin' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),array( $this, 'fsm_plugin_settings_link') );
        	add_shortcode( 'facebook-survey', array( $this,'fsm_shortcode_facebook_survey') );
	}
	
	//[facebook-survey]
	public function fsm_shortcode_facebook_survey( $atts ){
		
		 $a = shortcode_atts( array(
        'success' => 'facebook-survey-success',
        'failure' => '/',
    ), $atts );
		
        	return "
        <script>// <![CDATA[

if (window.fbAsyncInit.hasRun === true) {
  fbAsyncInit2(); // do something
} else {
  var oldCB = window.fbAsyncInit;
  window.fbAsyncInit = function () {
    if (typeof oldCB === 'function') { oldCB(); }
  fbAsyncInit2(); // do something
  };
}

var fbAsyncInit2 = function() { 
  checkLoginState();
};  

// This function is called on form load (after facebook initialized)
// and also on callback from Login button if we were not logged in
function checkLoginState() {
  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });
}

// This is called with the results from from FB.getLoginStatus().
function statusChangeCallback(response) {
  document.getElementById('loadingFB').style.display='none';
     if (response.status == 'connected') {
      // USER IS LOGGED IN AND HAS AUTHORIZED APP
      document.getElementById('registerFB').style.display='block';
      document.getElementById('loginFB').style.display='none';
      } else if (response.status == 'not_authorized') {
      // USER IS LOGGED IN TO FACEBOOK (BUT HASN'T AUTHORIZED YOUR APP YET)
      document.getElementById('registerFB').style.display='block';
      document.getElementById('loginFB').style.display='none';
      } else {
        document.getElementById('registerFB').style.display='none';
        document.getElementById('loginFB').style.display='block';
      }
  }

// ]]></script>

<div id='loginFB' style='display: none;'>
  <h2>First we need to register your identify using Facebook.  Please login into facebook</h2>
  <div class='fb-login-button' scope='public_profile,email' data-max-rows='1' data-size='large' data-show-faces='false' data-auto-logout-link='true' onlogin='checkLoginState();'></div>
  <h6>We will not post to your timeline or record any details other than those needed to confirm your identity as a stakeholder in Williamson County</h6>
</div>


<div id='loadingFB' style='display: block;' class="spinner"></div>



<div id='registerFB' style='display: none;'>
  <h2>First we need to register your identity using Facebook.</h2>
  <iframe src='https://www.facebook.com/plugins/registration?client_id=" . get_option('FACEBOOK_APP_ID'). ">&amp;redirect_uri=". plugins_url( 'facebook-survey-continue.php',  __FILE__ ) . "?success=". $a['success']." &amp;fb_only=true&amp;fields=name,first_name,last_name,email' width='450' height='450'>
  </iframe>
</div>
		";
        }

	public function fsm_plugin_settings_link( $links ) {
        $links[] = '<a href="'. get_admin_url(null, 'options-general.php?page=facebook-survey') .'">Settings</a>';
   	return $links;
	}

	public function fsm_register_settings() {
		register_setting( 'fsm-options', 'FACEBOOK_APP_ID' );
		register_setting( 'fsm-options', 'FACEBOOK_SECRET' );
		}


	public function fsm_setup_admin() {
		// Add our Menu Area
		add_options_page( 'Facebook Survey Module', 'Facebook Survey Module', 'administrator', 'facebook-survey', 
						  array( $this, 'fsm_admin_page' ) 
						);
	   }

	public function fsm_admin_page() {
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div><h2>Facebook Survey Settings</h2>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'fsm-options' ); ?>
				<?php settings_fields( 'fsm-options' ); ?>
			
			
			 <table class="form-table">
                            <tr valign="top">
                               <th scope="row">Facebook App Id</th>
                               <td><input type="text" name="FACEBOOK_APP_ID" value="<?php echo get_option( 'FACEBOOK_APP_ID'); ?>" />
                               <br /><span class='description'>Go to <a href='https://developers.facebook.com/apps'>https://developers.facebook.com/apps</a> for App Id</span></td>
                               
        		    </tr>
        		       <tr valign="top">
                               <th scope="row">Facebook Secret</th>
                               <td><input type="text" size="80" name="FACEBOOK_SECRET" value="<?php echo get_option( 'FACEBOOK_SECRET'); ?>" />
                                    <br /><span class='description'>Go to <a href='https://developers.facebook.com/apps'>https://developers.facebook.com/apps</a> for App Secret</span></td>
              
        		    </tr>
                         	<input type="hidden" name="action" value="update" />
				</table>
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
			</form>
		</div>
		<?php
	}
}


$fsm = fsm_Plugin::getInstance();
