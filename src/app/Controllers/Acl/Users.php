<?php

/**
 * User Feature from Acl Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-10
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-19
 */

namespace App\Controllers\Acl;

use App\Controllers\KV_Controller;
use App\Libraries\Acl\UserLib;
use App\Models\Acl\GroupModel;
use App\Models\Acl\UserGroupModel;
use App\Models\Acl\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class Users extends KV_Controller
{
    protected $user;
    protected $group;
    protected $user_group;
    protected $user_lib;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->user            = new UserModel();
        $this->user_group      = new UserGroupModel();
        $this->group           = new GroupModel();
        $this->user_lib        = new UserLib();
        $this->_title          = 'Daftar Pengguna';
        $this->_uri_name       = 'acl/users';
        $this->_uri_name_view  = 'acl/user';
        $this->_menu_code      = 'acl_user';
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return parent::_index();
    }

    public function postData()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        if ($this->request->isAJAX()) {
            $list = $this->user->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'                 => $item->id,
                    'full_name'          => $item->full_name ?? '-',
                    'username'           => kv_decrypt($item->username, getenv('APP_KEY')),
                    'email'              => $item->email ?? '-',
                    'role'               => $item->role ?? '-',
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->user->count_all(),
                'recordsFiltered'   => $this->user->count_filtered(),
                'data'              => $data
            ];

            return $this->response->setJSON($response);
        }

        return $this->response->setJSON(['error' => 'Invalid Request']);
    }

    public function getDetail($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return parent::_detail($id);
    }

    protected function get_detail($id)
    {
        $result = $this->db->table('acl.users au')
            ->select('au.id, au.username, au.is_active, au.is_change_password, au.description')
            ->where('au.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        if (!empty($id)) {
            $user_groups = $this->user_group->select('group_id')->where('user_id', $id)->findAll();

            $group_ids = array_column($user_groups, 'group_id');
            $this->_additional_display['group_ids'] = $group_ids;
        }

        $groups = $this->db->table('acl.groups ag')
            ->select('ag.id, ag.group_name')
            ->distinct()
            ->join('acl.user_groups aug', 'aug.group_id = ag.id', 'inner')
            ->join('identity.personal ipr', 'ipr.acl_user_id = aug.user_id', 'inner')
            ->where('ipr.employee_personnel_id IS NOT NULL')
            ->orderBy('ag.group_name', 'ASC')
            ->get()
            ->getResultArray();
            
        $this->_additional_display['group_options'] = format_multiple_options($groups, 'group_name');

        return parent::_form($this->user, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $validation = [
            'rules'   => $this->user->validationRules,
            'message' => $this->user->validationMessages,
        ];

        $this->message = 'Data berhasil disimpan';

        return parent::_execute('this', 'user_add_and_assign_group', $this->_uri_name, array(), $validation);
    }

    protected function user_add_and_assign_group()
    {
        $user_data = $this->_set_model_values($this->user, 'post');
        $user_data['id'] = $this->uuid;

        // Unique username validation
        try {
            $this->_validate_unique_username($this->user, $user_data['username']);
        } catch (Exception $e) {
            // If there is an unique error validation, return response error
            throw new Exception($e->getMessage());
        }

        // Save group data 
        if (!$this->user->save($user_data)) {
            throw new Exception('Gagal menyimpan data user.');
        }

        $group_ids = $this->request->getPost('group_id[]');

        // Assign user to group
        $this->user_lib->assign_user_groups($user_data['id'], $group_ids);

        return $user_data['id'];
    }

    public function postEdit($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $validation = [
            'rules'   => $this->user->validationRules,
            'message' => $this->user->validationMessages,
        ];

        if (empty($user_data['password']) && empty($user_data['confirm_password'])) {
            unset($validation['rules']['password']);
            unset($validation['rules']['confirm_password']);
            unset($validation['message']['password']);
            unset($validation['message']['confirm_password']);
        }

        $this->message = 'Data berhasil diperbarui';

        return parent::_execute('this', 'user_update_and_assign_group', $this->_uri_name, array($id), $validation);
    }

    protected function user_update_and_assign_group($id)
    {
        $user_data = $this->_set_model_values($this->user, 'post');

        if (empty($user_data['password']) && empty($user_data['confirm_password'])) {
            unset($user_data['password']);
            unset($user_data['confirm_password']);
        }

        // Unique username validation
        try {
            $this->_validate_unique_username($this->user, $user_data['username'], $id);
        } catch (Exception $e) {
            // If there is an unique error validation, return response error
            throw new Exception($e->getMessage());
        }

        // Save group data 
        if (!$this->user->update($id, $user_data)) {
            throw new Exception('Gagal memperbarui data user.');
        }

        $group_ids = $this->request->getPost('group_id[]');

        // Assign user to group
        $this->user_lib->assign_user_groups($id, $group_ids);

        return $id;
    }

    public function postDelete($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';
        return parent::_execute('this', 'delete_user', $this->_uri_name, ['id' => $id]);
    }

    protected function delete_user($id)
    {
        try {
            $this->user_group->where('user_id', $id)->delete();
            $this->user->delete($id);

            return $id;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
