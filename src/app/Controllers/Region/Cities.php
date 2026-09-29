<?php

/**
 * Cities Feature from Region Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-20
 */

namespace App\Controllers\Region;

use App\Controllers\KV_Controller;
use App\Models\Region\CityModel;
use App\Models\Region\ProvinceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Cities extends KV_Controller
{
    protected $city;
    protected $province;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->city            = new CityModel();
        $this->province        = new ProvinceModel();
        $this->_title          = 'Kota';
        $this->_uri_name       = 'region/cities';
        $this->_uri_name_view  = 'region/city';
        $this->_menu_code      = 'region_city';
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
            $list = $this->city->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'            => $item->id,
                    'province_name' => $item->province_name,
                    'city_name'     => $item->city_name,
                    'description'   => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->city->count_all(),
                'recordsFiltered'   => $this->city->count_filtered(),
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
        $result = $this->db->table('region.cities rc')
            ->select('rc.id, rc.city_name, rc.description, rc.created_at, rp.province_name')
            ->join('region.provinces rp', 'rc.province_id = rp.id', 'left')
            ->where('rc.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $provinces = $this->province->select('id, province_name')->findAll();

        $this->_additional_display['province_options'] = format_options($provinces, 'province_name');

        return parent::_form($this->city, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->city, $this->_uri_name);
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
        return parent::_update($this->city, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->city, NULL, ['id' => $id]);
    }

    public function getGet_list($province_id)
    {
        $data = $this->city->select('id, city_name')
            ->where('province_id', $province_id)
            ->findAll();

        $list = [];
        foreach ($data as $item) {
            $list[] = [
                'id'    => $item['id'],
                'text'  => $item['city_name'],
            ];
        }

        return $this->response->setJSON($list);
    }
}
