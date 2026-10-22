<?php

/**
 * Icon Feature from Acl Module
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
use CodeIgniter\Exceptions\PageNotFoundException;

class Icons extends KV_Controller
{
    protected $icon;

    public function __construct()
    {
        parent::__construct(TRUE);
        $this->icon            = new IconModel();
        $this->_title          = 'Icon';
        $this->_uri_name       = 'acl/icons';
        $this->_uri_name_view  = 'acl/icon';
        $this->_menu_code      = 'acl_icon';
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
            $list = $this->icon->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'         => $item->id,
                    'icon_name'  => $item->icon_name,
                    'groups'     => $item->groups,
                    'pack_name'  => $item->pack_name,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->icon->count_all(),
                'recordsFiltered'   => $this->icon->count_filtered(),
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
        $result = $this->db->table('acl.icons ai')
            ->select('ai.id, ai.icon_name, ai.groups, ai.pack_name')
            ->where('ai.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->icon, ['id' => $id]);
    }

    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->icon, $this->_uri_name);
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
        return parent::_update($this->icon, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->icon, NULL, ['id' => $id]);
    }
}
