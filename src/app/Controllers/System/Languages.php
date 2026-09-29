<?php

/**
 * Language Feature from System Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-20
 */

namespace App\Controllers\System;

use App\Controllers\KV_Controller;
use App\Models\System\LanguageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Languages extends KV_Controller
{
    protected $lang;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->lang            = new LanguageModel();
        $this->_title          = 'Language';
        $this->_uri_name       = 'system/languages';
        $this->_uri_name_view  = 'system/language';
        $this->_menu_code      = 'system_language';
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
            $list = $this->lang->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'lang_name'    => $item->lang_name,
                    'iso_code'     => $item->iso_code,
                    'description'  => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->lang->count_all(),
                'recordsFiltered'   => $this->lang->count_filtered(),
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
        $result = $this->db->table('sysapp.languages sl')
            ->select('sl.id, sl.lang_name, sl.iso_code, sl.description')
            ->where('sl.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->lang, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->lang, $this->_uri_name);
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
        return parent::_update($this->lang, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->lang, NULL, ['id' => $id]);
    }
}
