<?php

/**
 * Override Methode Model Codeigniter 4
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-14
 */

namespace App\Models;

use App\Libraries\System\SystemLib;
use CodeIgniter\Model;
use Config\Database;
use Config\Services;

class KV_Model extends Model
{
    protected $db;
    protected $request;
    protected $sys_lib = null;

    /**
     * Please set default to TRUE (and set FALSE to disable. Check set_mode_log)
     */
    private $_is_save_log = TRUE;

    public function __construct()
    {
        parent::__construct();
        $this->db      = Database::connect();
        $this->request = Services::request();
    }

    /**
     * Override insert with logging
     */
    public function insert($data = null, bool $return_id = TRUE)
    {
        $result = parent::insert($data, $return_id);

        if ($this->_is_save_log && $result !== false) {
            $last_query = (string) $this->db->getLastQuery();
            $this->change_logs('insert', $this->table, json_encode($data), $last_query);
        }

        return $result;
    }

    /**
     * Override update with logging
     */
    public function update($id = null, $data = null): bool
    {
        $data_before = $this->find($id);
        $result = parent::update($id, $data);

        if ($this->_is_save_log && $result !== false) {
            $notes      = json_encode([
                'before' => $data_before,
                'after'  => $data,
            ]);
            $last_query = (string) $this->db->getLastQuery();
            $this->change_logs('update', $this->table, $notes, $last_query);
        }

        return $result;
    }

    /**
     * Override delete with logging
     */
    public function delete($id = null, bool $purge = false): bool
    {
        $data_before = $this->find($id);
        $result      = parent::delete($id, $purge);

        if ($this->_is_save_log && $result !== false) {
            $last_query = (string) $this->db->getLastQuery();
            $this->change_logs('delete', $this->table, json_encode($data_before), $last_query);
        }

        return $result;
    }

    /**
     * Logging handler
     */
    public function change_logs($types = 'update', $table = null, $notes = null, $last_query = null)
    {
        $acl_user_id = session()->get('login_user_id');

        // Lazy Load Reference Configuration
        $useable_account = $this->load_system_lib()->get_sys_reference('allowed_organization_configuration');
        $useable_account_list = json_decode(str_replace("'", '"', $useable_account), TRUE);

        if (in_array($acl_user_id, $useable_account_list ?? [])) {
            $this->_is_save_log = FALSE;
        }

        if ($this->_is_save_log === TRUE) {
            $log_id = uuid_v4();

            $data = [
                'id'          => $log_id,
                'acl_user_id' => $acl_user_id,
                'table_name'  => $table,
                'types'       => $types,
                'notes'       => $notes,
                'query'       => $last_query ?? '',
                'ip_address'  => $this->request->getIPAddress(),
                'user_data'   => $this->request->getUserAgent(),
                'month'       => date('m'),
                'year'        => date('Y'),
            ];

            $this->db->table('log.change')->insert($data);

            return $log_id;
        }
    }

    protected function load_system_lib()
    {
        if ($this->sys_lib === null) {
            $this->sys_lib = new SystemLib();
        }
        return $this->sys_lib;
    }
}
