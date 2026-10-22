<?php

/**
 * Group Feature from Acl Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-19
 */

namespace App\Controllers\Acl;

use App\Controllers\KV_Controller;
use App\Libraries\Acl\AclLib;
use App\Models\Acl\ActionModel;
use App\Models\Acl\GroupModel;
use App\Models\Acl\GroupPermissionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class Groups extends KV_Controller
{
    protected $db;
    protected $group;
    protected $group_permission;
    protected $action;
    protected $acl_lib;

    public function __construct()
    {
        parent::__construct(TRUE);
        $this->group            = new GroupModel();
        $this->group_permission = new GroupPermissionModel();
        $this->action           = new ActionModel();
        $this->acl_lib          = new AclLib();
        $this->_title           = 'Grup';
        $this->_uri_name        = 'acl/groups';
        $this->_uri_name_view   = 'acl/group';
        $this->_menu_code       = 'acl_group';
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
            $list = $this->group->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'              => $item->id,
                    'group_name'      => $item->group_name,
                    'description'     => $item->description,
                    'is_publish'      => $item->is_publish,
                    'is_sub_publish'  => $item->is_sub_publish,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->group->count_all(),
                'recordsFiltered'   => $this->group->count_filtered(),
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

        $actions = $this->action->select('action_name, description')->orderBy('created_at', 'ASC')->findAll();

        $group_permissions = $this->db->table('acl.group_permissions agp')
            ->select('agp.id, agp.group_id, agp.permission_id')
            ->where('group_id', $id)
            ->get()
            ->getResultArray();

        $permission_list = $this->acl_lib->get_menu_module_permissions();

        $this->_additional_display['actions'] = $actions;
        $this->_additional_display['permission_list'] = $permission_list;
        $this->_additional_display['group_permissions'] = $group_permissions;

        return parent::_detail($id);
    }

    protected function get_detail($id)
    {
        $result = $this->db->table('acl.groups ag')
            ->select('ag.id, ag.group_name, ag.description, ag.is_publish, ag.is_sub_publish')
            ->where('ag.id', $id)
            ->get()
            ->getRowArray();
            
        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $actions = $this->action->select('action_name, description')->orderBy('created_at', 'ASC')->findAll();

        if (!empty($id)) {
            $group_permissions = $this->db->table('acl.group_permissions agp')
                ->select('agp.id, agp.group_id, agp.permission_id')
                ->where('group_id', $id)
                ->get()
                ->getResultArray();

            $this->_additional_display['group_permissions'] = $group_permissions;
        }

        $permission_list = $this->acl_lib->get_menu_module_permissions();

        $this->_additional_display['actions'] = $actions;
        $this->_additional_display['permission_list'] = $permission_list;

        return parent::_form($this->group, ['id' => $id]);
    }

    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $validation = [
            'rules'   => $this->group->validationRules,
            'message' => $this->group->validationMessages,
        ];

        $this->message = 'Data berhasil disimpan';

        return parent::_execute('this', 'group_add_and_assign_group_permissions', $this->_uri_name, array(), $validation);
    }

    protected function group_add_and_assign_group_permissions()
    {
        $group_data = $this->_set_model_values($this->group, 'post');
        $group_data['id'] = $this->uuid;

        $this->_validate_unique_fields($this->group, $this->group->uniqueFields, $group_data);

        // Save group data 
        if (!$this->group->save($group_data)) {
            throw new Exception('Gagal menyimpan data grup.');
        }

        $permissions = $this->request->getPost('permissions[]');

        // Assign permission to group
        $this->acl_lib->assign_group_permissions($group_data['id'], $permissions);

        return $group_data['id'];
    }

    public function postEdit($id)
    {

        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $validation = [
            'rules'   => $this->group->validationRules,
            'message' => $this->group->validationMessages,
        ];

        $this->message = 'Data berhasil diperbarui';

        return parent::_execute('this', 'group_update_and_assign_group_permissions', $this->_uri_name, array($id), $validation);
    }

    protected function group_update_and_assign_group_permissions($id)
    {
        $group_data = $this->_set_model_values($this->group, 'post');
        unset($group_data['id']);

        $this->_validate_unique_fields($this->group, $this->group->uniqueFields, $group_data, $id);

        // Save group data 
        if (!$this->group->update($id, $group_data)) {
            throw new Exception('Gagal memperbarui data grup.');
        }

        $permissions = $this->request->getPost('permissions[]');

        // Assign permission to group
        $this->acl_lib->assign_group_permissions($id, $permissions);

        return $id;
    }

    public function postDelete($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';
        return parent::_execute('this', 'delete_group', $this->_uri_name, ['id' => $id]);
    }

    public function postToggle($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid Request']);
        }

        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        if (!in_array($field, ['is_publish', 'is_sub_publish'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Field tidak valid']);
        }

        if (!in_array($value, ['0', '1', 0, 1], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nilai tidak valid']);
        }

        if (!$this->group->skipValidation(true)->update($id, [$field => $value])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui status']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Status berhasil diperbarui']);
    }

    protected function delete_group($id)
    {
        $this->group_permission->where('group_id', $id)->delete();
        $this->db->table('acl.user_groups')->where('group_id', $id)->delete();
        $this->group->delete($id);

        return $id;
    }
}
