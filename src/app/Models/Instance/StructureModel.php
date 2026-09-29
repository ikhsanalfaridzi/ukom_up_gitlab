<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class StructureModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.structures';
    protected $alias            = 'instance.structures AS is';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['structure_code'];
    protected $allowedFields    = ['id', 'structure_code', 'position_id', 'organization_id', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'structure_code'        => 'required|max_length[20]',
        'position_id'           => 'required',
        'organization_id'       => 'required',
    ];
    protected $validationMessages   = [
        'structure_code' => [
            'required'      => 'Silahkan masukkan kode struktur',
            'max_length'    => 'Kode struktur maksimal 20 karakter',
        ],
        'position_id' => [
            'required'      => 'Silahkan pilih jabatan',
        ],
        'organization_id' => [
            'required'      => 'Silahkan pilih unit kerja',
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
        $builder = $this->db->table($this->alias)->select('is.id')
            ->join('instance.positions ip', 'is.position_id = ip.id', 'left')
            ->join('instance.organizations io', 'is.organization_id = io.id', 'left')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'left')
            ->where('ig.sub_type !=', '0');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('is.id, is.structure_code, is.is_active, ip.position as position_name, io.organization as organization_name, is.created_at')
            ->join('instance.positions ip', 'is.position_id = ip.id', 'left')
            ->join('instance.organizations io', 'is.organization_id = io.id', 'left')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'left')
            ->where('ig.sub_type !=', '0');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('is.structure_code', $this->request->getVar('search')['value']);
            $builder->orLike('ip.position', $this->request->getVar('search')['value']);
            $builder->orLike('io.organization', $this->request->getVar('search')['value']);
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
