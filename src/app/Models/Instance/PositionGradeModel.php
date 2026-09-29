<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class PositionGradeModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'employee.grades';
    protected $alias            = 'employee.grades AS g';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'position_group_id', 'code', 'grade'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'position_group_id'   => 'permit_empty',
        'code'                => 'required',
        'grade'               => 'required',
    ];
    protected $validationMessages   = [
        'position_group_id' => [
            'required'      => 'Silahkan pilih kelompok jabatan',
        ],
        'code' => [
            'required'      => 'Silahkan masukkan kode tingkatan',
        ],
        'grade' => [
            'required'      => 'Silahkan masukkan nama tingkatan',
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
    protected $column_order = ['name'];
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
        return $this->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('g.id, g.code, g.grade AS name, ipg.name AS group_name, g.created_at')
            ->join('instance.position_groups AS ipg', 'g.position_group_id = ipg.id', 'left');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ipg.name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(g.grade)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(g.code)', strtolower($this->request->getVar('search')['value']));
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
