<?php

namespace App\Models\System;

use App\Models\KV_Model;

class ModuleModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sysapp.module';
    protected $alias            = 'sysapp.module AS sm';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $nullableFields   = ['description'];
    protected $allowedFields    = ['id', 'module_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'module_name' => 'required|max_length[100]',
        'description' => 'permit_empty|max_length[1000]',
    ];
    protected $validationMessages   = [
        'module_name' => [
            'required'      => 'Silahkan masukkan nama modul',
            'max_length'    => 'Nama modul maksimal 100 karakter',
        ],
        'description' => [
            'max_length'    => 'Deskripsi maksimal 1000 karakter',
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
    protected $column_order = ['module_name'];
    protected $order = ['updated_at' => 'DESC'];

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
        $builder = $this->db->table($this->alias)->select('sm.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('sm.id, sm.module_name, sm.description, sm.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(sm.module_name)', strtolower($this->request->getVar('search')['value']));
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
