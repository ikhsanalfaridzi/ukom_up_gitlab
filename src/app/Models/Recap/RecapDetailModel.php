<?php

namespace App\Models\Recap;

use App\Models\KV_Model;
use App\Models\Assessment\RawAssessmentModel;
use App\Models\Interview\InterviewGroupModel;
use App\Models\Assessment\QuestionInstrumentModel;

class RecapDetailModel extends KV_Model
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
    protected $employee;
    protected $raw_assessment;
    protected $interview_group;
    protected $question_instrument;
    protected $column_order = ['employee_number', 'full_name'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
        $this->raw_assessment = new RawAssessmentModel();
        $this->interview_group = new InterviewGroupModel();
        $this->question_instrument = new QuestionInstrumentModel();
    }

    public function get_datatables($interview_group_id)
    {
        $query = $this->get_datatables_query($interview_group_id);

        return $query;
    }

    public function count_all($interview_group_id)
    {
        $builder = $this->raw_assessment
            ->select('ep.id')
            ->distinct()
            ->join('employee.personnel ep', 'CAST(raw_assessment.employee_id AS VARCHAR) = CAST(ep.id AS VARCHAR)', 'left', false)
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('assessment.question_instrument', 'CAST(raw_assessment.question_instrument_id AS VARCHAR) = CAST(question_instrument.id AS VARCHAR)', 'left', false)
            ->join('assessment.aspect_question', 'CAST(raw_assessment.aspect_question_id AS VARCHAR) = CAST(aspect_question.id AS VARCHAR)', 'left', false)
            ->where('au.is_active', '1');

        return $builder->countAllResults();
    }


    public function count_filtered($interview_group_id)
    {
        $query = $this->get_datatables_query($interview_group_id);

        return count($query['final_result']);
    }

    private function get_datatables_query($interview_group_id)
    {
        $group = $this->interview_group->select('level')->find($interview_group_id);

        $builder = $this->raw_assessment
            ->select('raw_assessment.evaluator, raw_assessment.created_at, aoperator.full_name as operator, ep.id as employee_id, ipr.full_name, ipr.date_of_birth as birth_date, ea.assortment as grade_name, ea.code as grade_code, aspect_question.question, aspect_question.created_at, question_instrument.value, eg.code')
            ->distinct()
            ->join('acl.users aoperator', 'CAST(raw_assessment.user_id AS VARCHAR) = CAST(aoperator.id AS VARCHAR)', 'left', false)
            ->join('employee.personnel ep', 'CAST(raw_assessment.employee_id AS VARCHAR) = CAST(ep.id AS VARCHAR)', 'left', false)
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('assessment.interview_participants', 'CAST(raw_assessment.employee_id AS VARCHAR) = CAST(interview_participants.employee_id AS VARCHAR)', 'left', false)
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('assessment.question_instrument', 'CAST(raw_assessment.question_instrument_id AS VARCHAR) = CAST(question_instrument.id AS VARCHAR)', 'left', false)
            ->join('assessment.aspect_question', 'CAST(raw_assessment.aspect_question_id AS VARCHAR) = CAST(aspect_question.id AS VARCHAR)', 'left', false)
            ->where('au.is_active', '1')
            ->where('interview_participants.interview_group_id', $interview_group_id)
            ->orderBy('aspect_question.created_at', 'ASC');

        $result = $builder->findAll();

        $final_result = $this->format_result($result, $group['level']);

        return $final_result;
    }

    private function format_result($result, $level = 5)
    {
        $baseline = $this->question_instrument->select('aspect_question_id, aspect_question.question, aspect_question.level as max_value')
            ->join('assessment.aspect_question', 'question_instrument.aspect_question_id = aspect_question.id')
            ->where('aspect_question.is_active', true)
            ->where('aspect_question.level', $level)
            ->groupBy('aspect_question_id, aspect_question.question, aspect_question.level')
            ->findAll();

        $total_baseline = array_sum(array_column($baseline, 'max_value'));

        $final_result_temp = [];
        $final_result_qcount = [];
        $final_result_qsum = [];
        $final_head_p = [];
        $final_head_p_count = 0;

        // Step 1: Initialize counters and sums
        foreach ($result as $item) {
            $evaluator = $item['evaluator'];
            $operator = $item['operator'];
            $employee_id = $item['employee_id'];
            $question = $item['question'];

            if (!isset($final_result_qcount[$employee_id][$evaluator][$operator][$question])) {
                $final_result_qcount[$employee_id][$evaluator][$operator][$question] = 0;
                $final_result_qsum[$employee_id][$evaluator][$operator][$question] = 0;
            }

            if (!isset($final_head_p[$question])) {
                $final_head_p_count++;
                $final_head_p[$question] = $final_head_p_count;
            }

            $final_result_qcount[$employee_id][$evaluator][$operator][$question]++;
            $final_result_qsum[$employee_id][$evaluator][$operator][$question] += $item['value'];
        }

        // Step 2: Calculate average scores per question and set up final structure
        foreach ($final_result_qcount as $employee_id => $evaluators) {
            foreach ($evaluators as $evaluator => $operators) {
                foreach ($operators as $operator => $questions) {
                    $final_result_temp[$employee_id][$evaluator][$operator] = [
                        'employee_id'    => $employee_id,
                        'full_name'      => $result[array_search($employee_id, array_column($result, 'employee_id'))]['full_name'],
                        'birth_date'     => $result[array_search($employee_id, array_column($result, 'employee_id'))]['birth_date'],
                        'evaluator'      => $evaluator,
                        'operator'       => $operator,
                        'grade_name'     => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_name'],
                        'grade_code'     => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_code'],
                        'created_at'     => $result[array_search($employee_id, array_column($result, 'employee_id'))]['created_at'],
                        'score'          => 0,
                        'final_score'    => 0,
                        'details'        => []
                    ];

                    // Calculate the question-wise averages and add to details
                    foreach ($questions as $question => $count) {
                        $average_score = $final_result_qsum[$employee_id][$evaluator][$operator][$question] / $count;
                        $final_result_temp[$employee_id][$evaluator][$operator]['score'] += $average_score;

                        $final_result_temp[$employee_id][$evaluator][$operator]['details'][] = [
                            'question' => $question,
                            'value'    => $average_score
                        ];
                    }

                    // Calculate final_score and category
                    $final_result_temp[$employee_id][$evaluator][$operator]['final_score'] = ($final_result_temp[$employee_id][$evaluator][$operator]['score'] / $total_baseline) * 100;
                    $final_result_temp[$employee_id][$evaluator][$operator]['category'] = get_category($final_result_temp[$employee_id][$evaluator][$operator]['final_score']);

                    // Normalize Final Score
                    if ($final_result_temp[$employee_id][$evaluator][$operator]['final_score'] > 100) {
                        $final_result_temp[$employee_id][$evaluator][$operator]['final_score'] = 100;
                    }
                }
            }
        }

        // Step 3: Flatten and group results by employee_id
        $flattened_results = [];
        foreach ($final_result_temp as $employee_id => $evaluators) {
            foreach ($evaluators as $evaluator => $operators) {
                foreach ($operators as $operator => $details) {
                    $flattened_results[$employee_id][] = $details; // Group by employee_id
                }
            }
        }

        // Iterate over each employee_id group and apply ranking
        foreach ($flattened_results as $employee_id => &$data) {
            // Sort the data
            usort($data, function ($a, $b) {
                // Compare by final_score (descending)
                if ($a['final_score'] !== $b['final_score']) {
                    return $b['final_score'] <=> $a['final_score'];
                }

                // If final_score is the same, compare by grade_code (descending)
                if ($a['grade_code'] !== $b['grade_code']) {
                    return $b['grade_code'] <=> $a['grade_code'];
                }

                // If both final_score and grade_code are the same, compare by birth_date (ascending)
                return $a['birth_date'] <=> $b['birth_date'];
            });
        }

        $response = [
            'final_result' => $flattened_results,
            'header'       => $final_head_p,
        ];

        return $response;
    }
}
