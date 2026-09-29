<?php

/**
 * Grades Feature from Employee Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-19
 */

namespace App\Controllers\Employee;

use App\Controllers\KV_Controller;
use App\Models\Employee\GradeModel;
use App\Models\Instance\PositionGroupModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Grades extends KV_Controller
{
    protected $grade;
    protected $position_group;

    public function __construct()
    {
        parent::__construct(TRUE);
        $this->grade           = new GradeModel();
        $this->position_group  = new PositionGroupModel();
        $this->_title          = 'Golongan';
        $this->_uri_name       = 'employee/grades';
        $this->_uri_name_view  = 'employee/grade';
        $this->_menu_code      = 'employee_grade';
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
            $list = $this->grade->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'             => $item->id,
                    'code'           => $item->code,
                    'grade'          => $item->grade,
                    'level'          => $item->level,
                    'classification' => $item->classification,
                    'group_name'     => $item->group_name,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->grade->count_all(),
                'recordsFiltered'   => $this->grade->count_filtered(),
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
        $result = $this->db->table('employee.grades eg')
            ->select('eg.id, eg.code, eg.grade, eg.level, eg.classification, ipg.name as group_name')
            ->join('instance.position_groups ipg', 'eg.position_group_id = ipg.id', 'left')
            ->where('eg.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $position_groups = $this->position_group->select('id, name')->findAll();
        $classifications = $this->grade->select('classification')->distinct()->findAll();

        $this->_additional_display['position_group_options'] = format_options($position_groups, 'name');
        $this->_additional_display['classification_options'] = format_options($classifications, 'classification', 'classification');

        return parent::_form($this->grade, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->grade, $this->_uri_name);
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
        return parent::_update($this->grade, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->grade, NULL, ['id' => $id]);
    }
}
