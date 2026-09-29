<?php

namespace App\Models\Region;

use App\Models\KV_Model;

class CityModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'region.cities';
    protected $alias            = 'region.cities AS rc';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['city_name'];
    protected $allowedFields    = ['id', 'province_id', 'city_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'province_id'    => 'required',
        'city_name'      => 'required|max_length[255]',
        'description'    => 'permit_empty',
    ];
    protected $validationMessages   = [
        'province_id' => [
            'required'      => 'Silahkan masukkan pilih provinsi',
        ],
        'city_name' => [
            'required'      => 'Silahkan masukkan nama kota',
            'max_length'    => 'Nama kota maksimal {param} karakter',
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
    protected $column_order = ['city_name'];
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
        $builder = $this->db->table($this->alias)->select('rc.id')
            ->join('region.provinces rp', 'rc.province_id = rp.id', 'left');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('rc.id, rc.city_name, rc.description, rc.created_at, rp.province_name')
            ->join('region.provinces rp', 'rc.province_id = rp.id', 'left');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(rc.city_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(rp.province_name)', strtolower($this->request->getVar('search')['value']));
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
