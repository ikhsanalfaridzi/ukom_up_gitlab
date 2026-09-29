<?php

/**
 * Login Feature from Authentication Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-19
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-14
 */

namespace App\Controllers\Authentication;

use App\Controllers\KV_Controller;
use App\Libraries\Authentication\AuthenticationLib;
use App\Models\Authentication\AuthenticationModel;
use Exception;

class Login extends KV_Controller
{
    protected $auth;
    protected $auth_lib;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->auth            = new AuthenticationModel();
        $this->auth_lib        = new AuthenticationLib();
        $this->_title          = 'Login';
        $this->_uri_name       = 'authentication/login';
        $this->_uri_name_view  = 'authentication/login';
    }

    public function getIndex()
    {
        if (session()->get('login_user_id')) {
            return redirect()->to('home/dashboard');
        }

        return parent::_index();
    }

    public function postIndex()
    {
        try {
            // Validate input
            if (!$this->validate($this->auth->validationRules, $this->auth->validationMessages)) {
                throw new Exception('Invalid input. Please check your credentials.');
            }

            $data = $this->request->getPost();

            if (empty($data['kv-captcha'])) {
                throw new Exception('Missing Captcha. Please do captcha verification.');
            }

            if ($data['kv-captcha'] === session()->get('kv-code')) {
                $result = $this->auth_lib->authenticate($data);

                // Remove captcha code 
                session()->remove('kv-code');

                return $this->response_success($result);
            } else {
                throw new Exception('Invalid Captcha.');
            }
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }
    }
}
