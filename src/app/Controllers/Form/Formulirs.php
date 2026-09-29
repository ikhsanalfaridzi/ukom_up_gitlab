<?php

/**
 * Form Feature Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-18
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-18
 */

namespace App\Controllers\Form;

use App\Controllers\KV_Controller;
use App\Models\Form\FormulirModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Formulirs extends KV_Controller
{
    protected $form;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->form            = new FormulirModel();
        $this->_title          = 'Daftar Formulir';
        $this->_uri_name       = 'form/formulirs';
        $this->_uri_name_view  = 'form/formulir';
        $this->_menu_code      = 'form_formulir';
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
            $list = $this->form->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'          => $item->id,
                    'name'        => $item->name,
                    'description' => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->form->count_all(),
                'recordsFiltered'   => $this->form->count_filtered(),
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
        $result = $this->db->table('form.formulirs pi')
            ->select('pi.id, pi.name, pi.description')
            ->where('pi.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->form, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->form, $this->_uri_name);
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
        return parent::_update($this->form, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->form, NULL, ['id' => $id]);
    }
}
