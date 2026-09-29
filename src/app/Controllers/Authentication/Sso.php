<?php

namespace App\Controllers\Authentication;

use App\Controllers\KV_Controller;
use App\Libraries\Authentication\AuthenticationLib;
use App\Models\Acl\UserModel;
use Exception;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Sso extends KV_Controller
{
    private $provider;
    protected $auth_lib;

    public function __construct()
    {
        $this->auth_lib = new AuthenticationLib();
        $this->provider = new GenericProvider([
            'clientId'                => getenv('SSO_CLIENT_ID'),
            'clientSecret'            => getenv('SSO_CLIENT_SECRET'),
            'redirectUri'             => getenv('SSO_REDIRECT_URI'),
            'urlAuthorize'            => getenv('SSO_BASE_URL') . '/realms/' . getenv('SSO_REALMS') . '/protocol/openid-connect/auth',
            'urlAccessToken'          => getenv('SSO_BASE_URL') . '/realms/' . getenv('SSO_REALMS') . '/protocol/openid-connect/token',
            'urlResourceOwnerDetails' => getenv('SSO_BASE_URL') . '/realms/' . getenv('SSO_REALMS') . '/protocol/openid-connect/userinfo',
            'pkceMethod'              => GenericProvider::PKCE_METHOD_S256,
            'scopes'                  => 'openid profile email',
        ]);
    }

    public function getLogin()
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        session()->set('oauth2state', $this->provider->getState());
        session()->set('oauth2pkceCode', $this->provider->getPkceCode());

        return redirect()->to($authorizationUrl);
    }

    public function getCallback()
    {
        $state = $this->request->getGet('state');

        if (empty($state) || ($state !== session()->get('oauth2state'))) {
            session()->remove('oauth2state');
            return 'Invalid state';
        }

        $code = $this->request->getGet('code');

        try {
            $this->provider->setPkceCode(session()->get('oauth2pkceCode'));

            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);

            $resourceOwner = $this->provider->getResourceOwner($token);
            $user = $resourceOwner->toArray();

            session()->set('sso_login', [
                'token'         => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'id_token'      => $token->getValues()['id_token'],
                'id'            => $user['sub'],
                'full_name'     => $user['name'],
                'username'      => $user['preferred_username'],
                'email'         => $user['email'],
                'user'          => $user,
                'token'         => $token,
            ]);

            try {
                $this->auth_lib->authenticate(['username' => $user['preferred_username']], TRUE);

                return redirect()->to(site_url('home/dashboard'));
            } catch (Exception $e) {
                return $this->response_error($e->getMessage());
            }
        } catch (IdentityProviderException $e) {
            return $this->response_error($e->getMessage());
        }
    }
}
