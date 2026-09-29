<?php

/**
 * Other Organization Feature from Instance Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-02
 */

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\GroupModel;
use App\Models\Instance\OrganizationContactModel;
use App\Models\Instance\OtherOrganizationModel;
use App\Models\Region\ProvinceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class Other_organizations extends KV_Controller
{
    protected $province;
    protected $group;
    protected $organization;
    protected $organization_contact;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->province             = new ProvinceModel();
        $this->group                = new GroupModel();
        $this->organization         = new OtherOrganizationModel();
        $this->organization_contact = new OrganizationContactModel();
        $this->_title               = 'Unit Kerja Instansi Lainnya';
        $this->_uri_name            = 'instance/other_organizations';
        $this->_uri_name_view       = 'instance/other_organization';
        $this->_menu_code           = 'instance_other_organization';
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
            $list = $this->organization->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'                 => $item->id,
                    'organization_code'  => $item->organization_code,
                    'organization'       => $item->organization,
                    'parent_name'        => $item->parent_name,
                    'group_name'         => $item->group_name,
                    'province_name'      => $item->province_name,
                    'city_name'          => $item->city_name,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->organization->count_all(),
                'recordsFiltered'   => $this->organization->count_filtered(),
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
        $result = $this->db->table('instance.organizations io')
            ->select('io.id, io.organization_code, io.organization, io2.organization as parent_name, io.is_active, io.is_demo, ig.group as group_name, rp.province_name, rc.city_name, ioc.email, ioc.phone_number, ioc.fax_number, ioc.address, ioc.latitude, ioc.longitude, ioc.vector')
            ->join('instance.groups ig', 'ig.id = io.group_id', 'inner')
            ->join('instance.organization_contacts ioc', 'ioc.organization_id = io.id', 'left')
            ->join('region.provinces rp', 'rp.id = io.province_id', 'left')
            ->join('region.cities rc', 'rc.id = io.city_id', 'left')
            ->join('instance.organizations io2', 'io2.id = io.parent_id', 'left')
            ->where('io.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $group = $this->group->select('id, group')->findAll();
        $organization = $this->organization->select('id, organization')->findAll();
        $province = $this->province->select('id, province_name')->findAll();
        $contact = $this->organization_contact->where('organization_id', $id)->first();

        $this->_additional_display['group_options'] = format_options($group, 'group');
        $this->_additional_display['organization_options'] = format_options($organization, 'organization');
        $this->_additional_display['province_options'] = format_options($province, 'province_name');
        $this->_additional_display['contact'] = $contact;

        return parent::_form($this->organization, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $validation = [
            'rules'   => $this->organization->validationRules,
            'message' => $this->organization->validationMessages,
        ];

        $this->message = 'Data berhasil disimpan';

        return parent::_execute('this', 'insert_organization', $this->_uri_name, array(), $validation);
    }

    protected function insert_organization()
    {
        $organization_data = $this->_set_model_values($this->organization, 'post');
        $organization_data['id'] = $this->uuid;

        $this->_validate_unique_fields($this->organization, $this->organization->uniqueFields, $organization_data);

        // Save organization data 
        if (!$this->organization->save($organization_data)) {
            throw new Exception('Gagal menyimpan data unit kerja.');
        }

        $contact_data = $this->_set_model_values($this->organization_contact, 'post');
        $contact_data['organization_id'] = $organization_data['id'];
        $contact_data['id'] = $this->uuid;

        // Save organisasi contact 
        if (!$this->organization_contact->save($contact_data)) {
            throw new Exception('Gagal menyimpan data kontak unit kerja.');
        }

        return $organization_data['id'];
    }

    public function postEdit($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $validation = [
            'rules'   => $this->organization->validationRules,
            'message' => $this->organization->validationMessages,
        ];

        $this->message = 'Data berhasil diperbarui';

        return parent::_execute('this', 'update_organization', $this->_uri_name, ['id' => $id], $validation);
    }

    protected function update_organization($id)
    {
        $organization_data = $this->_set_model_values($this->organization, 'post');
        unset($organization_data['id']);

        $this->_validate_unique_fields($this->organization, $this->organization->uniqueFields, $organization_data, $id);

        // Save organization data 
        if (!$this->organization->update($id, $organization_data)) {
            throw new Exception('Gagal menyimpan perubahan data unit kerja.');
        }

        $contact = $this->organization_contact->select('id')->where('organization_id', $id)->first();

        $contact_data = $this->_set_model_values($this->organization_contact, 'post');
        $contact_data['organization_id'] = $id;
        unset($contact_data['id']);

        // Save organisasi contact 
        if (!$this->organization_contact->update($contact['id'], $contact_data)) {
            throw new Exception('Gagal menyimpan perubahan data kontak unit kerja.');
        }

        return $id;
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

        return parent::_execute('this', 'delete_organization', $this->_uri_name, ['id' => $id]);
    }

    protected function delete_organization($id)
    {
        try {
            $this->organization_contact->where('organization_id', $id)->delete();
            $this->organization->delete($id);

            return $id;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
