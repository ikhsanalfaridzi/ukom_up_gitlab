<?php

namespace App\Models\Form;

use App\Models\KV_Model;

class FormulirGroupModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'form.formulir_groups';
    protected $alias            = 'form.formulir_groups AS fg';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['formulir_group_name'];
    protected $allowedFields    = ['id', 'formulir_id', 'formulir_group_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'formulir_id'    => 'required',
        'formulir_group_name'      => 'required|max_length[255]',
        'description'    => 'permit_empty',
    ];
    protected $validationMessages   = [
        'formulir_id' => [
            'required'      => 'Silahkan masukkan pilih formulir',
        ],
        'formulir_group_name' => [
            'required'      => 'Silahkan masukkan nama grup formulir',
            'max_length'    => 'Nama grup formulir maksimal {param} karakter',
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
    protected $column_order = ['formulir_group_name'];
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
        $builder = $this->db->table($this->alias)->select('fg.id')
            ->join('form.formulirs pi', 'fg.formulir_id = pi.id', 'left');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('fg.id, fg.formulir_group_name, fg.description, fg.created_at, pi.name as formulir_name')
            ->join('form.formulirs pi', 'fg.formulir_id = pi.id', 'left');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(fg.formulir_group_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(pi.formulir_name)', strtolower($this->request->getVar('search')['value']));
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
