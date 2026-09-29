<?php

namespace App\Models\Ukom;

use App\Models\KV_Model;

class GradeModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ukom.grade';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'code', 'name', 'description'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [
        'code' => 'required',
        'name' => 'required',
    ];
    protected $validationMessages   = [
        'code' => [
            'required' => 'Silahkan masukkan kode golongan',
        ],
        'name' => [
            'required' => 'Silahkan masukkan nama golongan',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected $request;
    protected $column_order = ['code'];
    protected $order = ['code' => 'ASC'];

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
        return $this->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->table)
            ->select('id, code, name, description, created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(code)', strtolower($this->request->getVar('search')['value']));
            $builder->groupEnd();
        }

        if ($this->order) {
            $builder->orderBy(key($this->order), current($this->order));
        } else {
            $orderColumnIndex = $this->request->getVar('order')[0]['column'];
            $orderDirection = $this->request->getVar('order')[0]['dir'];
            $builder->orderBy($this->column_order[$orderColumnIndex], $orderDirection);
        }

        return $builder;
    }
}
