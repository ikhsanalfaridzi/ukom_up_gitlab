<?php

/**
 * Language Field Feature from System Module
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
use App\Models\System\LanguageFieldModel;
use App\Models\System\LanguageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Language_field extends KV_Controller
{
    protected $lang;
    protected $lang_field;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->lang            = new LanguageModel();
        $this->lang_field      = new LanguageFieldModel();
        $this->_title          = 'Language Field';
        $this->_uri_name       = 'system/language_field';
        $this->_uri_name_view  = 'system/language_field';
        $this->_menu_code      = 'system_language_field';
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
            $list = $this->lang_field->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'lang_name'    => $item->lang_name,
                    'field_name'   => $item->field_name,
                    'description'  => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->lang_field->count_all(),
                'recordsFiltered'   => $this->lang_field->count_filtered(),
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
        $result = $this->db->table('sysapp.language_field slf')
            ->select('slf.id, sl.lang_name , slf.field_name, slf.description')
            ->join('sysapp.languages sl', 'slf.language_id = sl.id', 'left')
            ->where('slf.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $lang = $this->lang->select('id, lang_name')->findAll();

        $this->_additional_display['lang_options'] = format_options($lang, 'lang_name');

        return parent::_form($this->lang_field, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->lang_field, $this->_uri_name);
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
        return parent::_update($this->lang_field, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->lang_field, NULL, ['id' => $id]);
    }
}
