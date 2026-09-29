<?php

namespace App\Models\Assessment;

use App\Models\KV_Model;
use App\Models\EmployeeManagement\EmployeeModel;

class StagingAssessmentModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'assessment.staging_assessment';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'employee_id', 'component_aspect_id', 'value', 'type', 'interview_participant_id'];

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

    public function get_staging_assessment_result($employee_id, $interview_participant_id = NULL)
    {
        $result = $this->db->table($this->table)
            ->select('staging_assessment.value, component_aspect.aspect, assessment_component.name')
            ->join('assessment.component_aspect', 'component_aspect.id = staging_assessment.component_aspect_id', 'left')
            ->join('assessment.assessment_component', 'assessment_component.id = component_aspect.assessment_component_id', 'left')
            ->where('employee_id', $employee_id);

        if (!empty($interview_participant_id)) {
            $result->where('interview_participant_id', $interview_participant_id);
        }

        return $result->orderBy('assessment_component.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }
}
