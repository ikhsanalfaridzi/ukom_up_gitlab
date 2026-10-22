<?php

/**
 * Menu Feature from Acl Module
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
use App\Models\Acl\IconModel;
use App\Models\Acl\MenuModel;
use App\Models\System\ModuleModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Menu extends KV_Controller
{
    protected $db;
    protected $menu;
    protected $module;
    protected $icon;

    public function __construct()
    {
        parent::__construct(TRUE);
        $this->menu            = new MenuModel();
        $this->module          = new ModuleModel();
        $this->icon            = new IconModel();
        $this->_title          = 'Menu';
        $this->_uri_name       = 'acl/menu';
        $this->_uri_name_view  = 'acl/menu';
        $this->_menu_code      = 'acl_menu';
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
            $list = $this->menu->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'          => $item->id,
                    'menu_code'   => $item->menu_code,
                    'menu_name'   => $item->menu_name,
                    'parent_name' => $item->parent_name,
                    'module_name' => $item->module_name,
                    'url'         => $item->url,
                    'icon_name'   => $item->icon_name,
                    'sequence_no' => $item->sequence_no,
                    'is_display'  => $item->is_display,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->menu->count_all(),
                'recordsFiltered'   => $this->menu->count_filtered(),
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
        $result = $this->db->table('acl.menu am')
            ->select('am.id, am.menu_code, am.menu_name, am.description, am2.menu_name as parent_name, sm.module_name, am.url, am.sequence_no, am.icon_name, am.is_display')
            ->join('acl.menu am2', 'am2.parent_id = am.id', 'left')
            ->join('sysapp.module sm', 'am.module_id = sm.id', 'left')
            ->where('am.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $parents = $this->menu->select('id, menu_name, menu_code')->findAll();
        $module = $this->module->select('id, module_name')->findAll();
        $icon = $this->icon->select('icon_name')->findAll();


        $parent_options = [];
        $parent_options[''] = '';

        foreach ($parents as $item) {
            $parent_options += [
                $item['id'] => $item['menu_name'] . " (" . $item['menu_code'] . ")"
            ];
        }

        $this->_additional_display['parent_options'] = $parent_options;

        $this->_additional_display['module_options'] = format_options($module, 'module_name');
        $this->_additional_display['icon_options'] = format_options($icon, 'icon_name', 'icon_name');

        return parent::_form($this->menu, ['id' => $id]);
    }

    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->menu, $this->_uri_name);
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
        return parent::_update($this->menu, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->menu, NULL, ['id' => $id]);
    }
}
