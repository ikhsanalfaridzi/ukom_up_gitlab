<?php

/**
 * Change Password Feature from Authentication Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-03-04
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-04
 */

namespace App\Controllers\Authentication;

use App\Controllers\KV_Controller;
use App\Models\Acl\UserReferenceModel;
use App\Models\Authentication\ChangePasswordModel;
use stdClass;

class Change_password extends KV_Controller
{
    protected $change_password;
    protected $user_reference;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->change_password = new ChangePasswordModel();
        $this->user_reference  = new UserReferenceModel();
        $this->_title          = 'Change Password';
        $this->_uri_name       = 'authentication/change_password';
        $this->_uri_name_view  = 'authentication/change_password';
    }

    public function getIndex()
    {
        if (empty(session()->get('login_user_id'))) {
            return redirect()->to(site_url('authentication/login'));
        }

        return parent::_index();
    }

    public function postIndex()
    {
        $data = $this->request->getPost();

        $user_id = session()->get('login_user_id');
        $result = $this->user_reference->where('user_id', $user_id)->first();

        if (!empty($result)) {
            $reference = [
                'secret'  => $data['password']
            ];

            $this->user_reference->update($result['id'], $reference);
        } else {
            $reference = [
                'id'      => uuid_v4(),
                'user_id' => $user_id,
                'secret'  => $data['password']
            ];

            $this->user_reference->save($reference);
        }

        // Validate input
        $this->validator->setRules($this->change_password->validationRules, $this->change_password->validationMessages);

        if (!$this->validator->run($data)) {
            return $this->response_error($this->validator->getErrors());
        }

        $user_id = session()->get('login_user_id');

        $this->change_password->update($user_id, ['password' => password_hash($data['password'], PASSWORD_ARGON2ID), 'is_change_password' => '0']);

        session()->remove('is_change_password');

        $result                = new stdClass();
        $result->status_code   = 200;
        $result->message       = 'Password Berhasil Diperbarui!';
        $result->redirect_url  = 'home/dashboard';
        $result->data          = new stdClass();

        return $this->response_success($result);
    }
}
