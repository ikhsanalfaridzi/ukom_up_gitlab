<?php

/**
 * Position Group Feature from Instance Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-14
 */

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\PositionGroupModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Position_groups extends KV_Controller
{
    protected $position_group;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->position_group  = new PositionGroupModel();
        $this->_title          = 'Kelompok Jabatan';
        $this->_uri_name       = 'instance/position_groups';
        $this->_uri_name_view  = 'instance/position_group';
        $this->_menu_code      = 'instance_position_group';
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
            $list = $this->position_group->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'name'         => $item->name,
                    'description'  => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->position_group->count_all(),
                'recordsFiltered'   => $this->position_group->count_filtered(),
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
        $result = $this->db->table('instance.position_groups ipg')
            ->select('ipg.id, ipg.name, ipg.description')
            ->where('ipg.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->position_group, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->position_group, $this->_uri_name);
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
        return parent::_update($this->position_group, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->position_group, NULL, ['id' => $id]);
    }
}
