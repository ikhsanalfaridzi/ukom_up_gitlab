<?php

namespace App\Models\System;

use App\Models\KV_Model;

class LanguageFieldModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sysapp.language_field';
    protected $alias            = 'sysapp.language_field AS slf';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['field_name'];
    protected $nullableFields   = ['description'];
    protected $allowedFields    = ['id', 'language_id', 'field_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'language_id' => 'required',
        'field_name'  => 'required|max_length[100]',
        'description' => 'permit_empty|max_length[1000]',
    ];
    protected $validationMessages   = [
        'language_id' => [
            'required'      => 'Silahkan pilih bahasa',
        ],
        'field_name' => [
            'required'      => 'Silahkan masukkan nama kolom',
            'max_length'    => 'Nama field maksimal 100 karakter',
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
    protected $column_order = ['field_name'];
    protected $order = ['slf.updated_at' => 'DESC'];

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
        $builder = $this->db->table($this->alias)->select('slf.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('slf.id, sl.lang_name , slf.field_name, slf.description, slf.created_at')
            ->join('sysapp.languages sl', 'slf.language_id = sl.id', 'left');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(sl.lang_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(slf.field_name)', strtolower($this->request->getVar('search')['value']));
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
