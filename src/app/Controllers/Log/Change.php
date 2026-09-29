<?php

/**
 * Change Log Feature from Log Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-05-06
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-05-06
 */

namespace App\Controllers\Log;

use App\Controllers\KV_Controller;
use App\Models\Log\ChangeModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Change extends KV_Controller
{
    protected $change;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->change          = new ChangeModel();
        $this->_title          = 'Log Perubahan';
        $this->_uri_name       = 'log/change';
        $this->_uri_name_view  = 'log/change';
        $this->_menu_code      = 'log_change';
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
            $list = $this->change->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'         => $item->id,
                    'username'   => kv_decrypt($item->username, getenv('APP_KEY')),
                    'table_name' => $item->table_name,
                    'types'      => $item->types,
                    'query'      => $item->query,
                    'notes'      => $item->notes,
                    'ip_address' => $item->ip_address,
                    'user_data'  => $item->user_data,
                    'created_at' => $item->created_at,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->change->count_all(),
                'recordsFiltered'   => $this->change->count_filtered(),
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
        $result = $this->db->table('log.change lc')
            ->select('lc.id, au.username, lc.table_name, lc.types, lc.query, lc.notes, lc.ip_address, lc.user_data, lc.created_at')
            ->join('acl.users au', 'lc.acl_user_id = au.id', 'left')
            ->where('lc.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }
}
