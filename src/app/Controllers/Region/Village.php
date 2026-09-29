<?php

/**
 * Village Feature from Region Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-03-27
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-27
 */

namespace App\Controllers\Region;

use App\Controllers\KV_Controller;
use App\Models\Region\DistrictModel;
use App\Models\Region\VillageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Village extends KV_Controller
{
    protected $village;
    protected $district;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->village         = new VillageModel();
        $this->district        = new DistrictModel();
        $this->_title          = 'Kelurahan';
        $this->_uri_name       = 'region/village';
        $this->_uri_name_view  = 'region/village';
        $this->_menu_code      = 'region_village';
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
            $list = $this->village->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'            => $item->id,
                    'village_name'  => $item->village_name,
                    'postal_code'   => $item->postal_code,
                    'district_name' => $item->district_name,
                    'city_name'     => $item->city_name,
                    'province_name' => $item->province_name,
                    'description'   => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->village->count_all(),
                'recordsFiltered'   => $this->village->count_filtered(),
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
        $result = $this->db->table('region.village rv')
            ->select('rv.id, rv.village_name, rv.postal_code, rv.description, rd.district_name, rc.city_name, rp.province_name')
            ->join('region.district rd', 'rv.district_id = rd.id', 'left')
            ->join('region.cities rc', 'rd.city_id = rc.id', 'left')
            ->join('region.provinces rp', 'rc.province_id = rp.id', 'left')
            ->where('rv.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $districts = $this->district->select('id, district_name')->findAll();

        $this->_additional_display['district_options'] = format_options($districts, 'district_name');

        return parent::_form($this->village, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->village, $this->_uri_name);
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
        return parent::_update($this->village, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->village, NULL, ['id' => $id]);
    }

    public function getGet_list($district_id)
    {
        $data = $this->village->select('id, village_name, postal_code')
            ->where('district_id', $district_id)
            ->findAll();

        $list = [];
        foreach ($data as $item) {
            $list[] = [
                'id'    => $item['id'],
                'text'  => $item['village_name'] . " (" . $item['postal_code'] . ")",
            ];
        }

        return $this->response->setJSON($list);
    }
}
