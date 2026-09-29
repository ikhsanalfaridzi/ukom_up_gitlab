<?php

namespace App\Libraries\System;

use App\Models\System\ReferenceModel;
use Config\Database;

class SystemLib
{
    protected $db;
    protected $reference;

    function __construct()
    {
        $this->db         = Database::connect();
        $this->reference  = new ReferenceModel();
    }

    public function get_sys_reference($key, $is_array = FALSE)
    {
        $result = $this->reference->select('notes')->where('description', $key)->first();

        if (empty($result['notes'])) {
            return $is_array ? [] : null;
        }

        if ($is_array) {
            $decoded = json_decode(str_replace("'", '"', $result['notes']), true);

            return is_array($decoded) ? $decoded : [];
        }

        return $result['notes'];
    }

    public function get_excluded_ids($key, $is_array = FALSE)
    {
        return $this->get_sys_reference($key, $is_array);
    }
}
