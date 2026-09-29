<?php

/**
 * Configuration Feature from System Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-03-16
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-16
 */

namespace App\Controllers\System;

use App\Controllers\KV_Controller;
use App\Models\System\ReferenceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Configuration extends KV_Controller
{
    protected $configuration;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->configuration   = new ReferenceModel();
        $this->_title          = 'App Configuration';
        $this->_uri_name       = 'system/configuration';
        $this->_uri_name_view  = 'system/configuration';
        $this->_menu_code      = 'system_configuration';
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
            $list = $this->configuration->get_datatables();
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
                'recordsTotal'      => $this->configuration->count_all(),
                'recordsFiltered'   => $this->configuration->count_filtered(),
                'data'              => $data
            ];

            return $this->response->setJSON($response);
        }

        return $this->response->setJSON(['error' => 'Invalid Request']);
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->configuration, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->configuration, $this->_uri_name);
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
        return parent::_update($this->configuration, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->configuration, NULL, ['id' => $id]);
    }
}
