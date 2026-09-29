<?php

/**
 * Position Structure Feature from Instance Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-12
 */

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\OrganizationStructureModel;
use App\Models\Instance\PositionModel;
use App\Models\Instance\PositionStructureModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Position_structures extends KV_Controller
{
    protected $position;
    protected $position_structure;
    protected $organization_structure;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->position               = new PositionModel();
        $this->position_structure     = new PositionStructureModel();
        $this->organization_structure = new OrganizationStructureModel();
        $this->_title                 = 'Struktur Jabatan';
        $this->_uri_name              = 'instance/position_structures';
        $this->_uri_name_view         = 'instance/position_structure';
        $this->_menu_code             = 'instance_position_structure';
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
            $list = $this->position_structure->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'position'     => $item->position,
                    'organization' => $item->organization,
                    'is_active'    => $item->is_active,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->position_structure->count_all(),
                'recordsFiltered'   => $this->position_structure->count_filtered(),
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
        $result = $this->db->table('instance.position_structures ips')
            ->select('ips.id, ip.position, io.organization, ips.is_active, ips.created_at')
            ->join('instance.positions ip', 'ips.position_id = ip.id', 'inner')
            ->join('instance.organization_structures ios', 'ips.organization_structure_id = ios.id', 'inner')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'inner')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"])
            ->where('ips.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $positions = $this->position->select('id, position')->findAll();
        $organization_structures = $this->organization_structure->select('id, structure_name')->findAll();

        $this->_additional_display['position_options'] = format_options($positions, 'position');
        $this->_additional_display['organization_structure_options'] = format_options($organization_structures, 'structure_name');

        return parent::_form($this->position_structure, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->position_structure, $this->_uri_name);
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
        return parent::_update($this->position_structure, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->position_structure, NULL, ['id' => $id]);
    }
}
