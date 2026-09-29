<?php

namespace App\Models\Region;

use App\Models\KV_Model;

class ProvinceModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'region.provinces';
    protected $alias            = 'region.provinces AS rp';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['province_name'];
    protected $allowedFields    = ['id', 'province_name', 'time_zone', 'iso_code', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'province_name'    => 'required|max_length[255]',
        'time_zone'        => 'permit_empty|max_length[50]',
        'iso_code'         => 'permit_empty|max_length[10]',
        'description'      => 'permit_empty',
    ];
    protected $validationMessages   = [
        'province_name' => [
            'required'      => 'Silahkan masukkan nama provinsi',
            'max_length'    => 'Nama provinsi maksimal {param} karakter',
        ],
        'time_zone' => [
            'max_length'    => 'Timezone maksimal {param} karakter',
        ],
        'iso_code' => [
            'max_length'    => 'Iso code maksimal {param} karakter',
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
    protected $column_order = ['province_name'];
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
        $builder = $this->db->table($this->alias)->select('rp.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('rp.id, rp.province_name, rp.time_zone, rp.iso_code, rp.description, rp.created_at');


        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(province_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(time_zone)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(iso_code)', strtolower($this->request->getVar('search')['value']));
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
