<?php

/**
 * Position Group Feature from Instance Module
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

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\FieldClassificationModel;
use App\Models\Instance\PositionClassificationModel;
use App\Models\Instance\PositionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Position_classifications extends KV_Controller
{
    protected $position_classification;
    protected $position;
    protected $field_classification;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->position_classification = new PositionClassificationModel();
        $this->position                = new PositionModel();
        $this->field_classification    = new FieldClassificationModel();
        $this->_title                  = 'Daftar Jabatan';
        $this->_uri_name               = 'instance/position_classifications';
        $this->_uri_name_view          = 'instance/position_classification';
        $this->_menu_code              = 'instance_position_classification';
    }

    public function getList($classification_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        if (empty($classification_id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $classification = $this->field_classification->select('id, classification, description')->where('id', $classification_id)->first();

        $this->_additional_display['classification'] = $classification;

        return parent::_index();
    }

    public function postData($classification_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        if ($this->request->isAJAX()) {
            $list = $this->position_classification->get_datatables($classification_id);
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'                => $item->id,
                    'classification_id' => $classification_id,
                    'position_name'     => $item->position_name,
                    'priority'          => $item->priority,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->position_classification->count_all($classification_id),
                'recordsFiltered'   => $this->position_classification->count_filtered($classification_id),
                'data'              => $data
            ];

            return $this->response->setJSON($response);
        }

        return $this->response->setJSON(['error' => 'Invalid Request']);
    }

    public function getDetail($classification_id, $id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->_additional_display['classification_id'] = $classification_id;

        return parent::_detail($id, ['classification_id' => $classification_id]);
    }

    protected function get_detail($id, $params = [])
    {
        $classification_id = isset($params['classification_id']) ? $params['classification_id'] : null;

        if (empty($classification_id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $result = $this->db->table('instance.position_classifications ipc')
            ->select('ipc.id, ip.position as position_name, ipc.priority, ipc.created_at')
            ->join('instance.positions ip', 'ipc.position_id = ip.id', 'inner')
            ->where('ipc.classification_id', $classification_id)
            ->where('ipc.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getForm($classification_id, $id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') || !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $positions = $this->position->select('id, position')->findAll();

        $classification = $this->position_classification->where('classification_id', $classification_id)->where('id', $id)->first();

        $this->_additional_display['position_options'] = format_options($positions, 'position');
        $this->_additional_display['classification_id'] = $classification_id;
        $this->_additional_display['classification'] = $classification;

        return parent::_form($this->position_classification, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $redirect_url = $this->_uri_name . '/list/';

        $this->message = 'Data berhasil disimpan';
        return parent::_insert($this->position_classification, $redirect_url, ['include_parent_id' => TRUE, 'parent_id' => 'classification_id']);
    }

    public function postEdit($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $redirect_url = $this->_uri_name . '/list/';

        $this->message = 'Data berhasil diperbarui';
        return parent::_update($this->position_classification, $redirect_url, ['id' => $id, 'include_parent_id' => TRUE, 'parent_id' => 'classification_id']);
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
        return parent::_delete($this->position_classification, NULL, ['id' => $id]);
    }
}
