<?php

namespace App\Models\Assessment;

use App\Models\KV_Model;
use App\Models\EmployeeManagement\EmployeeModel;

class AssessmentResultModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'assessment.assessment_result';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'employee_id', 'assessment_component_id', 'value', 'type', 'interview_participant_id'];

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

    public function get_final_assessment_result($employee_id, $interview_participant_id = NULL)
    {
        $result = $this->db->table($this->table)
            ->select('assessment_result.value, assessment_component.name')
            ->join('assessment.assessment_component', 'assessment_component.id = assessment_result.assessment_component_id', 'left')
            ->where('employee_id', $employee_id)
            ->where('assessment_component.is_active', true);

        if (!empty($interview_participant_id)) {
            $result->where('interview_participant_id', $interview_participant_id);
        }

        return $result->orderBy('assessment_component.order', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function get_final_score($employee_id, $interview_participant_id = NULL)
    {
        $result = $this->db->table('ukom.raw_assessment')
            ->select('AVG(question_instrument.value) as score, COUNT(raw_assessment.aspect_question_id) as baseline')
            ->join('assessment.question_instrument', 'CAST(raw_assessment.question_instrument_id AS VARCHAR) = CAST(question_instrument.id AS VARCHAR)', 'left', false)
            ->where('employee_id', $employee_id);

        if (!empty($interview_participant_id)) {
            $result->where('interview_participant_id', $interview_participant_id);
        }

        return $result->groupBy('raw_assessment.aspect_question_id')
            ->get()
            ->getResultArray();
    }

    public function get_evaluator($employee_id, $interview_participant_id = NULL)
    {
        $result = $this->db->table('ukom.raw_assessment')
            ->select('raw_assessment.evaluator, raw_assessment.created_at')
            ->distinct()
            ->where('employee_id', $employee_id);

        if (!empty($interview_participant_id)) {
            $result->where('interview_participant_id', $interview_participant_id);
        }

        return $result->orderBy('raw_assessment.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }
}
