<?php

namespace App\Models\User;

use App\Models\KV_Model;

class UserModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ukom.users';
    protected $alias            = 'ukom.users AS u';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'nama_lengkap', 'username', 'email', 'role', 'password', 'kode_instansi', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'nama_lengkap' => 'required',
        'username'     => 'required',
        'email'        => 'required',
        'role'         => 'required',
    ];
    protected $validationMessages   = [
        'nama_lengkap' => ['required' => 'Silahkan masukkan nama lengkap'],
        'username'     => ['required' => 'Silahkan masukkan username'],
        'email'        => ['required' => 'Silahkan masukkan email'],
        'role'         => ['required' => 'Silahkan masukkan role'],
    ];

    protected $request;
    protected $column_order = ['nama_lengkap', 'username', 'email', 'role'];
    protected $order = ['created_at' => 'DESC'];

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
        return $query->countAllResults();
    }

    public function count_all()
    {
        $builder = $this->db->table($this->alias)->select('u.id');
        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('u.id, u.nama_lengkap, u.username, u.email, u.role, u.is_active, u.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(u.nama_lengkap)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(u.username)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(u.email)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(u.role)', strtolower($this->request->getVar('search')['value']));
            $builder->groupEnd();
        }

        if ($this->order) {
            $builder->orderBy(key($this->order), current($this->order));
        } else {
            $orderColumnIndex = $this->request->getVar('order')[0]['column'] ?? 0;
            $orderDirection = $this->request->getVar('order')[0]['dir'] ?? 'asc';
            $builder->orderBy($this->column_order[$orderColumnIndex] ?? 'created_at', $orderDirection);
        }

        return $builder;
    }
}
