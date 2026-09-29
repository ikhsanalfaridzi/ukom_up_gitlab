<?php

/**
 * Provinces Feature from Region Module
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
use App\Models\Region\ProvinceModel;
use App\Models\Region\TimezoneModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class Provinces extends KV_Controller
{
    protected $province;
    protected $timezone;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db              = Database::connect();
        $this->province        = new ProvinceModel();
        $this->timezone        = new TimezoneModel();
        $this->_title          = 'Provinsi';
        $this->_uri_name       = 'region/provinces';
        $this->_uri_name_view  = 'region/province';
        $this->_menu_code      = 'region_province';
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
            $list = $this->province->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'             => $item->id,
                    'province_name'  => $item->province_name,
                    'time_zone'      => $item->time_zone,
                    'iso_code'       => $item->iso_code,
                    'description'    => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->province->count_all(),
                'recordsFiltered'   => $this->province->count_filtered(),
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
        $result = $this->db->table('region.provinces rp')
            ->select('rp.id, rp.province_name, rp.time_zone, rp.iso_code, rp.description')
            ->where('rp.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $timezone = $this->timezone->select('time_zone')->findAll();

        $this->_additional_display['timezone_options'] = format_options($timezone, 'time_zone', 'time_zone');

        return parent::_form($this->province, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->province, $this->_uri_name);
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
        return parent::_update($this->province, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->province, NULL, ['id' => $id]);
    }
}
