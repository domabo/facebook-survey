<?php
echo 'hello';
# No need for the template engine
define( 'WP_USE_THEMES', false );
# Load WordPress Core
// Assuming we're in a subdir: "~/wp-content/plugins/current_dir"
require_once( '../../../wp-load.php' );
$FACEBOOK_APP_ID = get_option('FACEBOOK_APP_ID');
$FACEBOOK_SECRET = get_option('FACEBOOK_SECRET');

// No need to change the function body
function parse_signed_request($signed_request, $secret) 
{
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);
// decode the data
    $sig = base64_url_decode($encoded_sig);
    $data = json_decode(base64_url_decode($payload), true);
    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
    {
        error_log('Unknown algorithm. Expected HMAC-SHA256');
        return null;
    }

// check sig
    $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
    if ($sig !== $expected_sig) 
    {
        error_log('Bad Signed JSON signature!');
        return null;
    }
    return $data;
}

function base64_url_decode($input) 
{
    return base64_decode(strtr($input, '-_', '+/'));
}

if ($_REQUEST) 
{
    $response = parse_signed_request($_REQUEST['signed_request'],
        $FACEBOOK_SECRET);

    if ($response == null)
    {
        echo 'invalid';
        exit;
    }

    $ip = getenv('HTTP_CLIENT_IP')?:
    getenv('HTTP_X_FORWARDED_FOR')?:
    getenv('HTTP_X_FORWARDED')?:
    getenv('HTTP_FORWARDED_FOR')?:
    getenv('HTTP_FORWARDED')?:
    getenv('REMOTE_ADDR');

    echo
    $response["registration"]["email"] .
    $response["registration"]["first_name"] .
    $response["registration"]["last_name"] .
    $ip .
    $response["user_id"];
    
    $session = new FacebookSession($response["oauth_token"]);
    
    $request = new FacebookRequest($session, 'GET', '/me/friends');
    $response = $request->execute();
    $graphObject = $response->getGraphObject();
    $friends = $graphObject ->asArray();
    
    echo 'ok';
    
    foreach($friends['data'] as $friend) {
        echo $friend->id . $friend->name;
      //  $this->_friends[$friend->id] = $friend->name;
        }


    /* Redirect browser */
// header("Location: ". get_home_url(null, $_GET['success']));

    /* Make sure that code below does not get executed when we redirect. */
    exit;
}
else 
{
    echo '$_REQUEST is empty';
}
?>
