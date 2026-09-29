<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class PayrollModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'payroll.salaries';
    protected $alias            = 'payroll.salaries AS ps';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'employee_personnel_id',
        'organization_id',
        'salary',
        'benefits',
        'document_path',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'employee_personnel_id' => 'required',
        'organization_id'      => 'required',
        'salary'               => 'required|numeric',
    ];

    protected $validationMessages = [
        'employee_personnel_id' => [
            'required' => 'Silahkan pilih pegawai.',
        ],
        'organization_id' => [
            'required' => 'Silahkan pilih unit kerja.',
        ],
        'salary' => [
            'required' => 'Silahkan masukkan nominal gaji.',
            'numeric'  => 'Nominal gaji harus berupa angka.',
        ],
    ];

    protected $request;
    protected $column_order = ['full_name', 'organization', 'salary', 'created_at'];
    protected $order = ['full_name' => 'ASC'];

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
        $builder = $this->db->table('employee.personnel ep')->select('ep.id');
        return $builder->countAllResults();
    }

    public function get_top_4_data()
    {
        return $this->db->table('payroll.salaries ps')
            ->select('ps.id AS salary_id, ps.employee_personnel_id, ps.organization_id, ps.salary, ps.benefits, ps.document_path, ps.created_at, COALESCE(ip.full_name, ep.employee_number, \'--\') AS full_name, COALESCE(io.organization, \'--\') AS organization')
            ->join('employee.personnel ep', 'ep.id = ps.employee_personnel_id', 'inner')
            ->join('identity.personal ip', 'ip.employee_personnel_id = ep.id', 'left')
            ->join('instance.organizations io', 'io.id = ps.organization_id', 'left')
            ->orderBy('ps.created_at', 'DESC')
            ->limit(4)
            ->get()
            ->getResult();
    }

    private function get_datatables_base_builder()
    {
        return $this->db->table('employee.personnel ep')
            ->select('ep.id AS employee_personnel_id, ios.organization_id AS default_org_id, COALESCE(ip.full_name, ep.employee_number, \'--\') AS full_name, COALESCE(io.organization, \'--\') AS organization, ps.id AS salary_id, ps.salary, ps.document_path, ps.created_at')
            ->join('identity.personal ip', 'ip.employee_personnel_id = ep.id', 'left')
            ->join('instance.organization_structures ios', 'ios.id = ep.organization_structure_id', 'left')
            ->join('instance.organizations io', 'io.id = ios.organization_id', 'left')
            ->join('payroll.salaries ps', 'ps.employee_personnel_id = ep.id', 'left');
    }

    private function get_datatables_query()
    {
        $builder = $this->get_datatables_base_builder();

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $searchValue = strtolower($this->request->getVar('search')['value']);
            $builder->groupStart();
            $builder->like('LOWER(ip.full_name)', $searchValue);
            $builder->orLike('LOWER(io.organization)', $searchValue);
            $builder->orLike('LOWER(ep.employee_number)', $searchValue);
            $builder->orLike('CAST(ps.salary AS TEXT)', $searchValue);
            $builder->groupEnd();
        }

        if ($this->request->getVar('order')) {
            $order_column_index = $this->request->getVar('order')[0]['column'];
            $order_direction = $this->request->getVar('order')[0]['dir'];
            if (isset($this->column_order[$order_column_index])) {
                $builder->orderBy($this->column_order[$order_column_index], $order_direction);
            }
        } else {
            $builder->orderBy('ip.full_name', 'ASC');
        }

        return $builder;
    }

    public function get_detail_by_id($id)
    {
        return $this->db->table($this->alias)
            ->select('ps.*, COALESCE(ip.full_name, ep.employee_number, \'--\') AS full_name, COALESCE(io.organization, \'--\') AS organization')
            ->join('employee.personnel ep', 'ep.id = ps.employee_personnel_id', 'left')
            ->join('identity.personal ip', 'ip.employee_personnel_id = ep.id', 'left')
            ->join('instance.organizations io', 'io.id = ps.organization_id', 'left')
            ->where('ps.id', $id)
            ->get()
            ->getRowArray();
    }
}
