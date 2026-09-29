<?php

/**
 * Organization Structure Feature from Instance Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-13
 */

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\OrganizationModel;
use App\Models\Instance\PositionModel;
use App\Models\Instance\StructureModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Structures extends KV_Controller
{
    protected $structure;
    protected $position;
    protected $organization;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->structure       = new StructureModel();
        $this->position        = new PositionModel();
        $this->organization    = new OrganizationModel();
        $this->_title          = 'Struktur Organisasi';
        $this->_uri_name       = 'instance/structures';
        $this->_uri_name_view  = 'instance/structure';
        $this->_menu_code      = 'instance_structure';
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
            $list = $this->structure->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'                 => $item->id,
                    'structure_code'     => $item->structure_code,
                    'position_name'      => $item->position_name,
                    'organization_name'  => $item->organization_name,
                    'is_active'          => $item->is_active,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->structure->count_all(),
                'recordsFiltered'   => $this->structure->count_filtered(),
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
        $result = $this->db->table('instance.structures is')
            ->select('is.id, is.structure_code, is.is_active, ip.position as position_name, io.organization as organization_name')
            ->join('instance.positions ip', 'is.position_id = ip.id', 'left')
            ->join('instance.organizations io', 'is.organization_id = io.id', 'left')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'left')
            ->where('ig.sub_type !=', '0')
            ->where('is.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $position = $this->position->select('id, position')->findAll();
        $organizations = $this->organization
            ->select('io.id, io.organization')
            ->from('instance.organizations io')
            ->join('instance.groups ig', 'ig.id = io.group_id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"])
            ->findAll();

        $this->_additional_display['position_options'] = format_options($position, 'position');
        $this->_additional_display['organization_options'] = format_options($organizations, 'organization');

        return parent::_form($this->structure, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->structure, $this->_uri_name);
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
        return parent::_update($this->structure, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->structure, NULL, ['id' => $id]);
    }
}
