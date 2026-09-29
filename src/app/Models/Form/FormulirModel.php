<?php

namespace App\Models\Form;

use App\Models\KV_Model;

class FormulirModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'form.formulirs';
    protected $alias            = 'form.formulirs as pi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['name'];
    protected $allowedFields    = ['id', 'name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[100]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Silahkan masukkan nama formulir',
            'max_length' => 'Nama formulir maksimal {param} karakter',
        ],
    ];

    protected $skipValidation = false;
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
            $query->limit(
                $this->request->getVar('length'),
                $this->request->getVar('start')
            );
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
        $builder = $this->db->table($this->alias)->select('pi.id');
        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)
            ->select('pi.id, pi.name, pi.description, pi.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {

            $builder->groupStart();
            $builder->like(
                'LOWER(pi.name)',
                strtolower($this->request->getVar('search')['value'])
            );
            $builder->groupEnd();
        }

        if ($this->order) {
            $builder->orderBy(key($this->order), current($this->order));
        } else {
            $order_column_index = $this->request->getVar('order')[0]['column'];
            $order_direction = $this->request->getVar('order')[0]['dir'];

            $builder->orderBy(
                $this->column_order[$order_column_index],
                $order_direction
            );
        }

        return $builder;
    }
}