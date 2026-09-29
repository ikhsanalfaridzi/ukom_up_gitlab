<?php

/**
 * Position Class Feature from Instance Module
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
use App\Models\Instance\ClassModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Classes extends KV_Controller
{
    protected $class;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->class           = new ClassModel();
        $this->_title          = 'Kelas Jabatan';
        $this->_uri_name       = 'instance/classes';
        $this->_uri_name_view  = 'instance/class';
        $this->_menu_code      = 'instance_class';
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
            $list = $this->class->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'                    => $item->id,
                    'class'                 => $item->class,
                    'performance_allowance' => format_currency($item->performance_allowance),
                    'description'           => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->class->count_all(),
                'recordsFiltered'   => $this->class->count_filtered(),
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
        $result = $this->db->table('instance.classes ic')
            ->select('ic.id, ic.class, ic.performance_allowance, ic.description')
            ->where('ic.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->class, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->class, $this->_uri_name);
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
        return parent::_update($this->class, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->class, NULL, ['id' => $id]);
    }
}
