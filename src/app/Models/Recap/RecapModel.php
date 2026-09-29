<?php

namespace App\Models\Recap;

use App\Models\KV_Model;
use App\Models\Assessment\AspectQuestionModel;
use App\Models\Assessment\RawAssessmentModel;
use App\Models\Interview\InterviewGroupModel;
use App\Models\Assessment\QuestionInstrumentModel;

class RecapModel extends KV_Model
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
    protected $aspect_question;
    protected $raw_assessment;
    protected $interview_group;
    protected $question_instrument;
    protected $column_order = ['employee_number', 'full_name'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
        $this->aspect_question = new AspectQuestionModel();
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
            ->join('assessment.interview_participants', 'CAST(raw_assessment.employee_id AS VARCHAR) = CAST(interview_participants.employee_id AS VARCHAR)', 'left', false)
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('assessment.question_instrument', 'CAST(raw_assessment.question_instrument_id AS VARCHAR) = CAST(question_instrument.id AS VARCHAR)', 'left', false)
            ->join('assessment.aspect_question', 'CAST(raw_assessment.aspect_question_id AS VARCHAR) = CAST(aspect_question.id AS VARCHAR)', 'left', false)
            ->where('au.is_active', '1')
            ->where('interview_participants.interview_group_id', $interview_group_id)
            ->orderBy('aspect_question.order', 'ASC');

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
            ->select('ep.id as employee_id, ipr.full_name, ipr.date_of_birth as birth_date, ea.assortment as grade_name, ea.code as grade_code, aspect_question.question, aspect_question.order, question_instrument.value, eg.code')
            // ->distinct()
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
            ->orderBy('aspect_question.order', 'ASC');

        $result = $builder->findAll();

        $final_result = $this->format_result($result, $group['level']);

        return $final_result;
    }

    private function format_result($result, $level = 5)
    {
        $max_instrument = $this->aspect_question->select('MAX(qi.value) as max_value, COUNT(DISTINCT aspect_question.id) as max_question')
            ->join('assessment.question_instrument qi', 'qi.aspect_question_id = aspect_question.id', 'left')
            ->where('aspect_question.level', $level)
            ->first();

        $max_score = $max_instrument['max_value'] * $max_instrument['max_question'];

        $final_result_temp = [];
        $final_result_qcount = [];
        $final_result_qsum = [];
        $final_head_p = [];
        $final_head_p_count = 0;

        // Step 1: Initialize counters and sums
        foreach ($result as $index => $item) {
            if (!isset($final_result_qcount[$item['employee_id']][$item['question']])) {
                $final_result_qcount[$item['employee_id']][$item['question']] = 0;
                $final_result_qsum[$item['employee_id']][$item['question']] = 0;
            }

            if (!isset($final_head_p[$item['question']])) {
                $final_head_p_count++;
                $final_head_p[$item['question']] = $final_head_p_count;
            }

            $final_result_qcount[$item['employee_id']][$item['question']]++;
            $final_result_qsum[$item['employee_id']][$item['question']] += $item['value'];
        }

        // Step 2: Calculate average scores per question and set up final structure
        foreach ($final_result_qcount as $employee_id => $q) {
            $final_result_temp[$employee_id] = [
                'employee_id' => $employee_id,
                'full_name'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['full_name'],
                'birth_date'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['birth_date'],
                'grade_name'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_name'],
                'grade_code'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_code'],
                'score'       => 0,
                'final_score' => 0,
                'details'     => []
            ];

            // Calculate the question-wise averages and add to details
            foreach ($q as $question => $count) {
                $average_score = $final_result_qsum[$employee_id][$question] / $count;
                $final_result_temp[$employee_id]['score'] += $average_score;

                $final_result_temp[$employee_id]['details'][] = [
                    'question' => $question,
                    'value'    => $average_score
                ];
            }

            // Calculate final_score and category
            // $final_result_temp[$employee_id]['final_score'] = ($final_result_temp[$employee_id]['score'] / $total_baseline) * 100;
            $final_result_temp[$employee_id]['final_score'] = $max_score > 0 ? ($final_result_temp[$employee_id]['score'] / $max_score) * 100 : 0;
            $final_result_temp[$employee_id]['category'] = get_category($final_result_temp[$employee_id]['final_score']);
        }

        // Step 3: Sort by final_score in descending order
        usort($final_result_temp, function ($a, $b) {
            // Compare by final_score (descending)
            if ($a['final_score'] !== $b['final_score']) {
                return $b['final_score'] <=> $a['final_score'];
            }

            // If final_score is the same, compare by grade_code (descending)
            if ($a['grade_code'] !== $b['grade_code']) {
                return $b['grade_code'] <=> $a['grade_code'];
            }

            // If both final_score and grade_code are the same, compare by birth_date (descending)
            return $b['birth_date'] <=> $a['birth_date'];
        });

        // Step 4: Add ranking
        foreach ($final_result_temp as $index => &$item) {
            $item['ranking'] = $index + 1;
        }

        $response = [
            'final_result' => $final_result_temp,
            'header'       => $final_head_p,
        ];

        return $response;
    }

    public function get_all_data($eselon)
    {
        $query = $this->get_all_data_query($eselon);

        return $query;
    }

    public function count_all_data($eselon)
    {
        if (empty($eselon)) {
            return 0;
        }

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

        if (!empty($eselon) && $eselon !== 'FUNG') {
            switch ($eselon) {
                case "ES1":
                    $builder->whereIn('eg.grade', ['Eselon I.a', 'Eselon I.b', 'Eselon II.a']);

                    break;
                case "ES2":
                    $builder->whereIn('eg.grade', ['Eselon II.b', 'Eselon III.a']);

                    break;
                case "ES3":
                    $builder->whereIn('eg.grade', ['Eselon III.b', 'Eselon IV.a']);

                    break;
                case "ES4":
                    $builder->whereIn('eg.grade', ['Eselon IV.b', 'Eselon V', 'Pelaksana']);

                    break;
                case "ES5":
                    $builder->where('eg.grade', 'Pelaksana');

                    break;
            }
        }

        return $builder->countAllResults();
    }

    public function count_filtered_data($eselon)
    {
        $query = $this->get_all_data_query($eselon);

        return count($query['final_result']);
    }

    private function get_all_data_query($eselon)
    {
        if (empty($eselon)) {
            return [
                'final_result' => [],
                'header'       => [],
            ];
        }

        $builder = $this->raw_assessment
            ->select('ep.id as employee_id, ipr.full_name, ipr.date_of_birth as birth_date, ea.assortment as grade_name, ea.code as grade_code, aspect_question.question, aspect_question.order, aspect_question.level, question_instrument.value, eg.code')
            // ->distinct()
            ->join('employee.personnel ep', 'CAST(raw_assessment.employee_id AS VARCHAR) = CAST(ep.id AS VARCHAR)', 'left', false)
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('assessment.question_instrument', 'CAST(raw_assessment.question_instrument_id AS VARCHAR) = CAST(question_instrument.id AS VARCHAR)', 'left', false)
            ->join('assessment.aspect_question', 'CAST(raw_assessment.aspect_question_id AS VARCHAR) = CAST(aspect_question.id AS VARCHAR)', 'left', false)
            ->where('au.is_active', '1')
            ->orderBy('aspect_question.order', 'ASC');

        if (!empty($eselon) && $eselon !== 'FUNG') {
            switch ($eselon) {
                case "ES1":
                    $builder->whereIn('eg.grade', ['Eselon I.a', 'Eselon I.b', 'Eselon II.a']);

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES2":
                    $builder->whereIn('eg.grade', ['Eselon II.b', 'Eselon III.a']);

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES3":
                    $builder->whereIn('eg.grade', ['Eselon III.b', 'Eselon IV.a']);

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES4":
                    $builder->whereIn('eg.grade', ['Eselon IV.b', 'Eselon V', 'Pelaksana']);

                    $builder->orderBy('eg.code', 'ASC');
                    break;
                case "ES5":
                    $builder->where('eg.grade', 'Pelaksana');

                    $builder->orderBy('eg.code', 'ASC');
                    break;
            }
        } elseif (!empty($eselon) && $eselon === 'FUNG') {
            $builder->orderBy('eg.code', 'ASC');
        }

        if ($searchValue = strtolower($this->request->getVar('search')['value'] ?? '')) {
            $builder->groupStart()
                ->like('LOWER(ipr.full_name)', $searchValue)
                ->groupEnd();
        }

        $result = $builder->findAll();

        $final_result = $this->format_data($result, $eselon);

        return $final_result;
    }

    private function format_data($result, $eselon)
    {
        if ($eselon === 'FUNG') {
            return $this->format_all_data($result, $eselon);
        }

        $level = get_eselon_level($eselon);

        $max_instrument = $this->aspect_question->select('MAX(qi.value) as max_value, COUNT(DISTINCT aspect_question.id) as max_question')
            ->join('assessment.question_instrument qi', 'qi.aspect_question_id = aspect_question.id', 'left')
            ->where('aspect_question.level', $level)
            ->first();

        $max_score = ($max_instrument['max_value'] ?? 0) * ($max_instrument['max_question'] ?? 0);

        $final_result_temp = [];
        $final_result_qcount = [];
        $final_result_qsum = [];
        $final_head_p = [];
        $final_head_p_count = 0;

        // Step 1: Initialize counters and sums
        foreach ($result as $index => $item) {
            if (!isset($final_result_qcount[$item['employee_id']][$item['question']])) {
                $final_result_qcount[$item['employee_id']][$item['question']] = 0;
                $final_result_qsum[$item['employee_id']][$item['question']] = 0;
            }

            if (!isset($final_head_p[$item['question']])) {
                $final_head_p_count++;
                $final_head_p[$item['question']] = $final_head_p_count;
            }

            $final_result_qcount[$item['employee_id']][$item['question']]++;
            $final_result_qsum[$item['employee_id']][$item['question']] += $item['value'];
        }

        // Step 2: Calculate average scores per question and set up final structure
        foreach ($final_result_qcount as $employee_id => $q) {
            $final_result_temp[$employee_id] = [
                'employee_id' => $employee_id,
                'full_name'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['full_name'],
                'birth_date'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['birth_date'],
                'grade_name'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_name'],
                'grade_code'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_code'],
                'score'       => 0,
                'final_score' => 0,
                'details'     => []
            ];

            // Calculate the question-wise averages and add to details
            foreach ($q as $question => $count) {
                $average_score = $final_result_qsum[$employee_id][$question] / $count;
                $final_result_temp[$employee_id]['score'] += $average_score;

                $final_result_temp[$employee_id]['details'][] = [
                    'question' => $question,
                    'value'    => $average_score
                ];
            }

            // Calculate final_score and category
            $final_result_temp[$employee_id]['final_score'] = $max_score > 0 ? ($final_result_temp[$employee_id]['score'] / $max_score) * 100 : 0;
            $final_result_temp[$employee_id]['category'] = get_category($final_result_temp[$employee_id]['final_score']);
        }

        // Step 3: Sort by final_score in descending order
        usort($final_result_temp, function ($a, $b) {
            if ($a['final_score'] !== $b['final_score']) {
                return $b['final_score'] <=> $a['final_score'];
            }

            if ($a['grade_code'] !== $b['grade_code']) {
                return $b['grade_code'] <=> $a['grade_code'];
            }

            return $b['birth_date'] <=> $a['birth_date'];
        });

        // Step 4: Add ranking
        foreach ($final_result_temp as $index => &$item) {
            $item['ranking'] = $index + 1;
        }

        $response = [
            'final_result' => $final_result_temp,
            'header'       => $final_head_p,
        ];

        return $response;
    }

    private function format_all_data($result, $eselon)
    {
        unset($eselon);
        $is_all = true;

        // Canonical question list (order must match the aspect() columns)
        $questions = $this->aspect_question->select('question')
            ->orderBy('level', 'ASC')->orderBy('order', 'ASC')->findAll();
        $question_list = [];
        foreach ($questions as $qq) {
            if (!in_array($qq['question'], $question_list, true)) {
                $question_list[] = $qq['question'];
            }
        }
        $question_index = array_flip($question_list);
        $question_count = count($question_list);

        // Baseline max score per level
        $max_by_level = [];
        $levels = array_values(array_unique(array_filter(array_column($result, 'level'), function ($l) {
            return $l !== NULL;
        })));
        foreach ($levels as $l) {
            $mi = $this->aspect_question->select('MAX(qi.value) as max_value, COUNT(DISTINCT aspect_question.id) as max_question')
                ->join('assessment.question_instrument qi', 'qi.aspect_question_id = aspect_question.id', 'left')
                ->where('aspect_question.level', $l)
                ->first();
            $max_by_level[$l] = (int)($mi['max_value'] ?? 0) * (int)($mi['max_question'] ?? 0);
        }

        $final_result_temp = [];
        $final_result_qcount = [];
        $final_result_qsum = [];

        // Step 1: Initialize counters and sums
        foreach ($result as $item) {
            if (!isset($final_result_qcount[$item['employee_id']][$item['question']])) {
                $final_result_qcount[$item['employee_id']][$item['question']] = 0;
                $final_result_qsum[$item['employee_id']][$item['question']] = 0;
            }

            $final_result_qcount[$item['employee_id']][$item['question']]++;
            $final_result_qsum[$item['employee_id']][$item['question']] += $item['value'];
        }

        // Step 2: Calculate average scores per question and set up final structure
        foreach ($final_result_qcount as $employee_id => $q) {
            $row = $result[array_search($employee_id, array_column($result, 'employee_id'))];
            $emp_level = $row['level'] ?? NULL;

            // Align details to the canonical question columns (NULL = unanswered)
            $details = array_fill(0, $question_count, NULL);
            $emp_score = 0;

            foreach ($q as $question => $count) {
                $average_score = $final_result_qsum[$employee_id][$question] / $count;
                $emp_score += $average_score;

                if (isset($question_index[$question])) {
                    $details[$question_index[$question]] = $average_score;
                }
            }

            $emp_max = $max_by_level[$emp_level] ?? 0;
            $final_score = $emp_max > 0 ? ($emp_score / $emp_max) * 100 : 0;

            $final_result_temp[$employee_id] = [
                'employee_id' => $employee_id,
                'full_name'   => $row['full_name'],
                'birth_date'   => $row['birth_date'],
                'grade_name'   => $row['grade_name'],
                'grade_code'   => $row['grade_code'],
                'score'       => $emp_score,
                'final_score' => $final_score,
                'details'     => $details,
            ];
            $final_result_temp[$employee_id]['category'] = get_category($final_score);
        }

        // Step 3: Sort by final_score, then grade_code, then birth_date (all descending)
        usort($final_result_temp, function ($a, $b) {
            if ($a['final_score'] !== $b['final_score']) {
                return $b['final_score'] <=> $a['final_score'];
            }

            if ($a['grade_code'] !== $b['grade_code']) {
                return strcmp((string) $b['grade_code'], (string) $a['grade_code']);
            }

            return (string) $b['birth_date'] <=> (string) $a['birth_date'];
        });

        // Step 4: Add ranking
        foreach ($final_result_temp as $index => &$item) {
            $item['ranking'] = $index + 1;
        }

        $response = [
            'final_result' => $final_result_temp,
            'header'       => $question_list,
        ];

        return $response;
    }

    public function get_convert_datatables($interview_group_id)
    {
        $query = $this->get_convert_datatables_query($interview_group_id);

        return $query;
    }

    private function get_convert_datatables_query($interview_group_id)
    {
        $group = $this->interview_group->select('level')->find($interview_group_id);

        $builder = $this->raw_assessment
            ->select('ep.id as employee_id, ipr.full_name, ipr.date_of_birth as birth_date, ea.assortment as grade_name, ea.code as grade_code, aspect_question.question, aspect_question.order, question_instrument.value, eg.code')
            // ->distinct()
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
            ->orderBy('aspect_question.order', 'ASC');

        $result = $builder->findAll();

        $final_result = $this->format_convert_result($result, $group['level']);

        return $final_result;
    }

    private function format_convert_result($result, $level = 5)
    {
        $max_instrument = $this->aspect_question->select('MAX(qi.value) as max_value, COUNT(DISTINCT aspect_question.id) as max_question')
            ->join('assessment.question_instrument qi', 'qi.aspect_question_id = aspect_question.id', 'left')
            ->where('aspect_question.level', $level)
            ->first();

        $max_score = $max_instrument['max_value'] * $max_instrument['max_question'];

        $final_result_temp = [];
        $final_result_qcount = [];
        $final_result_qsum = [];
        $final_head_p = [];
        $final_head_p_count = 0;

        // Step 1: Initialize counters and sums
        foreach ($result as $index => $item) {
            if (!isset($final_result_qcount[$item['employee_id']][$item['question']])) {
                $final_result_qcount[$item['employee_id']][$item['question']] = 0;
                $final_result_qsum[$item['employee_id']][$item['question']] = 0;
            }

            if (!isset($final_head_p[$item['question']])) {
                $final_head_p_count++;
                $final_head_p[$item['question']] = $final_head_p_count;
            }

            $final_result_qcount[$item['employee_id']][$item['question']]++;
            $final_result_qsum[$item['employee_id']][$item['question']] += $item['value'];
        }

        // Step 2: Calculate average scores per question and set up final structure
        foreach ($final_result_qcount as $employee_id => $q) {
            $final_result_temp[$employee_id] = [
                'employee_id' => $employee_id,
                'full_name'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['full_name'],
                'birth_date'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['birth_date'],
                'grade_name'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_name'],
                'grade_code'   => $result[array_search($employee_id, array_column($result, 'employee_id'))]['grade_code'],
                'score'       => 0,
                'final_score' => 0,
                'details'     => []
            ];

            // Calculate the question-wise averages and add to details
            foreach ($q as $question => $count) {
                /*
                * Konversi hardcode disini
                */
                $average_score = convert_scale($final_result_qsum[$employee_id][$question], '5', '3') / $count;
                $final_result_temp[$employee_id]['score'] += $average_score;

                $final_result_temp[$employee_id]['details'][] = [
                    'question' => $question,
                    'value'    => $average_score
                ];
            }

            // Calculate final_score and category
            /*
            * Konversi hardcode disini
            */
            // $final_result_temp[$employee_id]['final_score'] = ($final_result_temp[$employee_id]['score'] / $total_baseline) * 100;
            $final_result_temp[$employee_id]['final_score'] = ($final_result_temp[$employee_id]['score'] / 33) * 100;
            $final_result_temp[$employee_id]['category'] = get_category($final_result_temp[$employee_id]['final_score']);
        }

        // Step 3: Sort by final_score in descending order
        usort($final_result_temp, function ($a, $b) {
            // Compare by final_score (descending)
            if ($a['final_score'] !== $b['final_score']) {
                return $b['final_score'] <=> $a['final_score'];
            }

            // If final_score is the same, compare by grade_code (descending)
            if ($a['grade_code'] !== $b['grade_code']) {
                return $b['grade_code'] <=> $a['grade_code'];
            }

            // If both final_score and grade_code are the same, compare by birth_date (descending)
            return $b['birth_date'] <=> $a['birth_date'];
        });

        // Step 4: Add ranking
        foreach ($final_result_temp as $index => &$item) {
            $item['ranking'] = $index + 1;
        }

        $response = [
            'final_result' => $final_result_temp,
            'header'       => $final_head_p,
        ];

        return $response;
    }
}
