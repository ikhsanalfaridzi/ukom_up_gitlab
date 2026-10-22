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
    private $group_result_cache = [];

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

    /**
     * Ringkasan statistik UKKJ & PDJL untuk Laman.
     * Lulus = final_score MS (>= 80), selain itu tidak lulus.
     */
    public function get_summary($start = NULL, $end = NULL)
    {
        $builder = $this->db->table('assessment.interview_groups ig')
            ->select('ig.id, ig.name, ig.level, ig.jenis, ig.created_at')
            ->orderBy('ig.created_at', 'ASC');

        if (!empty($start)) {
            $builder->where('ig.created_at >=', $start . ' 00:00:00');
        }

        if (!empty($end)) {
            $builder->where('ig.created_at <=', $end . ' 23:59:59');
        }

        $groups = $builder->get()->getResultArray();

        $empty = [
            'pendaftar'     => 0,
            'dinilai'       => 0,
            'lulus'         => 0,
            'tidak_lulus'   => 0,
            'belum_dinilai' => 0,
            'kelompok'      => 0,
        ];

        $summary = [
            'UKKJ'    => $empty,
            'PDJL'    => $empty,
            'periods' => [],
        ];

        $participants = [];
        $rows = $this->db->table('assessment.interview_participants')
            ->select('interview_group_id, COUNT(DISTINCT employee_id) AS total')
            ->groupBy('interview_group_id')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $participants[$row['interview_group_id']] = (int) $row['total'];
        }

        foreach ($groups as $group) {
            $jenis  = $group['jenis'] === 'PDJL' ? 'PDJL' : 'UKKJ';
            $period = !empty($group['created_at']) ? substr($group['created_at'], 0, 7) : 'lainnya';

            if (!isset($summary['periods'][$period])) {
                $summary['periods'][$period] = ['UKKJ' => $empty, 'PDJL' => $empty];
            }

            $pendaftar = $participants[$group['id']] ?? 0;

            $result      = $this->get_group_result($group['id']);
            $dinilai     = 0;
            $lulus       = 0;
            $tidak_lulus = 0;

            foreach ($result['final_result'] as $item) {
                $dinilai++;

                if ((float) $item['final_score'] >= 80) {
                    $lulus++;
                } else {
                    $tidak_lulus++;
                }
            }

            $belum_dinilai = max(0, $pendaftar - $dinilai);

            $summary[$jenis]['pendaftar']     += $pendaftar;
            $summary[$jenis]['dinilai']       += $dinilai;
            $summary[$jenis]['lulus']         += $lulus;
            $summary[$jenis]['tidak_lulus']   += $tidak_lulus;
            $summary[$jenis]['belum_dinilai'] += $belum_dinilai;
            $summary[$jenis]['kelompok']++;

            $summary['periods'][$period][$jenis]['pendaftar']     += $pendaftar;
            $summary['periods'][$period][$jenis]['dinilai']       += $dinilai;
            $summary['periods'][$period][$jenis]['lulus']         += $lulus;
            $summary['periods'][$period][$jenis]['tidak_lulus']   += $tidak_lulus;
            $summary['periods'][$period][$jenis]['belum_dinilai'] += $belum_dinilai;
            $summary['periods'][$period][$jenis]['kelompok']++;
        }

        ksort($summary['periods']);

        return $summary;
    }

    private function get_group_result($interview_group_id)
    {
        if (!isset($this->group_result_cache[$interview_group_id])) {
            $this->group_result_cache[$interview_group_id] = $this->get_datatables($interview_group_id);
        }

        return $this->group_result_cache[$interview_group_id];
    }

    private function target_jenjang($grade)
    {
        switch ($grade) {
            case 'Ahli Pertama':
                return 'Pertama ke Muda';
            case 'Ahli Muda':
                return 'Muda ke Madya';
            case 'Ahli Madya':
                return 'Madya ke Utama';
        }

        return 'Lainnya';
    }

    /**
     * Riwayat UKKJ: tren per tahun, rincian per gelombang, dan breakdown jenjang target.
     */
    public function get_ukkj_history($start = NULL, $end = NULL)
    {
        $builder = $this->db->table('assessment.interview_groups ig')
            ->select('ig.id, ig.name, ig.level, ig.created_at')
            ->where('ig.jenis', 'UKKJ')
            ->orderBy('ig.created_at', 'ASC');

        if (!empty($start)) {
            $builder->where('ig.created_at >=', $start . ' 00:00:00');
        }

        if (!empty($end)) {
            $builder->where('ig.created_at <=', $end . ' 23:59:59');
        }

        $groups = $builder->get()->getResultArray();

        if (empty($groups)) {
            return ['groups' => [], 'years' => [], 'jenjang' => []];
        }

        $group_ids = array_column($groups, 'id');

        $rows = $this->db->table('assessment.interview_participants ip')
            ->select('ip.interview_group_id, ip.employee_id, g.grade AS current_grade')
            ->join('employee.personnel ep', 'ep.id = ip.employee_id', 'left')
            ->join('instance.positions p', 'p.id = ep.position_id', 'left')
            ->join('employee.grades g', 'g.id = p.grade_id', 'left')
            ->whereIn('ip.interview_group_id', $group_ids)
            ->get()
            ->getResultArray();

        $participants = [];

        foreach ($rows as $row) {
            $participants[$row['interview_group_id']][$row['employee_id']] = $row['current_grade'];
        }

        $buckets = ['Pertama ke Muda', 'Muda ke Madya', 'Madya ke Utama', 'Lainnya'];

        $jenjang = array_fill_keys($buckets, 0);
        $years   = [];
        $result  = [];

        foreach ($groups as $group) {
            $year = !empty($group['created_at']) ? substr($group['created_at'], 0, 4) : '-';

            if (!isset($years[$year])) {
                $years[$year] = [
                    'pendaftar'     => 0,
                    'dinilai'       => 0,
                    'lulus'         => 0,
                    'tidak_lulus'   => 0,
                    'belum_dinilai' => 0,
                ];
            }

            $scores = [];

            foreach ($this->get_group_result($group['id'])['final_result'] as $item) {
                $scores[$item['employee_id']] = (float) $item['final_score'];
            }

            $group_participants = $participants[$group['id']] ?? [];
            $pendaftar          = count($group_participants);
            $dinilai            = 0;
            $lulus              = 0;
            $tidak_lulus        = 0;
            $group_jenjang      = array_fill_keys($buckets, 0);

            foreach ($group_participants as $employee_id => $grade) {
                $bucket = $this->target_jenjang($grade);

                $group_jenjang[$bucket]++;
                $jenjang[$bucket]++;

                if (isset($scores[$employee_id])) {
                    $dinilai++;

                    if ($scores[$employee_id] >= 80) {
                        $lulus++;
                    } else {
                        $tidak_lulus++;
                    }
                }
            }

            $belum_dinilai = max(0, $pendaftar - $dinilai);

            $years[$year]['pendaftar']     += $pendaftar;
            $years[$year]['dinilai']       += $dinilai;
            $years[$year]['lulus']         += $lulus;
            $years[$year]['tidak_lulus']   += $tidak_lulus;
            $years[$year]['belum_dinilai'] += $belum_dinilai;

            $result[] = [
                'id'             => $group['id'],
                'name'           => $group['name'],
                'year'           => $year,
                'date'           => !empty($group['created_at']) ? substr($group['created_at'], 0, 10) : '-',
                'pendaftar'      => $pendaftar,
                'dinilai'        => $dinilai,
                'lulus'          => $lulus,
                'tidak_lulus'    => $tidak_lulus,
                'belum_dinilai'  => $belum_dinilai,
                'jenjang'        => $group_jenjang,
            ];
        }

        usort($result, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        ksort($years);

        return [
            'groups'  => $result,
            'years'   => $years,
            'jenjang' => $jenjang,
        ];
    }

    /**
     * Riwayat PDJL: tren per tahun, rincian per gelombang, dan distribusi asal jabatan.
     */
    public function get_pdjl_history($start = NULL, $end = NULL)
    {
        $builder = $this->db->table('assessment.interview_groups ig')
            ->select('ig.id, ig.name, ig.level, ig.created_at')
            ->where('ig.jenis', 'PDJL')
            ->orderBy('ig.created_at', 'ASC');

        if (!empty($start)) {
            $builder->where('ig.created_at >=', $start . ' 00:00:00');
        }

        if (!empty($end)) {
            $builder->where('ig.created_at <=', $end . ' 23:59:59');
        }

        $groups = $builder->get()->getResultArray();

        if (empty($groups)) {
            return ['groups' => [], 'years' => [], 'jabatan' => []];
        }

        $group_ids = array_column($groups, 'id');

        $rows = $this->db->table('assessment.interview_participants ip')
            ->select('ip.interview_group_id, ip.employee_id, p.position AS position_name')
            ->join('employee.personnel ep', 'ep.id = ip.employee_id', 'left')
            ->join('instance.positions p', 'p.id = ep.position_id', 'left')
            ->whereIn('ip.interview_group_id', $group_ids)
            ->get()
            ->getResultArray();

        $participants = [];

        foreach ($rows as $row) {
            $participants[$row['interview_group_id']][$row['employee_id']] = $row['position_name'];
        }

        $jabatan = [];
        $years   = [];
        $result  = [];

        foreach ($groups as $group) {
            $year = !empty($group['created_at']) ? substr($group['created_at'], 0, 4) : '-';

            if (!isset($years[$year])) {
                $years[$year] = [
                    'pendaftar'     => 0,
                    'dinilai'       => 0,
                    'lulus'         => 0,
                    'tidak_lulus'   => 0,
                    'belum_dinilai' => 0,
                ];
            }

            $scores = [];

            foreach ($this->get_group_result($group['id'])['final_result'] as $item) {
                $scores[$item['employee_id']] = (float) $item['final_score'];
            }

            $group_participants = $participants[$group['id']] ?? [];
            $pendaftar          = count($group_participants);
            $dinilai            = 0;
            $lulus              = 0;
            $tidak_lulus        = 0;
            $group_jabatan      = [];

            foreach ($group_participants as $employee_id => $position) {
                $label = !empty($position) ? trim($position) : 'Tidak diketahui';

                $group_jabatan[$label] = ($group_jabatan[$label] ?? 0) + 1;
                $jabatan[$label]      = ($jabatan[$label] ?? 0) + 1;

                if (isset($scores[$employee_id])) {
                    $dinilai++;

                    if ($scores[$employee_id] >= 80) {
                        $lulus++;
                    } else {
                        $tidak_lulus++;
                    }
                }
            }

            $belum_dinilai = max(0, $pendaftar - $dinilai);

            $years[$year]['pendaftar']     += $pendaftar;
            $years[$year]['dinilai']       += $dinilai;
            $years[$year]['lulus']         += $lulus;
            $years[$year]['tidak_lulus']   += $tidak_lulus;
            $years[$year]['belum_dinilai'] += $belum_dinilai;

            $result[] = [
                'id'            => $group['id'],
                'name'          => $group['name'],
                'year'          => $year,
                'date'          => !empty($group['created_at']) ? substr($group['created_at'], 0, 10) : '-',
                'pendaftar'     => $pendaftar,
                'dinilai'       => $dinilai,
                'lulus'         => $lulus,
                'tidak_lulus'   => $tidak_lulus,
                'belum_dinilai' => $belum_dinilai,
                'jabatan'       => $group_jabatan,
            ];
        }

        arsort($jabatan);

        usort($result, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        ksort($years);

        return [
            'groups'  => $result,
            'years'   => $years,
            'jabatan' => $jabatan,
        ];
    }

    /**
     * Rata-rata nilai per komponen penilaian, dikelompokkan per jenis uji.
     */
    public function get_avg_component($start = NULL, $end = NULL)
    {
        $sql = "SELECT ac.name, ig.jenis, ROUND(AVG(sa.value)::numeric, 2) AS avg_score
                FROM assessment.staging_assessment sa
                JOIN assessment.component_aspect ca ON ca.id = sa.component_aspect_id
                JOIN assessment.assessment_component ac ON ac.id = ca.assessment_component_id
                JOIN assessment.interview_participants ip ON CAST(ip.employee_id AS VARCHAR) = CAST(sa.employee_id AS VARCHAR)
                JOIN assessment.interview_groups ig ON ig.id = ip.interview_group_id
                WHERE ig.jenis IN ('UKKJ', 'PDJL')";

        $params = [];

        if (!empty($start)) {
            $sql .= " AND ig.created_at >= ?";
            $params[] = $start . ' 00:00:00';
        }

        if (!empty($end)) {
            $sql .= " AND ig.created_at <= ?";
            $params[] = $end . ' 23:59:59';
        }

        $sql .= " GROUP BY ac.name, ig.jenis ORDER BY ac.name, ig.jenis";

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * Status verifikasi berkas peserta (employee.personnel.is_verified).
     */
    public function get_verification_status($start = NULL, $end = NULL)
    {
        $sql = "SELECT COALESCE(CAST(ep.is_verified AS VARCHAR), '0') AS verified, COUNT(*) AS total
                FROM (
                    SELECT DISTINCT ip.employee_id
                    FROM assessment.interview_participants ip
                    JOIN assessment.interview_groups ig ON ig.id = ip.interview_group_id
                    WHERE ig.jenis IN ('UKKJ', 'PDJL')";

        $params = [];

        if (!empty($start)) {
            $sql .= " AND ig.created_at >= ?";
            $params[] = $start . ' 00:00:00';
        }

        if (!empty($end)) {
            $sql .= " AND ig.created_at <= ?";
            $params[] = $end . ' 23:59:59';
        }

        $sql .= ") p JOIN employee.personnel ep ON ep.id = p.employee_id GROUP BY 1";

        $rows   = $this->db->query($sql, $params)->getResultArray();
        $result = ['terverifikasi' => 0, 'belum' => 0, 'total' => 0];

        foreach ($rows as $row) {
            $count = (int) $row['total'];

            $result['total'] += $count;

            if ((string) $row['verified'] === '1') {
                $result['terverifikasi'] += $count;
            } else {
                $result['belum'] += $count;
            }
        }

        return $result;
    }

    /**
     * Breakdown persebaran kelulusan & verifikasi berdasarkan instansi pembina
     * (instance.groups via unit kerja peserta) dan provinsi wilayah kerja.
     * Kegiatan yang unit kerja pesertanya tidak ter-resolve dialihkan ke
     * pemetaan instance_code (opsi gabungan).
     */
    public function get_region_breakdown($start = NULL, $end = NULL)
    {
        $builder = $this->db->table('assessment.interview_groups ig')
            ->select('ig.id, ig.name, ig.jenis, ig.instance_code, ig.verification_status, ig.created_at')
            ->whereIn('ig.jenis', ['UKKJ', 'PDJL']);

        if (!empty($start)) {
            $builder->where('ig.created_at >=', $start . ' 00:00:00');
        }

        if (!empty($end)) {
            $builder->where('ig.created_at <=', $end . ' 23:59:59');
        }

        $groups = $builder->get()->getResultArray();

        if (empty($groups)) {
            return ['instansi' => [], 'provinsi' => [], 'totals' => $this->empty_breakdown_total()];
        }

        $group_ids = array_column($groups, 'id');

        $participants = $this->db->table('assessment.interview_participants ip')
            ->select('ip.interview_group_id, ip.employee_id, gr.group AS group_name, prov.province_name AS province_name')
            ->join('employee.personnel ep', 'ep.id = ip.employee_id', 'left')
            ->join('instance.organization_structures os', 'os.id = ep.organization_structure_id', 'left')
            ->join('instance.organizations io', 'io.id = os.organization_id', 'left')
            ->join('instance.groups gr', 'gr.id = io.group_id', 'left')
            ->join('region.provinces prov', 'prov.id = io.province_id', 'left')
            ->whereIn('ip.interview_group_id', $group_ids)
            ->get()
            ->getResultArray();

        $group_members = [];

        foreach ($participants as $row) {
            $group_members[$row['interview_group_id']][] = $row;
        }

        $instansi = [];
        $provinsi = [];
        $totals   = $this->empty_breakdown_total();

        foreach ($groups as $group) {
            $members = $group_members[$group['id']] ?? [];

            $npm = $this->majority_label($members, 'group_name');
            $prov = $this->majority_label($members, 'province_name');

            $instansi_label = !empty($npm) ? $npm : $this->map_instance_code($group['instance_code']);
            $provinsi_label = !empty($prov) ? $prov : 'Tidak diketahui';

            $scores = [];

            foreach ($this->get_group_result($group['id'])['final_result'] as $item) {
                $scores[$item['employee_id']] = (float) $item['final_score'];
            }

            $stat = [
                'groups'        => 1,
                'peserta'       => count($members),
                'lulus'         => 0,
                'tidak_lulus'   => 0,
                'belum_dinilai' => 0,
                'verifikasi'    => $this->bucket_verification($group['verification_status']),
            ];

            foreach ($members as $member) {
                if (isset($scores[$member['employee_id']])) {
                    if ($scores[$member['employee_id']] >= 80) {
                        $stat['lulus']++;
                    } else {
                        $stat['tidak_lulus']++;
                    }
                } else {
                    $stat['belum_dinilai']++;
                }
            }

            $this->accumulate_breakdown($instansi, $instansi_label, $stat);
            $this->accumulate_breakdown($provinsi, $provinsi_label, $stat);
            $this->accumulate_breakdown_totals($totals, $stat);
        }

        uasort($instansi, fn($a, $b) => $b['peserta'] <=> $a['peserta']);
        uasort($provinsi, fn($a, $b) => $b['peserta'] <=> $a['peserta']);

        return [
            'instansi' => $instansi,
            'provinsi' => $provinsi,
            'totals'   => $totals,
        ];
    }

    private function empty_breakdown_total(): array
    {
        return [
            'groups'        => 0,
            'peserta'       => 0,
            'lulus'         => 0,
            'tidak_lulus'   => 0,
            'belum_dinilai' => 0,
            'verifikasi'    => ['draft' => 0, 'menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0],
        ];
    }

    private function bucket_verification($status): array
    {
        $allowed = ['draft', 'menunggu', 'disetujui', 'ditolak'];
        $key     = in_array($status, $allowed, TRUE) ? $status : 'menunggu';

        $bucket            = ['draft' => 0, 'menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0];
        $bucket[$key]      = 1;

        return $bucket;
    }

    private function accumulate_breakdown(array &$target, $label, array $stat): void
    {
        if (!isset($target[$label])) {
            $target[$label] = $this->empty_breakdown_total();
        }

        $this->accumulate_breakdown_totals($target[$label], $stat);
    }

    private function accumulate_breakdown_totals(array &$target, array $stat): void
    {
        $target['groups']        += $stat['groups'];
        $target['peserta']       += $stat['peserta'];
        $target['lulus']         += $stat['lulus'];
        $target['tidak_lulus']   += $stat['tidak_lulus'];
        $target['belum_dinilai'] += $stat['belum_dinilai'];

        foreach (['draft', 'menunggu', 'disetujui', 'ditolak'] as $key) {
            $target['verifikasi'][$key] += $stat['verifikasi'][$key];
        }
    }

    private function majority_label(array $members, string $field): ?string
    {
        $counts = [];

        foreach ($members as $member) {
            $value = $member[$field] ?? NULL;

            if (!empty($value)) {
                $counts[$value] = ($counts[$value] ?? 0) + 1;
            }
        }

        if (empty($counts)) {
            return NULL;
        }

        arsort($counts);

        return array_key_first($counts);
    }

    private function map_instance_code($code): string
    {
        $normalized = strtoupper(trim((string) $code));

        $map = [
            'IMIPAS'  => 'Imipas',
            'IM'      => 'Imigrasi',
            'PAS'     => 'Pemasyarakatan',
            'SETJEN'  => 'Sekretariat Jenderal',
            'IMIPAS1' => 'Imipas',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        return !empty($normalized) ? $normalized : 'Tidak diketahui';
    }
}
