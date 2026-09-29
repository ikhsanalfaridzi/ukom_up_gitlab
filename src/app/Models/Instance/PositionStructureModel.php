<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class PositionStructureModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.position_structures';
    protected $alias            = 'instance.position_structures AS ips';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $allowedFields    = ['id', 'position_id', 'organization_structure_id', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'position_id'               => 'required',
        'organization_structure_id' => 'required',
    ];
    protected $validationMessages   = [
        'position_id' => [
            'required'      => 'Silahkan pilih jabatan',
        ],
        'organization_structure_id' => [
            'required'      => 'Silahkan pilih struktur/bidang',
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
    protected $column_order = ['position_id'];
    protected $order = ['position_id' => 'ASC'];

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
        $builder = $this->db->table($this->alias)->select('ips.id')
            ->join('instance.positions ip', 'ips.position_id = ip.id', 'inner')
            ->join('instance.organization_structures ios', 'ips.organization_structure_id = ios.id', 'inner')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'inner')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"]);

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('ips.id, ip.position, io.organization, ips.is_active, ips.created_at')
            ->join('instance.positions ip', 'ips.position_id = ip.id', 'inner')
            ->join('instance.organization_structures ios', 'ips.organization_structure_id = ios.id', 'inner')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'inner')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"]);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ip.position)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(io.organization)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ips.is_active)', strtolower($this->request->getVar('search')['value']));
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
