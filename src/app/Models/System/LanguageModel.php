<?php

namespace App\Models\System;

use App\Models\KV_Model;

class LanguageModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sysapp.languages';
    protected $alias            = 'sysapp.languages AS sl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $nullableFields   = ['iso_code', 'description', 'photo_path'];
    protected $allowedFields    = ['id', 'lang_name', 'iso_code', 'description', 'photo_path'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'lang_name'   => 'required|max_length[200]',
        'iso_code'    => 'permit_empty|max_length[10]',
        'description' => 'permit_empty|max_length[1000]',
        'photo_path'  => 'permit_empty|max_length[1000]',
    ];
    protected $validationMessages   = [
        'lang_name' => [
            'required'      => 'Silahkan masukkan nama bahasa',
            'max_length'    => 'Nama bahasa maksimal 200 karakter',
        ],
        'iso_code' => [
            'max_length'    => 'ISO Code maksimal 10 karakter',
        ],
        'description' => [
            'max_length'    => 'Deskripsi maksimal 1000 karakter',
        ],
        'photo_path' => [
            'max_length'    => 'Icon path maksimal 1000 karakter',
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
    protected $column_order = ['lang_name'];
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
        $builder = $this->db->table($this->alias)->select('sl.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('sl.id, sl.lang_name, sl.iso_code, sl.description, sl.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(sl.lang_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(sl.iso_code)', strtolower($this->request->getVar('search')['value']));
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
