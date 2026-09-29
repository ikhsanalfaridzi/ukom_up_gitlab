<?php

/**
 * Access Log Feature from Log Module
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
use App\Models\Log\AccessModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Access extends KV_Controller
{
    protected $access;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->access          = new AccessModel();
        $this->_title          = 'Log Akses';
        $this->_uri_name       = 'log/access';
        $this->_uri_name_view  = 'log/access';
        $this->_menu_code      = 'log_access';
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
            $list = $this->access->get_datatables();
            $data = [];

            foreach ($list as $item) {
                $data[] = [
                    'id'         => $item->id,
                    'username'   => kv_decrypt($item->username, getenv('APP_KEY')),
                    'url'        => $item->url,
                    'ip_address' => $item->ip_address,
                    'user_data'  => $item->user_data,
                    'created_at' => $item->created_at,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->access->count_all(),
                'recordsFiltered'   => $this->access->count_filtered(),
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
        $result = $this->db->table('log.access la')
            ->select('la.id, au.username, la.url, la.ip_address, la.user_data, la.created_at')
            ->join('acl.users au', 'la.acl_user_id = au.id', 'left')
            ->where('la.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }
}
