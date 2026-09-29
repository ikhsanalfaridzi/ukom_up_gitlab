<?php

namespace App\Models\Assessment;

use App\Models\KV_Model;

class AssessmentModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'assessment';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
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

    public function get_datatables($eselon, $is_grade_active)
    {
        $query = $this->get_datatables_query($eselon, $is_grade_active);

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return
            $query->get()->getResult();
    }

    public function count_all($eselon, $is_grade_active)
    {
        $builder = $this->db->table('employee.personnel ep')
            ->select('ep.id')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('instance.organization_structures ios', 'CAST(ep.organization_structure_id AS VARCHAR) = CAST(ios.id AS VARCHAR)', 'left', false)
            ->where('au.is_active', '1')
            ->orderBy('eg.code', 'ASC');

        if (!empty($eselon)) {
            switch ($eselon) {
                case "ES1":
                    $builder->whereIn('eg.grade', ['Eselon I.a', 'Eselon I.b', 'Eselon II.a']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['IV/c', 'IV/d', 'IV/e']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES2":
                    $builder->whereIn('eg.grade', ['Eselon II.b', 'Eselon III.a']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['IV/b', 'IV/c', 'IV/d']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES3":
                    $builder->whereIn('eg.grade', ['Eselon III.b', 'Eselon IV.a']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['III/d', 'III/e', 'IV/a', 'IV/b']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES4":
                    $builder->whereIn('eg.grade', ['Eselon IV.b', 'Eselon V', 'Pelaksana']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['III/b', 'III/c', 'III/d']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES5":
                    $builder->where('eg.grade', 'Pelaksana');

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['III/a', 'III/b']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "FUNG":
                    $builder->whereIn('eg.grade', ['Ahli Utama', 'Ahli Madya', 'Ahli Muda', 'Ahli Pertama', 'Jabatan Fungsional Ahli', 'Jabatan Fungsional Terampil']);
                    $builder->orderBy('eg.code', 'ASC');
                    break;
            }
        }

        return $builder->countAllResults();
    }

    public function count_filtered($eselon, $is_grade_active)
    {
        $query = $this->get_datatables_query($eselon, $is_grade_active);

        return $query->countAllResults(false);
    }

    private function get_datatables_query($eselon, $is_grade_active)
    {
        if (empty($eselon)) {
            return [];
        }

        $builder = $this->db->table('employee.personnel ep')
            ->select('ep.id, ep.employee_number, ipr.full_name, ea.assortment as grade_name, eg.grade as position_grade, eg.code as position_grade_code, ips.position as position_name, ios.structure_name as instance_name, au.is_active, ep.created_at')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('instance.organization_structures ios', 'CAST(ep.organization_structure_id AS VARCHAR) = CAST(ios.id AS VARCHAR)', 'left', false)
            ->where('au.is_active', '1')
            ->orderBy('eg.code', 'ASC');

        if (!empty($eselon)) {
            switch ($eselon) {
                case "ES1":
                    $builder->whereIn('eg.grade', ['Eselon I.a', 'Eselon I.b', 'Eselon II.a']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['IV/c', 'IV/d', 'IV/e']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES2":
                    $builder->whereIn('eg.grade', ['Eselon II.b', 'Eselon III.a']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['IV/b', 'IV/c', 'IV/d']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES3":
                    $builder->whereIn('eg.grade', ['Eselon III.b', 'Eselon IV.a']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['III/d', 'III/e', 'IV/a', 'IV/b']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES4":
                    $builder->whereIn('eg.grade', ['Eselon IV.b', 'Eselon V', 'Pelaksana']);

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['III/b', 'III/c', 'III/d']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES5":
                    $builder->where('eg.grade', 'Pelaksana');

                    if ($is_grade_active) {
                        $builder->orWhereIn('ea.assortment', ['III/a', 'III/b']);
                    }

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "FUNG":
                    $builder->whereIn('eg.grade', ['Ahli Utama', 'Ahli Madya', 'Ahli Muda', 'Ahli Pertama', 'Jabatan Fungsional Ahli', 'Jabatan Fungsional Terampil']);
                    $builder->orderBy('eg.code', 'ASC');
                    break;
            }
        }

        if ($searchValue = strtolower($this->request->getVar('search')['value'] ?? '')) {
            $builder->groupStart()
                ->like('LOWER(ep.employee_number)', $searchValue)
                ->orLike('LOWER(ipr.full_name)', $searchValue)
                ->orLike('LOWER(ea.assortment)', $searchValue)
                ->orLike('LOWER(ips.position)', $searchValue)
                ->groupEnd();
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
