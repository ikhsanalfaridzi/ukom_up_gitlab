<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class PositionModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.positions';
    protected $alias            = 'instance.positions AS ip';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['position'];
    protected $allowedFields    = ['id', 'position', 'grade_id', 'class_id', 'unor_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'position' => 'required|max_length[255]',
        'grade_id' => 'required',
        'class_id' => 'permit_empty',
        'unor_id'  => 'permit_empty',
    ];
    protected $validationMessages   = [
        'position' => [
            'required'      => 'Silahkan masukkan nama jabatan',
            'max_length'    => 'Nama jabatan maksimal 255 karakter',
        ],
        'grade_id' => [
            'required'      => 'Silahkan pilih tingkatan jabatan',
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
        $builder = $this->db->table($this->alias)->select('ip.id')
            ->join('employee.grades eg', 'ip.grade_id = eg.id', 'inner')
            ->join('instance.classes ic', 'ip.class_id = ic.id', 'left');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('ip.id, ip.position, eg.grade as grade_name, ic.class as class_name, ip.created_at')
            ->join('employee.grades eg', 'ip.grade_id = eg.id', 'inner')
            ->join('instance.classes ic', 'ip.class_id = ic.id', 'left');

        $searchValue = $this->request->getVar('search')['value'] ?? null;

        if (!empty($searchValue)) {
            $builder->groupStart();
            $builder->like('LOWER(ip.position)', strtolower($searchValue));
            $builder->orLike('LOWER(eg.grade)', strtolower($searchValue));

            if (ctype_digit($searchValue)) {
                $builder->orWhere('ic.class', (int)$searchValue);
            }
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
