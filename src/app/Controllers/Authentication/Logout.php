<?php

/**
 * Logout Feature from Authentication Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-19
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-04
 */

namespace App\Controllers\Authentication;

use App\Controllers\KV_Controller;
use App\Libraries\Authentication\AuthenticationLib;

class Logout extends KV_Controller
{
    protected $auth_lib;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->auth_lib = new AuthenticationLib();
    }

    public function getIndex()
    {
        $sso_login = session()->get('sso_login');

        if (!empty($sso_login)) {
            $response = sso_logout();

            if ($response['status'] === 'success') {
                session()->destroy();

                $this->auth_lib->save_log('Logout', kv_decrypt(session()->get('login_username'), getenv('APP_KEY')), session()->get('login_user_id'));

                return redirect()->to(site_url('authentication/login'));
            } else {
                return redirect()->back()->with('error', $response['message']);
            }
        } else {
            session()->destroy();

            $this->auth_lib->save_log('Logout', kv_decrypt(session()->get('login_username'), getenv('APP_KEY')), session()->get('login_user_id'));

            return redirect()->to(site_url('authentication/login'));
        }
    }
}
