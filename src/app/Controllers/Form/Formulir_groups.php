<?php

/**
 * Group Formulir Feature from Form Module
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

namespace App\Controllers\Form;

use App\Controllers\KV_Controller;
use App\Models\Form\FormulirGroupModel;
use App\Models\Form\FormulirModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Formulir_groups extends KV_Controller
{
    protected $formulir_group;
    protected $formulir;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->formulir_group  = new FormulirGroupModel();
        $this->formulir        = new FormulirModel();
        $this->_title          = 'Formulir Group';
        $this->_uri_name       = 'form/formulir_groups';
        $this->_uri_name_view  = 'form/formulir_group';
        $this->_menu_code      = 'form_formulir_group';
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

        if ($this->request->isAJAX() || $this->request->getVar('draw')) {
            $list = $this->formulir_group->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'            => $item->id,
                    'formulir_name' => $item->formulir_name,
                    'formulir_group_name' => $item->formulir_group_name,
                    'description'   => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->formulir_group->count_all(),
                'recordsFiltered'   => $this->formulir_group->count_filtered(),
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
        $result = $this->db->table('form.formulir_groups fg')
            ->select('fg.id, fg.formulir_group_name, fg.description, fg.created_at, pi.name as formulir_name')
            ->join('form.formulirs pi', 'fg.formulir_id = pi.id', 'left')
            ->where('fg.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $formulirs = $this->formulir->select('id, name')->findAll();

        $this->_additional_display['formulir_options'] = format_options($formulirs, 'name');

        return parent::_form($this->formulir_group, ['id' => $id]);
    }

    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->formulir_group, $this->_uri_name);
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
        return parent::_update($this->formulir_group, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->formulir_group, NULL, ['id' => $id]);
    }

    public function getGet_list($formulir_id)
    {
        $data = $this->formulir_group->select('id, formulir_group_name')
            ->where('formulir_id', $formulir_id)
            ->findAll();

        $list = [];
        foreach ($data as $item) {
            $list[] = [
                'id'    => $item['id'],
                'text'  => $item['formulir_group_name'],
            ];
        }

        return $this->response->setJSON($list);
    }
}
