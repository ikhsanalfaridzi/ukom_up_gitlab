<?php

namespace App\Models\Interview;

use App\Models\KV_Model;

class EmployeeInterviewResponseModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'assessment.employee_interview_response';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $allowedFields    = ['employee_id', 'aspect_question_id', 'response'];
}
