<?php
echo "HELLO WORLD";

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
$me = (new FacebookRequest(
  $session, 'GET', '/me'
))->execute()->getGraphObject(GraphUser::className());
print_r($me);
}
?>