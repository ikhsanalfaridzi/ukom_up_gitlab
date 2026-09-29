<?php

namespace App\Models\Employee;

use App\Models\KV_Model;

class AssortmentModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'employee.assortments';
    protected $alias            = 'employee.assortments AS ea';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['code'];
    protected $allowedFields    = ['id', 'code', 'assortment', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'code'        => 'permit_empty|max_length[10]',
        'assortment'  => 'required|max_length[100]',
        'description' => 'permit_empty|max_length[200]',
    ];
    protected $validationMessages   = [
        'code' => [
            'max_length'    => 'Kode pangkat/golongan maksimal 10 karakter',
        ],
        'assortment' => [
            'required'      => 'Silahkan masukkan nama pangkat/golongan',
            'max_length'    => 'Pangkat/golongan maksimal 100 karakter',
        ],
        'description' => [
            'max_length'    => 'Deskripsi maksimal 200 karakter',
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
        $builder = $this->db->table($this->alias)->select('ea.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('ea.id, ea.code, ea.assortment, ea.description, ea.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ea.code)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ea.assortment)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ea.description)', strtolower($this->request->getVar('search')['value']));
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
