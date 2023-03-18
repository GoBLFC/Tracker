<?php

require "main.php";

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId' => $OAUTH_CLIENT_ID,
    'clientSecret' => $OAUTH_CLIENT_SECRET,
    'redirectUri' => "{$CANONICAL_URL}/sso.php",
    'urlAuthorize' => "{$OAUTH_CONCAT_BASE_URL}/oauth/authorize",
    'urlAccessToken' => "{$OAUTH_CONCAT_BASE_URL}/api/oauth/token",
    'urlResourceOwnerDetails' => "{$OAUTH_CONCAT_BASE_URL}/api/users/current",
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
		
        $_SESSION["badgeid"] = $userInfo["id"];
        $_SESSION['accessToken'] = $accessToken->getToken();

        if (!$db->getUser($userInfo["id"])->fetch()) {
            $db->createUser($userInfo["id"], $userInfo["firstName"], $userInfo["lastName"], $userInfo["username"]);
        }

        header("Refresh:0; url=/", true, 303);

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        exit($e->getMessage()); // Unable to get user info, should probably show an error that isn't the message
    }
}

?>
