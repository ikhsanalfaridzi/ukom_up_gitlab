<?php

namespace App\Models\Assessment;

use App\Models\KV_Model;

class ComponentAspectModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'assessment.component_aspect';
    protected $alias            = 'assessment.component_aspect AS ca';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'assessment_component_id', 'aspect', 'is_active', 'order'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'aspect'    => 'required',
        'is_active' => 'required',
        'order'     => 'required',
    ];
    protected $validationMessages   = [
        'aspect' => [
            'required' => 'Silahkan masukkan nama aspek penilaian'
        ],
        'is_active' => [
            'required' => 'Silahkan pilih status aspek penilaian'
        ],
        'order' => [
            'required' => 'Silahkan masukkan urutan tampil aspek penilaian'
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
    protected $column_order = ['aspect'];
    protected $order = ['ca.created_at' => 'ASC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
    }

    public function get_datatables($id = "")
    {
        $query = $this->get_datatables_query($id);


        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_filtered($id = "")
    {
        $query = $this->get_datatables_query($id);
        return $query->countAllResults();
    }

    public function count_all($id = "")
    {
        $builder = $this->db->table($this->alias)
            ->select('ca.id')
            ->where('assessment_component_id', $id);

        return $builder->countAllResults();
    }

    public function get_datatables_all()
    {
        $query = $this->get_datatables_all_query();

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_filtered_all()
    {
        $query = $this->get_datatables_all_query();
        return $query->countAllResults();
    }

    public function count_all_all()
    {
        $builder = $this->db->table($this->alias)
            ->select('ca.id')
            ->countAllResults();

        return $builder;
    }

    private function get_datatables_all_query()
    {
        $builder = $this->db->table($this->alias)
            ->select('ca.id, ca.aspect, ca.is_active, ca.order, ca.created_at, ca.assessment_component_id, assessment.assessment_component.name AS component_name')
            ->join('assessment.assessment_component', 'assessment.assessment_component.id = ca.assessment_component_id', 'inner');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $value = strtolower($this->request->getVar('search')['value']);
            $builder->groupStart();
            $builder->like('LOWER(ca.aspect)', $value);
            $builder->orLike('LOWER(assessment.assessment_component.name)', $value);
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

    private function get_datatables_query($id)
    {
        $builder = $this->db->table($this->alias)
            ->select('ca.id, ca.aspect, ca.is_active, ca.order, ca.created_at')
            ->where('assessment_component_id', $id);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(aspect)', strtolower($this->request->getVar('search')['value']));
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
