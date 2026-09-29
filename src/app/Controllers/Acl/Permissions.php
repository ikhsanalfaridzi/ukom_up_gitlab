<?php

/**
 * Permission Feature from Acl Module
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
use App\Models\Acl\ActionModel;
use App\Models\Acl\MenuModel;
use App\Models\Acl\PermissionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Permissions extends KV_Controller
{
    protected $permission;
    protected $action;
    protected $menu;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->permission      = new PermissionModel();
        $this->action          = new ActionModel();
        $this->menu            = new MenuModel();
        $this->_title          = 'Permission';
        $this->_uri_name       = 'acl/permissions';
        $this->_uri_name_view  = 'acl/permission';
        $this->_menu_code      = 'acl_permission';
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
            $list = $this->permission->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'               => $item->id,
                    'permission_name'  => $item->action_code . ' ' . $item->menu_code,
                    'sequence_no'      => $item->sequence_no,
                    'description'      => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->permission->count_all(),
                'recordsFiltered'   => $this->permission->count_filtered(),
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
        $result = $this->db->table('acl.permissions ap')
            ->select('ap.id, ap.action_code, ap.menu_code, ap.sequence_no, ap.description')
            ->where('ap.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $actions = $this->action->select('action_code, action_name')->findAll();
        $menus = $this->menu->select('menu_code, menu_name')
            ->orderBy('sequence_no', 'ASC')
            ->findAll();

        $menu_options = [];
        $menu_options[''] = '';

        foreach ($menus as $item) {
            $menu_options += [
                $item['menu_code'] => $item['menu_name'] . " (" . $item['menu_code'] . ")"
            ];
        }

        $this->_additional_display['menu_options'] = $menu_options;
        $this->_additional_display['action_options'] = format_options($actions, 'action_name', 'action_code');

        return parent::_form($this->permission, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->permission, $this->_uri_name);
    }

    public function postEdit($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->message = 'Data berhasil diperbarui';
        return parent::_update($this->permission, $this->_uri_name, ['id' => $id]);
    }

    public function postDelete($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->message = 'Data berhasil dihapus';
        return parent::_delete($this->permission, NULL, ['id' => $id]);
    }
}
