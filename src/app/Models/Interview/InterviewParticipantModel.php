<?php

namespace App\Models\Interview;

use App\Libraries\System\SystemLib;
use App\Models\KV_Model;

class InterviewParticipantModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'assessment.interview_participants';
    protected $alias            = 'assessment.interview_participants AS ip';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'interview_group_id', 'employee_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'interview_group_id'    => 'required',
        'employee_id'           => 'required',
    ];
    protected $validationMessages   = [
        'interview_group_id' => [
            'required'      => 'Silahkan masukkan nama kelompok',
        ],
        'employee_id' => [
            'required'      => 'Silahkan masukkan pilih nama pegawai',
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
    protected $column_order = ['name'];
    protected $order = ['created_at' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
    }

    public function get_datatables($id)
    {
        $query = $this->get_datatables_query($id);

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_filtered($id)
    {
        $query = $this->get_datatables_query($id);

        return $query->countAllResults();
    }

    public function count_all($id)
    {
        $builder = $this->db->table($this->alias)->select('ip.id')
            ->join('employee.personnel ep', 'ip.employee_id = ep.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->where('ip.interview_group_id', $id);

        return $builder->countAllResults();
    }

    private function get_datatables_query($id)
    {
        $builder = $this->db->table($this->alias)->select('ip.id, ep.id as employee_id, ep.employee_number, ipr.full_name, ea.assortment as grade_name, COALESCE(ips.position, \'Belum terdaftar\') as position_name, COALESCE(eg.grade, \'Belum terdaftar\') as position_grade_name, ip.created_at')
            ->join('employee.personnel ep', 'ip.employee_id = ep.id', 'left')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->where('ip.interview_group_id', $id);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ep.employee_number)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ipr.full_name)', strtolower($this->request->getVar('search')['value']));
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