<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class OtherOrganizationModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.organizations';
    protected $alias            = 'instance.organizations AS io';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['organization_code'];
    protected $allowedFields    = ['id', 'organization_code', 'organization', 'group_id', 'parent_id', 'province_id', 'time_zone', 'city_id', 'unor_id', 'is_demo', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'organization_code' => 'required|max_length[20]',
        'organization'      => 'required|max_length[250]',
        'group_id'          => 'required',
    ];
    protected $validationMessages = [
        'organization_code' => [
            'required'      => 'Silahkan masukkan kode instansi.',
            'is_exists'     => 'Kode instansi telah digunakan.',
            'max_length'    => 'Kode instansi tidak boleh lebih dari 20 karakter.',
        ],
        'organization' => [
            'required'      => 'Silahkan masukkan nama instansi.',
            'max_length'    => 'Nama instansi tidak boleh lebih dari 250 karakter.',
        ],
        'group_id' => [
            'required'      => 'Silahkan pilih jenis instansi.',
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
    protected $column_order = ['organization_code'];
    protected $order = ['created_at' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
    }

    public function get_datatables($is_parent = false)
    {
        $query = $this->get_datatables_query($is_parent);

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_filtered($is_parent = false)
    {
        $query = $this->get_datatables_query($is_parent);

        return $query->countAllResults();
    }

    public function count_all($is_parent = false)
    {
        $builder = $this->db->table($this->alias)->select('id')
            ->join('instance.organizations io2', 'io2.id = io.parent_id', 'left')
            ->join('instance.groups ig', 'ig.id = io.group_id', 'left')
            ->join('region.provinces rp', 'rp.id = io.province_id', 'left')
            ->join('region.cities rc', 'rc.id = io.city_id', 'left')
            ->where('io.organization_code NOT LIKE', '01%');

        if ($is_parent) {
            $builder->where('ig.sub_type', "5");
        } else {
            $builder->where('ig.sub_type !=', "5");
            $builder->where('ig.sub_type !=', "0");
        }

        return $builder->countAllResults();
    }

    private function get_datatables_query($is_parent)
    {
        $builder = $this->db->table($this->alias)
            ->select('io.id, io.organization_code, io.organization, io2.organization as parent_name, ig.group as group_name, rp.province_name, rc.city_name, io.created_at')
            ->join('instance.organizations io2', 'io2.id = io.parent_id', 'left')
            ->join('instance.groups ig', 'ig.id = io.group_id', 'left')
            ->join('region.provinces rp', 'rp.id = io.province_id', 'left')
            ->join('region.cities rc', 'rc.id = io.city_id', 'left')
            ->where('io.organization_code NOT LIKE', '01%');

        if ($is_parent) {
            $builder->where('ig.sub_type', "5");
        } else {
            $builder->where('ig.sub_type !=', "5");
            $builder->where('ig.sub_type !=', "0");
        }

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(io.organization_code)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(io.organization)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ig.group)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(rp.province_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(rc.city_name)', strtolower($this->request->getVar('search')['value']));
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
