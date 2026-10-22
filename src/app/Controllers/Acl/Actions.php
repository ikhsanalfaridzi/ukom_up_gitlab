<?php

/**
 * Action Feature from Acl Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-19
 */

namespace App\Controllers\Acl;

use App\Controllers\KV_Controller;
use App\Models\Acl\ActionModel;
use App\Models\Reference\IndicatorModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Actions extends KV_Controller
{
    protected $action;
    protected $indicator;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->action          = new ActionModel();
        $this->indicator       = new IndicatorModel();
        $this->_title          = 'Aksi';
        $this->_uri_name       = 'acl/actions';
        $this->_uri_name_view  = 'acl/action';
        $this->_menu_code      = 'acl_action';
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
            $list = $this->action->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'           => $item->id,
                    'action_code'  => $item->action_code,
                    'action_name'  => $item->action_name,
                    'indicator'    => $item->indicator,
                    'description'  => $item->description,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->action->count_all(),
                'recordsFiltered'   => $this->action->count_filtered(),
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
        $result = $this->db->table('acl.actions aa')
            ->select('aa.id, aa.action_name, aa.action_code, aa.indicator, aa.description')
            ->where('aa.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $indicators = $this->indicator->select('name')->findAll();

        $this->_additional_display['indicator_options'] = format_options($indicators, 'name', 'name');

        return parent::_form($this->action, ['id' => $id]);
    }

    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->action, $this->_uri_name);
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
        return parent::_update($this->action, $this->_uri_name, ['id' => $id]);
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
        return parent::_delete($this->action, NULL, ['id' => $id]);
    }
}
