<?php

/**
 * Reference Feature from System Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-13
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-07
 */

namespace App\Controllers\System;

use App\Controllers\KV_Controller;
use App\Models\System\ReferenceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class References extends KV_Controller
{
    protected $reference;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->reference       = new ReferenceModel();
        $this->_title          = 'Referensi';
        $this->_uri_name       = 'system/references';
        $this->_uri_name_view  = 'system/reference';
        $this->_menu_code      = 'system_reference';
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
            $list = $this->reference->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'              => $item->id,
                    'description'     => $item->description,
                    'groups'          => $item->groups,
                    'notes'           => $item->notes,
                    'is_active'       => $item->is_active,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->reference->count_all(),
                'recordsFiltered'   => $this->reference->count_filtered(),
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
        $result = $this->db->table('sysapp.references sr')
            ->select('sr.id, sr.description, sr.groups, sr.notes, sr.is_active, sr.is_value_option, sr.is_note_option')
            ->where('sr.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $groups = $this->reference->select('groups')->distinct()->findAll();

        $this->_additional_display['group_options'] = format_options($groups, 'groups', 'groups');

        return parent::_form($this->reference, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->reference, $this->_uri_name);
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
        return parent::_update($this->reference, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->reference, NULL, ['id' => $id]);
    }
}
