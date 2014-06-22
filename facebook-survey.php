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

   use Facebook\FacebookSession;
  use Facebook\FacebookRedirectLoginHelper;
  use Facebook\FacebookRequest;
  use Facebook\FacebookResponse;
  use Facebook\FacebookSDKException;
  use Facebook\FacebookRequestException;
  use Facebook\FacebookAuthorizationException;
  use Facebook\GraphObject;


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
  add_action( 'init', array( $this, 'fsm_init' ), 0 );

// Add the menu page
  add_action( 'admin_menu', array( $this, 'fsm_setup_admin' ) );

  add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),array( $this, 'fsm_plugin_settings_link') );
  add_shortcode( 'facebook-survey', array( $this,'fsm_shortcode_facebook_survey') );
  add_shortcode( 'facebook-survey-reg', array( $this,'fsm_shortcode_facebook_survey_reg') );
  add_shortcode( 'session', array( $this,'fsm_shortcode_session') );
  add_shortcode( 'session-destroy', array( $this,'fsm_shortcode_session_destroy') );
  add_shortcode( 'session-verify', array( $this,'fsm_shortcode_session_verify') );

  add_filter("gform_field_value_fsm_name",array( $this, "populate_name"));
  add_filter("gform_field_value_fsm_firstname",array( $this, "populate_firstname"));
  add_filter("gform_field_value_fsm_lastname",array( $this, "populate_lastname"));
  add_filter("gform_field_value_fsm_email",array( $this, "populate_email"));
  add_filter("gform_field_value_fsm_userid",array( $this, "populate_userid"));
  add_filter("gform_field_value_fsm_ip",array( $this, "populate_ip"));

}

public function fsm_init(){
  if(!session_id()) {
    session_start();
  }
}

public function populate_name($value){
  return $_SESSION["fsm_first_name"] . " " . $_SESSION["fsm_last_name"];
}

public function populate_firstname($value){
  return $_SESSION["fsm_first_name"] ;
}
public function populate_lastname($value){
  return  $_SESSION["fsm_last_name"];
}


public function populate_email($value){
  return $_SESSION["fsm_email"] ;
}

public function populate_userid($value){
  return $_SESSION["fsm_userid"] ;
}

public function populate_ip($value){
  return $_SESSION["fsm_ip"] ;
}


//[session var="fsm_userid"]

public function fsm_shortcode_session( $atts ){

  $a = shortcode_atts( array(
    'var' => 'fsm_userid'
    ), $atts );
  return $_SESSION[$a['var']];

}

public function fsm_shortcode_session_destroy( $atts ){
  session_destroy ();
  return "";

}

public function fsm_shortcode_session_verify( $atts ){
  if ($_SESSION["fsm_source"]!="FSM")
  {
    session_destroy ();
    return "<script>window.location='/'</script>";
  }
  else
    return "";

}

//[facebook-survey]
public function fsm_shortcode_facebook_survey( $atts ){
  
  if (!class_exists("FacebookSession"))
  {
  require_once( 'Facebook/FacebookSession.php' );
  require_once( 'Facebook/FacebookRedirectLoginHelper.php' );
  require_once( 'Facebook/FacebookRequest.php' );
  require_once( 'Facebook/FacebookResponse.php' );
  require_once( 'Facebook/FacebookSDKException.php' );
  require_once( 'Facebook/FacebookRequestException.php' );
  require_once( 'Facebook/FacebookAuthorizationException.php' );
  require_once( 'Facebook/GraphObject.php' );
  require_once( 'Facebook/HttpClients/FacebookCurl.php' );
  require_once( 'Facebook/HttpClients/FacebookHttpable.php' );
  require_once( 'Facebook/HttpClients/FacebookCurlHttpClient.php' );
  }
 
  $a = shortcode_atts( array(
    'success' => 'stakeholder-survey-detail',
    'failure' => '/'
    ), $atts );

  $_SESSION["success"] = $a['success'];
  $_SESSION["failure"] = $a['failure'];
  
  $FACEBOOK_APP_ID = get_option('FACEBOOK_APP_ID');
  $FACEBOOK_SECRET = get_option('FACEBOOK_SECRET');

  FacebookSession::setDefaultApplication($FACEBOOK_APP_ID, $FACEBOOK_SECRET);
  $redirect_url = plugins_url( 'facebook-survey-continue.php',  __FILE__ );
   $image_url = plugins_url( 'images/facebook-login.png',  __FILE__ );

  $helper = new FacebookRedirectLoginHelper($redirect_url );

  return "
  <div id='loginFB' style='display: block;'>
  <h2>Please sign in with Facebook to continue</h2>
    <div><a href='" . $helper->getLoginUrl(array("email")) . "'><img src='" . $image_url . "' alt='Sign in with Facebook' /></a></div>
  </div>";

}
//[facebook-survey-reg]
public function fsm_shortcode_facebook_survey_reg( $atts ){

  $a = shortcode_atts( array(
    'success' => 'stakeholder-survey-detail',
    'failure' => '/'
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
      reload_iframes();
      FB.getLoginStatus(function(response) {
        statusChangeCallback(response);
      });
}

function reload_iframes() {
  var f_list = document.getElementsByTagName('iframe');

  for (var i = 0, f; f = f_list[i]; i++) {
    f.src = f.src;
  }
}

// This is called with the results from from FB.getLoginStatus().
function statusChangeCallback(response) {
  if (response.status == 'connected') {
// USER IS LOGGED IN AND HAS AUTHORIZED APP
    document.getElementById('registerFB').style.visibility='visible';
    document.getElementById('registerFBLogout').style.display='block';
    document.getElementById('loginFB').style.display='none';
  } else if (response.status == 'not_authorized') {
// USER IS LOGGED IN TO FACEBOOK (BUT HASN'T AUTHORIZED YOUR APP YET)
    document.getElementById('registerFB').style.visibility='visible';
    document.getElementById('registerFBLogout').style.display='block';
    document.getElementById('loginFB').style.display='none';
  } else {
    document.getElementById('registerFB').style.visibility='hidden';
    document.getElementById('registerFBLogout').style.display='none';
    document.getElementById('loginFB').style.display='block';
  }
}

// ]]></script>

<div id='loginFB' style='display: none;'>
  <h2>Please login/register with Facebook to continue</h2>
  <div class='fb-login-button' scope='public_profile,email' data-max-rows='1' data-size='large' data-show-faces='false' data-auto-logout-link='true' onlogin='checkLoginState();'></div>
</div>
<div id='registerFB' style='visibility: hidden;'>
  <iframe id='registerFBframe' src='https://www.facebook.com/plugins/registration?client_id=" . get_option('FACEBOOK_APP_ID'). ">&amp;redirect_uri=". plugins_url( 'facebook-survey-continue.php',  __FILE__ ) . "?success=". $a['success']." &amp;fb_only=true&amp;fields=name,first_name,last_name,email' 
    width='450' height='340'>
  </iframe>
</div>
<div id='registerFBLogout' style='display: none;'>
  <h6>Not you? &nbsp;&nbsp;
    <div class='fb-login-button' scope='public_profile,email' data-max-rows='1' data-size='small' data-show-faces='false' data-auto-logout-link='true' onlogin='checkLoginState();'></div>
  </h6>
</div>";
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
