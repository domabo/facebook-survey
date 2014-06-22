<?php
define( 'WP_USE_THEMES', false );
# Load WordPress Core
// Assuming we're in a subdir: "~/wp-content/plugins/current_dir"
require_once( '../../../wp-load.php' );

require_once( 'Facebook/FacebookSession.php' );
require_once( 'Facebook/FacebookRedirectLoginHelper.php' );
require_once( 'Facebook/FacebookRequest.php' );
require_once( 'Facebook/FacebookResponse.php' );
require_once( 'Facebook/FacebookSDKException.php' );
require_once( 'Facebook/FacebookRequestException.php' );
require_once( 'Facebook/FacebookAuthorizationException.php' );
require_once( 'Facebook/GraphObject.php' );
require_once( 'Facebook/GraphUser.php' );
require_once( 'Facebook/HttpClients/FacebookCurl.php' );
require_once( 'Facebook/HttpClients/FacebookHttpable.php' );
require_once( 'Facebook/HttpClients/FacebookCurlHttpClient.php' );

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;

$FACEBOOK_APP_ID = get_option('FACEBOOK_APP_ID');
$FACEBOOK_SECRET = get_option('FACEBOOK_SECRET');

FacebookSession::setDefaultApplication($FACEBOOK_APP_ID, $FACEBOOK_SECRET);
$redirect_url = plugins_url( 'facebook-test-redirect.php',  __FILE__ );

$helper = new FacebookRedirectLoginHelper($redirect_url);
try {
	$session = $helper->getSessionFromRedirect();
} catch(FacebookRequestException $ex) {
// When Facebook returns an error
} catch(\Exception $ex) {
// When validation fails or other local issues
}

if ($session) {

	try {

		$user = (new FacebookRequest(
			$session, 'GET', '/me'
			))->execute()->getGraphObject(GraphUser::className());

	} catch(FacebookRequestException $e) {

		echo "Exception occured, code: " . $e->getCode();
		echo " with message: " . $e->getMessage();

	}   

	$ip = getenv('HTTP_CLIENT_IP')?:
	getenv('HTTP_X_FORWARDED_FOR')?:
	getenv('HTTP_X_FORWARDED')?:
	getenv('HTTP_FORWARDED_FOR')?:
	getenv('HTTP_FORWARDED')?:
	getenv('REMOTE_ADDR');

	$_SESSION["fsm_email"] = $user->email;
	$_SESSION["fsm_first_name"] = $user->first_name;
	$_SESSION["fsm_last_name"] = $user->last_name;
	$_SESSION["fsm_userid"] = $user->id;
	$_SESSION["fsm_ip"] = $ip;
	$_SESSION["fsm_source"] = "FSM";

	echo "HELLO " . $user->email;

	//header("Location: ". get_home_url(null, $_GET['success']));

}
else 
{
	//header("Location: ". get_home_url());
}
?>