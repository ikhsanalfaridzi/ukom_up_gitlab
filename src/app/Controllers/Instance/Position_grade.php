<?php

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\PositionGradeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Position_grade extends KV_Controller
{
    protected $position_grade;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->position_grade  = new PositionGradeModel();
        $this->_title          = 'Tingkatan';
        $this->_uri_name       = 'instance/position_grade';
        $this->_uri_name_view  = 'instance/position_grade';
        $this->_menu_code      = 'instance_position_grade';
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
            $list = $this->position_grade->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'code'         => $item->code,
                    'name'         => $item->name,
                    'group_name'   => $item->group_name,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->position_grade->count_all(),
                'recordsFiltered'   => $this->position_grade->count_filtered(),
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
        $result = $this->db->table('employee.grades g')
            ->select('g.id, g.code, g.grade AS name, ipg.name AS group_name')
            ->join('instance.position_groups AS ipg', 'g.position_group_id = ipg.id', 'left')
            ->where('g.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        return parent::_form($this->position_grade, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->position_grade, $this->_uri_name);
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
        return parent::_update($this->position_grade, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->position_grade, NULL, ['id' => $id]);
    }
}
