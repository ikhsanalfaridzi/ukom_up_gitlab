<?php

namespace App\Libraries\Authentication;

use App\Libraries\System\SystemLib;
use App\Models\Acl\UserGroupModel;
use App\Models\Acl\UserModel;
use Config\Database;
use Config\Services;
use Exception;
use stdClass;

class AuthenticationLib
{
    protected $db;
    protected $request;
    protected $sys_lib;
    protected $user;
    protected $user_group;
    protected $response;

    function __construct()
    {
        $this->db                      = Database::connect();
        $this->request                 = Services::request();
        $this->sys_lib                 = new SystemLib();
        $this->user                    = new UserModel();
        $this->user_group              = new UserGroupModel();
        $this->response                = new stdClass();
        $this->response->status_code   = 200;
        $this->response->message       = 'Login Berhasil!';
        $this->response->redirect_url  = 'home/dashboard';
        $this->response->data          = new stdClass();
    }

    public function authenticate($data, $is_sso_login = FALSE)
    {
        $user = $this->user->select('au.id, au.username, au.password, au.is_active, au.is_change_password, ip.employee_personnel_id')
            ->where('au.username', kv_encrypt($data['username'], getenv('APP_KEY')))
            ->join('identity.personal ip', 'ip.acl_user_id = au.id', 'left')
            ->from('acl.users au')
            ->first();

        if (empty($user)) {
            $this->save_log('Account Not Registered', $data['username'], NULL);

            throw new Exception('Akun belum terdaftar, silahkan hubungi administrator.');
        }

        // Get User Role
        $user_groups = $this->user_group->select('ag.group_name')
            ->join('acl.groups ag', 'acl.user_groups.group_id = ag.id')
            ->where('user_id', $user['id'])->findAll();

        $groups = array_column($user_groups, 'group_name');

        // Check if the user exists and the password matches
        if (!$is_sso_login) {
            if (!$user || !password_verify($data['password'], $user['password'])) {
                $this->save_log('Failed Login', $data['username'], NULL);

                throw new Exception('Username atau password salah.');
            }
        }

        if ($is_sso_login && empty($user)) {
            $response = sso_logout();

            if ($response['status'] === 'success') {
                session()->destroy();

                throw new Exception('Pengguna belum terdaftar di aplikasi, silahkan hubungi administrator.');
            } else {
                throw new Exception($response['message']);
            }
        }

        if (!$user['is_active'] === "0") {
            $this->save_log('Account Disabled', $data['username'], NULL);

            throw new Exception('Akun telah dinonaktifkan, silahkan hubungi administrator.');
        }

        // Set session data if authentication is successful
        session()->set('login_user_id', $user['id']);
        session()->set('login_username', $user['username']);
        session()->set('login_user_group', $groups);


        if (!empty($user['employee_personnel_id'])) {
            session()->set('login_personnel_id', $user['employee_personnel_id']);
        }

        $this->save_log('Login', $data['username'], $user['id']);

        if ($user['is_change_password'] === "1") {
            session()->set('is_change_password', "1");

            $this->response->redirect_url  = 'authentication/change_password';

            return $this->response;
        } else {
            return $this->response;
        }
    }

    public function save_log($type, $username, $acl_user_id = NULL)
    {
        $is_saved = TRUE;

        $useable_account = $this->sys_lib->get_sys_reference('allowed_organization_configuration');
        $useable_account_list = json_decode(str_replace("'", '"', $useable_account), true);

        if (in_array($acl_user_id, $useable_account_list ?? [])) {
            $is_saved = FALSE;
        }

        if ($is_saved == TRUE) {
            $log_id =  uuid_v4();

            $data = [
                'id'          => $log_id,
                'types'       => $type,
                'username'    => $username,
                'acl_user_id' => $acl_user_id,
                'ip_address'  => $this->request->getIPAddress(),
                'user_data'   => $this->request->getUserAgent(),
                'month'       => date('m'),
                'year'        => date('Y'),
            ];

            $this->db->table('log.login')->insert($data);

            return $log_id;
        }
    }
}
