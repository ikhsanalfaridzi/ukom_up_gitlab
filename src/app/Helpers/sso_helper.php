<?php

function is_valid_token($token)
{
    $introspect_url = getenv('SSO_BASE_URL') . '/realms/' . getenv('SSO_REALMS') . '/protocol/openid-connect/token/introspect';

    $client_id = getenv('SSO_CLIENT_ID');
    $client_secret = getenv('SSO_CLIENT_SECRET');

    $client = service('curlrequest');

    $response = $client->post($introspect_url, [
        'auth' => [$client_id, $client_secret],
        'form_params' => [
            'token' => $token
        ]
    ]);

    $response_body = json_decode($response->getBody(), true);

    return isset($response_body['active']) && $response_body['active'];
}

function refresh_token($refresh_token)
{
    $tokenUrl = getenv('SSO_BASE_URL') . '/realms/' . getenv('SSO_REALMS') . '/protocol/openid-connect/token';

    $client_id = getenv('SSO_CLIENT_ID');
    $client_secret = getenv('SSO_CLIENT_SECRET');

    $client = service('curlrequest');

    try {
        $response = $client->post($tokenUrl, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query([
                'grant_type' => 'refresh_token',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token
            ])
        ]);

        $response_body = json_decode($response->getBody(), true);

        if (!isset($response_body['access_token'])) {
            return false;
        }

        // Store new token
        session()->set('sso_login', [
            'id_token'      => $response_body['id_token'],
            'refresh_token' => $response_body['refresh_token']
        ]);

        return true;
    } catch (\Exception $e) {
        log_message('error', 'SSO Refresh Token error: ' . $e->getMessage());
        return false;
    }
}


function sso_logout()
{
    try {
        $token = session()->get('sso_login');

        if (empty($token)) {
            throw new Exception('No Token available for logout.');
        }

        $logout_url = getenv('SSO_BASE_URL') . '/realms/' . getenv('SSO_REALMS') . '/protocol/openid-connect/logout';

        $postData = [
            'client_id'     => getenv('SSO_CLIENT_ID'),
            'id_token_hint' => $token['id_token']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $logout_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new Exception('cURL Error: ' . $curl_error);
        }

        if ($http_code === 204 || $http_code === 200) {
            return [
                'status'  => 'success',
                'message' => 'Logout berhasil'
            ];
        } else {
            throw new Exception("Logout Keycloak gagal. HTTP Code: $http_code. Response: $response");
        }
    } catch (Exception $e) {
        log_message('error', 'Logout Error: ' . $e->getMessage());

        return [
            'status'  => 'error',
            'message' => $e->getMessage()
        ];
    }
}
