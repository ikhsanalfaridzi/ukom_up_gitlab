<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class ClassModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.classes';
    protected $alias            = 'instance.classes AS ic';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['class'];
    protected $allowedFields    = ['id', 'class', 'performance_allowance', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'class'                     => 'required',
        'performance_allowance'     => 'required|max_length[150]',
        'description'               => 'permit_empty',
    ];
    protected $validationMessages   = [
        'class' => [
            'required'      => 'Silahkan masukkan kelas jabatan',
        ],
        'performance_allowance' => [
            'max_length'    => 'Maksimal 150 karakter',
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
    protected $column_order = ['class'];
    protected $order = ['class' => 'ASC'];

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
        $builder = $this->db->table($this->alias)->select('ic.id, ic.class, ic.performance_allowance, ic.description, ic.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('CAST(ic.class AS TEXT)', $this->request->getVar('search')['value']);
            $builder->orLike('ic.performance_allowance', $this->request->getVar('search')['value']);
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
