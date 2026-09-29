<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class OrganizationStructureModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.organization_structures';
    protected $alias            = 'instance.organization_structures AS ios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['structure_code', 'archive_code'];
    protected $allowedFields    = ['id', 'structure_code', 'archive_code', 'structure_name', 'organization_id', 'is_active', 'unor_id', 'direct_parent_unor_id', 'parent_unor_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'structure_code'        => 'required|max_length[50]',
        'archive_code'           => 'permit_empty|max_length[50]',
        'structure_name'        => 'required|max_length[255]',
        'organization_id'       => 'required',
        'direct_parent_unor_id' => 'permit_empty|max_length[50]',
        'parent_unor_id'        => 'permit_empty|max_length[50]',
    ];
    protected $validationMessages   = [
        'structure_code' => [
            'required'      => 'Silahkan masukkan kode struktur',
            'max_length'    => 'Kode struktur maksimal {param} karakter',
        ],
        'archive_code' => [
            'max_length'    => 'Kode persuratan maksimal {param} karakter',
        ],
        'structure_name' => [
            'required'      => 'Silahkan masukkan nama struktur/bidang',
            'max_length'    => 'Nama struktur/bidang maksimal {param} karakter',
        ],
        'organization_id' => [
            'required'      => 'Silahkan pilih unit kerja',
        ],
        'direct_parent_unor_id' => [
            'max_length'    => 'Unor Struktur Atasan maksimal {param} karakter',
        ],
        'parent_unor_id' => [
            'max_length'    => 'Unor Struktur Induk maksimal {param} karakter',
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
    protected $column_order = ['structure_code'];
    protected $order = ['structure_code' => 'ASC'];

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
        $builder = $this->db->table($this->alias)->select('ios.id')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'inner')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"]);

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('ios.id, ios.structure_code, ios.archive_code, ios.structure_name, ios.is_active, io.organization, ios.created_at')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'inner')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"]);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ios.structure_code)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ios.archive_code)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ios.structure_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ios.is_active)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(io.organization)', strtolower($this->request->getVar('search')['value']));
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
