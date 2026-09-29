<?php

/**
 * Position Feature from Instance Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-04
 */

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Employee\GradeModel;
use App\Models\Instance\ClassModel;
use App\Models\Instance\PositionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Positions extends KV_Controller
{
    protected $grade;
    protected $class;
    protected $position;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->grade           = new GradeModel();
        $this->class           = new ClassModel();
        $this->position        = new PositionModel();
        $this->_title          = 'Jabatan';
        $this->_uri_name       = 'instance/positions';
        $this->_uri_name_view  = 'instance/position';
        $this->_menu_code      = 'instance_position';
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
            $list = $this->position->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'          => $item->id,
                    'position'    => $item->position,
                    'grade_name'  => $item->grade_name,
                    'class_name'  => $item->class_name,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->position->count_all(),
                'recordsFiltered'   => $this->position->count_filtered(),
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
        $result = $this->db->table('instance.positions ip')
            ->select('ip.id, ip.position, eg.grade as grade_name, ic.class as class_name')
            ->join('employee.grades eg', 'ip.grade_id = eg.id', 'inner')
            ->join('instance.classes ic', 'ip.class_id = ic.id', 'left')
            ->where('ip.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $grades = $this->grade->select('id, grade')
            ->orderBy('code', 'ASC')
            ->findAll();

        $classes = $this->class->select('id, class')
            ->orderBy('class', 'ASC')
            ->findAll();

        $this->_additional_display['grade_options'] = format_options($grades, 'grade');
        $this->_additional_display['class_options'] = format_options($classes, 'class');

        return parent::_form($this->position, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->position, $this->_uri_name);
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
        return parent::_update($this->position, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->position, NULL, ['id' => $id]);
    }
}
