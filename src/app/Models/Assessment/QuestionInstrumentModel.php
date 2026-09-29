<?php

namespace App\Models\Assessment;

use App\Models\KV_Model;

class QuestionInstrumentModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'assessment.question_instrument';
    protected $alias            = 'assessment.question_instrument AS qi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'aspect_question_id', 'instrument', 'value'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'instrument'      => 'required',
        'value'           => 'required',
    ];
    protected $validationMessages   = [
        'instrument' => [
            'required'      => 'Silahkan masukkan instrumen penilaian',
        ],
        'value' => [
            'required'      => 'Silahkan masukkan standar nilai',
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
    protected $column_order = ['instrument'];
    protected $order = ['value' => 'ASC'];

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
            ->select('qi.id, qi.instrument, qi.value, qi.created_at')
            ->where('qi.aspect_question_id', $id);

        return $builder->countAllResults();
    }

    private function get_datatables_query($id)
    {
        $builder = $this->db->table($this->alias)
            ->select('qi.id, qi.instrument, qi.value, qi.created_at, qi.aspect_question_id')
            ->where('qi.aspect_question_id', $id);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(instrument)', strtolower($this->request->getVar('search')['value']));
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
