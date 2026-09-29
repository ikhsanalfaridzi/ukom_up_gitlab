<?php

namespace App\Models\Region;

use App\Models\KV_Model;

class VillageModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'region.village';
    protected $alias            = 'region.village AS rv';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['village_name'];
    protected $allowedFields    = ['id', 'district_id', 'village_name', 'postal_code', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'district_id'   => 'required',
        'village_name'  => 'required|max_length[255]',
        'postal_code'   => 'required|max_length[10]',
        'description'   => 'permit_empty',
    ];
    protected $validationMessages   = [
        'district_id' => [
            'required'      => 'Silahkan masukkan pilih kecamatan',
        ],
        'village_name' => [
            'required'      => 'Silahkan masukkan nama kelurahan',
            'max_length'    => 'Nama kelurahan maksimal {param} karakter',
        ],
        'postal_code' => [
            'required'      => 'Silahkan masukkan kode pos',
            'max_length'    => 'Kode pos maksimal {param} karakter',
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
    protected $column_order = ['village_name'];
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
        $builder = $this->db->table($this->alias)->select('rv.id')
            ->join('region.district rd', 'rv.district_id = rd.id', 'inner')
            ->join('region.cities rc', 'rd.city_id = rc.id', 'inner')
            ->join('region.provinces rp', 'rc.province_id = rp.id', 'inner');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('rv.id, rv.village_name, rv.postal_code, rv.description, rv.created_at, rd.district_name, rc.city_name, rp.province_name')
            ->join('region.district rd', 'rv.district_id = rd.id', 'inner')
            ->join('region.cities rc', 'rd.city_id = rc.id', 'inner')
            ->join('region.provinces rp', 'rc.province_id = rp.id', 'inner');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(rv.village_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(rd.district_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(rc.city_name)', strtolower($this->request->getVar('search')['value']));
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
