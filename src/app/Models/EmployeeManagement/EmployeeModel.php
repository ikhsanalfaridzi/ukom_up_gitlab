<?php

namespace App\Models\EmployeeManagement;

use App\Libraries\System\SystemLib;
use App\Models\KV_Model;

class EmployeeModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ukom.employee';
    protected $alias            = 'ukom.employee AS e';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'employee_number', 'old_employee_number', 'office_email', 'assignment_letter_path', 'unor_id', 'employment_id', 'assortment_id', 'position_id', 'position_grade_id', 'organization_structure_id', 'employment_type_id', 'is_verified', 'notes', 'verificator_id', 'verified_at', 'responsibility_letter_path', 'created_id', 'updated_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'employee_number'    => 'max_length[18]',
    ];
    protected $validationMessages   = [
        'employee_number' => [
            'max_length'    => 'NIP/NRP Maksimal memiliki {param} karakter',
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
    protected $column_order = ['employee_number', 'full_name'];
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
        $builder = $this->db->table('employee.personnel ep')
            ->select('ep.id')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('instance.organization_structures ios', 'CAST(ep.organization_structure_id AS VARCHAR) = CAST(ios.id AS VARCHAR)', 'left', false);

        $this->add_complete_filter($builder);

        return $builder->countAllResults();
    }

    private function add_complete_filter($builder)
    {
        $builder->where('ep.id IS NOT NULL')
            ->where('ep.employee_number IS NOT NULL')
            ->where('ep.employee_number !=', '')
            ->where('ipr.full_name IS NOT NULL')
            ->where('ipr.full_name !=', '')
            ->where('ips.position IS NOT NULL')
            ->where('ips.position !=', '')
            ->where('ios.structure_name IS NOT NULL')
            ->where('ios.structure_name !=', '');
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table('employee.personnel ep')
            ->select('ep.id, ep.employee_number, ipr.full_name, ips.position as position_name, ea.assortment as grade_name, ios.structure_name as instance_name, au.is_active, ep.created_at, eg.grade as position_grade')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('instance.organization_structures ios', 'CAST(ep.organization_structure_id AS VARCHAR) = CAST(ios.id AS VARCHAR)', 'left', false);

        $this->add_complete_filter($builder);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ep.employee_number)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ipr.full_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ea.assortment)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ips.position)', strtolower($this->request->getVar('search')['value']));
            $builder->groupEnd();
        }

        if ($this->order) {
            $builder->orderBy(key($this->order), current($this->order));
        } else {
            $orderColumnIndex = $this->request->getVar('order')[0]['column'];
            $orderDirection = $this->request->getVar('order')[0]['dir'];
            $builder->orderBy($this->column_order[$orderColumnIndex], $orderDirection);
        }

        return $builder;
    }
}
