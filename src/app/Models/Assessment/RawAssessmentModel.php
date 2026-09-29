<?php

namespace App\Models\Assessment;

use App\Models\KV_Model;
use App\Models\EmployeeManagement\EmployeeModel;

class RawAssessmentModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ukom.raw_assessment';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'evaluator', 'user_id', 'employee_id', 'aspect_question_id', 'question_instrument_id', 'type', 'interview_participant_id'];

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

    public function get_assessment_result($employee_id, $interview_participant_id = NULL)
    {
        $result = $this->db->table($this->table)
            ->select('component_aspect.aspect, aspect_question.question, aspect_question.order, question_instrument.value, assessment_component.name as component')
            ->join('assessment.question_instrument', 'CAST(raw_assessment.question_instrument_id AS VARCHAR) = CAST(question_instrument.id AS VARCHAR)', 'left', false)
            ->join('assessment.aspect_question', 'CAST(raw_assessment.aspect_question_id AS VARCHAR) = CAST(aspect_question.id AS VARCHAR)', 'left', false)
            ->join('assessment.component_aspect', 'aspect_question.component_aspect_id = component_aspect.id', 'left')
            ->join('assessment.assessment_component', 'component_aspect.assessment_component_id = assessment_component.id', 'left')
            ->where('employee_id', $employee_id);

        if (!empty($interview_participant_id)) {
            $result->where('interview_participant_id', $interview_participant_id);
        }

        return $result->orderBy('assessment_component.order', 'ASC')
            ->orderBy('aspect_question.order', 'ASC')
            ->get()
            ->getResultArray();
    }
}
