<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 1/29/2019
 * Time: 11:58 PM
 */
//if (!defined('TRACKER')) die('No.');

//header("Refresh:2; url=/tracker", true, 303);

//SSO Placeholder
/*
$_SESSION['badgeid'] = 1234;
$badgeID = $_SESSION['badgeid'];

session_regenerate_id();

setcookie("badge", $badgeID);
setcookie("session", session_id());
updateSession($badgeID, session_id());
*/
?>

<?php
if (!defined('TRACKER')) {
    define('TRACKER', TRUE);

    // Included from index
    require_once('../vendor/autoload.php');
    include('../includes/header.php');
} else {
    // Direct callback
    require_once('vendor/autoload.php');
}

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId' => '4',
    'clientSecret' => '7D863CA4-E42B-4345-9DC6-4ADD479917EE',
    'redirectUri' => 'https://tracker.goblfc.org/pages/sso.php',
    'urlAuthorize' => 'https://reg.goblfc.org/oauth/authorize',
    'urlAccessToken' => 'https://reg.goblfc.org/api/oauth/token',
    'urlResourceOwnerDetails' => 'https://reg.goblfc.org/api/users/current',
    'scopes' => 'pii:basic',
]);

if (!isset($_GET['code'])) {
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authorizationUrl);
    exit;
} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }
    exit('Invalid state'); // State info doesn't match, potential security breach
} else {
    try {
        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);

        $userInfo = $provider->getResourceOwner($accessToken)->toArray();

        $_SESSION['badgeid'] = $userInfo['id'];
        $_SESSION['accessToken'] = $accessToken->getToken();

        $badgeID = $_SESSION['badgeid'];

        session_regenerate_id();

        setcookie("badge", $badgeID, 0, "/");
        setcookie("session", session_id(), 0, "/");
        if (isset($isAdmin) && ($isAdmin || $isManager)) addLog($badgeID, "logIn", "ip:" . $_SERVER["HTTP_CF_CONNECTING_IP"]);
        //die(print_r($userInfo));
        updateSession($badgeID, $userInfo['firstName'], $userInfo['lastName'], $userInfo['username'], session_id());

        header("Refresh:0; url=/", true, 303);
        ?>

        <?php
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        exit($e->getMessage()); // Unable to get user info, should probably show an error that isn't the message
    }
}
?>