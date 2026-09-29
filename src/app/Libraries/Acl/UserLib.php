<?php

namespace App\Libraries\Acl;

use App\Models\Acl\UserGroupModel;
use App\Models\Acl\UserModel;
use Config\Database;
use Exception;

class UserLib
{
    protected $db;
    protected $user;
    protected $user_group;

    function __construct()
    {
        $this->db         = Database::connect();
        $this->user       = new UserModel();
        $this->user_group = new UserGroupModel();
    }

    public function assign_user_groups($user_id, $group_ids)
    {
        if (empty($group_ids)) {
            throw new Exception('Silahkan pilih grup akses.');
        }

        // Remove permission by group
        $this->user_group->where('user_id', $user_id)->delete();

        // Assign permission
        $data = array_map(fn($group_id) => [
            'id'       => uuid_v4(),
            'user_id'  => $user_id,
            'group_id' => $group_id,
        ], $group_ids);

        $this->user_group->insertBatch($data);

        return true;
    }
}
