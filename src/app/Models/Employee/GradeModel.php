<?php

namespace App\Models\Employee;

use App\Models\KV_Model;

class GradeModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'employee.grades';
    protected $alias            = 'employee.grades AS eg';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['code'];
    protected $allowedFields    = ['id', 'code', 'grade', 'level', 'classification', 'position_group_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'code'              => 'permit_empty|max_length[10]',
        'grade'             => 'required|max_length[50]',
        'level'             => 'permit_empty|max_length[5]',
        'classification'    => 'permit_empty|max_length[50]',
    ];
    protected $validationMessages   = [
        'code' => [
            'max_length'    => 'Kode tingkatan maksimal {param} karakter',
        ],
        'grade' => [
            'required'      => 'Silahkan masukkan nama tingkatan',
            'max_length'    => 'Nama tingkatan maksimal {param} karakter',
        ],
        'level' => [
            'max_length'    => 'Level tingkatan maksimal {param} karakter',
        ],
        'classification' => [
            'max_length'    => 'Klasifikasi maksimal {param} karakter',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // Datatables
    protected $request;
    protected $column_order = ['code'];
    protected $order = ['code' => 'asc'];

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
        $builder = $this->db->table($this->alias)->select('eg.id')
            ->join('instance.position_groups ipg', 'eg.position_group_id = ipg.id', 'left');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('eg.id, eg.code, eg.grade, eg.level, eg.classification, ipg.name as group_name, eg.created_at')
            ->join('instance.position_groups ipg', 'eg.position_group_id = ipg.id', 'left');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(eg.code)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(eg.grade)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(eg.level)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(eg.classification)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ipg.name)', strtolower($this->request->getVar('search')['value']));
            $builder->groupEnd();
        }

        if ($this->order) {
            $builder->orderBy(key($this->order), current($this->order));
        } else {
            $order_column_index = $this->request->getVar('order')[0]['column'];
            $order_direction = $this->request->getVar('order')[0]['dir'];
            $builder->orderBy($this->column_order[$order_column_index], $order_direction);
        }


        return $builder;
    }
}
