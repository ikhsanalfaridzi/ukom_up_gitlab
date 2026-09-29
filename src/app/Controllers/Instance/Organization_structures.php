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
 * @updated 	: 2025-04-12
 */

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\OrganizationModel;
use App\Models\Instance\OrganizationStructureModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Organization_structures extends KV_Controller
{
    protected $organization_structure;
    protected $organization;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->organization_structure = new OrganizationStructureModel();
        $this->organization           = new OrganizationModel();
        $this->_title                 = 'Struktur Organisasi';
        $this->_uri_name              = 'instance/organization_structures';
        $this->_uri_name_view         = 'instance/organization_structure';
        $this->_menu_code             = 'instance_organization_structure';
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
            $list = $this->organization_structure->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'             => $item->id,
                    'structure_code' => $item->structure_code,
                    'archive_code'    => $item->archive_code,
                    'structure_name' => $item->structure_name,
                    'organization'   => $item->organization,
                    'is_active'      => $item->is_active,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->organization_structure->count_all(),
                'recordsFiltered'   => $this->organization_structure->count_filtered(),
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
        $result = $this->db->table('instance.organization_structures ios')
            ->select('ios.id, ios.structure_code, ios.archive_code, ios.structure_name, ios.is_active, io.organization, ios.created_at')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'inner')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"])
            ->where('ios.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $organizations = $this->organization
            ->select('io.id, io.organization')
            ->from('instance.organizations io')
            ->join('instance.groups ig', 'ig.id = io.group_id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"])
            ->findAll();

        $this->_additional_display['organization_options'] = format_options($organizations, 'organization');

        return parent::_form($this->organization_structure, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->organization_structure, $this->_uri_name);
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
        return parent::_update($this->organization_structure, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->organization_structure, NULL, ['id' => $id]);
    }
}
