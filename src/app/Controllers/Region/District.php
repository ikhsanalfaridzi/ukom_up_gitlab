<?php

/**
 * District Feature from Region Module
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
use App\Models\Region\CityModel;
use App\Models\Region\DistrictModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class District extends KV_Controller
{
    protected $district;
    protected $city;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->district        = new DistrictModel();
        $this->city            = new CityModel();
        $this->_title          = 'Kecamatan';
        $this->_uri_name       = 'region/district';
        $this->_uri_name_view  = 'region/district';
        $this->_menu_code      = 'region_district';
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
            $list = $this->district->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'            => $item->id,
                    'district_name' => $item->district_name,
                    'city_name'     => $item->city_name,
                    'province_name' => $item->province_name,
                    'description'   => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->district->count_all(),
                'recordsFiltered'   => $this->district->count_filtered(),
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
        $result = $this->db->table('region.district rd')
            ->select('rd.id, rd.district_name, rd.description, rc.city_name, rp.province_name')
            ->join('region.cities rc', 'rd.city_id = rc.id', 'inner')
            ->join('region.provinces rp', 'rc.province_id = rp.id', 'inner')
            ->where('rd.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $cities = $this->city->select('id, city_name')->findAll();

        $this->_additional_display['city_options'] = format_options($cities, 'city_name');

        return parent::_form($this->district, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->district, $this->_uri_name);
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
        return parent::_update($this->district, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->district, NULL, ['id' => $id]);
    }

    public function getGet_list($city_id)
    {
        $data = $this->district->select('id, district_name')
            ->where('city_id', $city_id)
            ->findAll();

        $list = [];
        foreach ($data as $item) {
            $list[] = [
                'id'    => $item['id'],
                'text'  => $item['district_name'],
            ];
        }

        return $this->response->setJSON($list);
    }
}
