<?php

/**
 * Group Feature from Instance Module
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
use App\Models\Instance\GroupModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Groups extends KV_Controller
{
    protected $group;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->group           = new GroupModel();
        $this->_title          = 'Jenis Instansi';
        $this->_uri_name       = 'instance/groups';
        $this->_uri_name_view  = 'instance/group';
        $this->_menu_code      = 'instance_group';
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
            $list = $this->group->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'group'            => $item->group,
                    'sequence_number'  => $item->sequence_number,
                    'sub_type'         => get_sub_type_value($item->sub_type),
                    'group_parent'     => $item->group_parent,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->group->count_all(),
                'recordsFiltered'   => $this->group->count_filtered(),
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
        $result = $this->db->table('instance.groups ig')
            ->select('ig.id, ig.group, ig.sequence_number, ig.sub_type, ig2.group as group_parent, ig.description, ig.is_access_province')
            ->join('instance.groups ig2', 'ig.parent_id = ig2.id', 'left')
            ->where('ig.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $groups = $this->group->select('id, group')->findAll();

        $this->_additional_display['group_options'] = format_options($groups, 'group');

        return parent::_form($this->group, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->group, $this->_uri_name);
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
        return parent::_update($this->group, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->group, NULL, ['id' => $id]);
    }
}
