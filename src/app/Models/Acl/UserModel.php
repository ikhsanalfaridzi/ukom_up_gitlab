<?php

namespace App\Models\Acl;

use App\Models\KV_Model;

class UserModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'acl.users';
    protected $alias            = 'acl.users AS au';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['username'];
    protected $allowedFields    = ['id', 'username', 'password', 'confirm_password', 'is_active', 'is_change_password', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'username'           => 'required|trim|min_length[3]|max_length[50]',
        'password'           => 'required|trim|min_length[8]|max_length[200]',
        'confirm_password'   => 'matches[password]',
        'is_active'          => 'permit_empty',
        'is_change_password' => 'permit_empty',
        'description'        => 'permit_empty|max_length[1000]',
    ];
    protected $validationMessages   = [
        'username' => [
            'required'      => 'Silahkan masukkan username',
            'max_length'    => 'Username maksimal {param} karakter',
        ],
        'password' => [
            'required'      => 'Silahkan masukkan password',
            'min_length'    => 'Password minimal {param} karakter',
            'max_length'    => 'Password maksimal {param} karakter',
        ],
        'confirm_password' => [
            'matches' => 'Konfirmasi password tidak sesuai dengan password'
        ],
        'description' => [
            'max_length'    => 'Deskripsi maksimal {param} karakter',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['user_hash', 'set_default'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['user_hash'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // Datatables
    protected $request;
    protected $column_order = ['username'];
    protected $order = ['au.updated_at' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
    }

    public function get_datatables()
    {
        $query = $this->get_datatables_query();

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_filtered()
    {
        $query = $this->get_datatables_query();
        $result = $query->get()->getResult();
        return count($result);
    }

    public function count_all()
    {
        $builder = $this->db->table($this->alias)->select('au.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('au.id, au.username, au.full_name, au.email, au.role, au.kode_instansi, au.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $search = $this->request->getVar('search')['value'];
            $builder->groupStart();
            $builder->like('LOWER(au.full_name)', strtolower($search));
            $builder->orLike('LOWER(au.username)', strtolower($search));
            $builder->orLike('LOWER(au.email)', strtolower($search));
            $builder->orLike('LOWER(au.role)', strtolower($search));
            $builder->groupEnd();
        }

        if ($this->order) {
            $builder->orderBy(key($this->order), current($this->order));
        } else {
            $builder->orderBy('au.updated_at', 'DESC');
        }

        return $builder;
    }

    protected function user_hash(array $data)
    {
        if (isset($data['data']['password']) && isset($data['data']['confirm_password'])) {
            $data['data']['password'] = pass_hash($data['data']['password']);
            unset($data['data']['confirm_password']);
        }

        $data['data']['username'] = kv_encrypt($data['data']['username'], getenv('APP_KEY'));

        return $data;
    }

    protected function set_default(array $data)
    {
        $data['data']['is_active'] = set_default_value($data['data']['is_active']);
        $data['data']['is_change_password'] = set_default_value($data['data']['is_change_password']);

        return $data;
    }
}
