<?php

/**
 * Login Log Feature from Log Module
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
use App\Models\Log\LoginModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Login extends KV_Controller
{
    protected $login;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->login           = new LoginModel();
        $this->_title          = 'Log Login';
        $this->_uri_name       = 'log/login';
        $this->_uri_name_view  = 'log/login';
        $this->_menu_code      = 'log_login';
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
            $list = $this->login->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'         => $item->id,
                    'username'   => $item->username,
                    'types'      => $item->types,
                    'ip_address' => $item->ip_address,
                    'user_data'  => $item->user_data,
                    'created_at' => $item->created_at,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->login->count_all(),
                'recordsFiltered'   => $this->login->count_filtered(),
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
        $result = $this->db->table('log.login ll')
            ->select('ll.id, ll.username, ll.types, ll.session_id, ll.ip_address, ll.user_data, ll.created_at')
            ->where('ll.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }
}
