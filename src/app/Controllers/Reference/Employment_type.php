<?php

/**
 * Employment Type Feature from Reference Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-28
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-08
 */

namespace App\Controllers\Reference;

use App\Controllers\KV_Controller;
use App\Models\Reference\EmploymentTypeModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Employment_type extends KV_Controller
{
    protected $employment_type;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->employment_type = new EmploymentTypeModel();
        $this->_title          = 'Tipe Pegawai';
        $this->_uri_name       = 'reference/employment_type';
        $this->_uri_name_view  = 'reference/employment_type';
        $this->_menu_code      = 'reference_employment_type';
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
            $list = $this->employment_type->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'name'         => $item->name,
                    'is_active'    => $item->is_active,
                    'description'  => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->employment_type->count_all(),
                'recordsFiltered'   => $this->employment_type->count_filtered(),
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
        $result = $this->db->table('public.employment_type pet')
            ->select('pet.id, pet.name, pet.is_active, pet.description')
            ->where('pet.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->employment_type, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->employment_type, $this->_uri_name);
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
        return parent::_update($this->employment_type, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->employment_type, NULL, ['id' => $id]);
    }
}
