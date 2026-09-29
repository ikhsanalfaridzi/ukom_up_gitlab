<?php

/**
 * Position Feature from Recap Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-04
 */

namespace App\Controllers\Recap;

use App\Controllers\KV_Controller;
use App\Models\Recap\RecapModel;
use App\Models\Assessment\RawAssessmentModel;
use App\Models\Assessment\AssessmentResultModel;
use App\Models\Interview\InterviewGroupModel;
use App\Models\Recap\RecapDetailModel;
use App\Models\Assessment\AspectQuestionModel;

class Recap extends KV_Controller
{
    protected $recap;
    protected $raw_assessment;
    protected $aspect_question;
    protected $assessment_result;
    protected $interview_group;
    protected $recap_detail;

    function __construct()
    {
        parent::__construct(TRUE);

        $this->recap = new RecapModel();
        $this->raw_assessment = new RawAssessmentModel();
        $this->aspect_question = new AspectQuestionModel();
        $this->assessment_result = new AssessmentResultModel();
        $this->interview_group = new InterviewGroupModel();
        $this->recap_detail = new RecapDetailModel();
        $this->_uri_name = 'recap/recap';
        $this->_menu_code = 'recap';
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Rekapitulasi";

        $aspect = $this->aspect_question->select('id, question')->orderBy('order', 'ASC')->findAll();

        return view('recap/recap/index', [
            'title'   => $title,
            'aspect'  => $aspect,
            'uri_name' => $this->_uri_name,
        ]);
    }

    public function getList($interview_group_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Rekapitulasi";

        $group = $this->interview_group->find($interview_group_id);

        $aspect = $this->aspect_question->select('id, question')
            ->where('level', $group['level'])
            ->orderBy('order', 'ASC')
            ->findAll();

        return view('recap/recap/list', [
            'title'              => $title,
            'aspect'             => $aspect,
            'group'              => $group,
            'interview_group_id' => $interview_group_id,
            'uri_name'           => $this->_uri_name,
        ]);
    }

    public function getDetail($interview_group_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Rekapitulasi";

        $group = $this->interview_group->find($interview_group_id);

        $data = $this->recap_detail->get_datatables($interview_group_id);

        return view('recap/recap/detail', [
            'title'              => $title,
            'data'               => $data,
            'group'              => $group,
            'interview_group_id' => $interview_group_id,
            'uri_name'           => $this->_uri_name,
        ]);
    }

    public function getAll()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Rekapitulasi Keseluruhan";

        return view('recap/recap/all', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
        ]);
    }

    public function aspect($eselon = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $builder = $this->aspect_question->select('id, question');

        if (!empty($eselon) && $eselon !== 'FUNG') {
            $level = get_eselon_level($eselon);
            $builder->where('level', $level)->orderBy('order', 'ASC');
            $aspect = $builder->findAll();
        } else {
            $rows = $builder->orderBy('level', 'ASC')->orderBy('order', 'ASC')->findAll();
            $aspect = [];
            $seen = [];
            foreach ($rows as $r) {
                if (!in_array($r['question'], $seen, true)) {
                    $seen[] = $r['question'];
                    $aspect[] = ['id' => $r['id'], 'question' => $r['question']];
                }
            }
        }

        return $this->response->setJSON($aspect);
    }

    public function getAspect($eselon = NULL)
    {
        return $this->aspect($eselon);
    }

    public function postData()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getData();
    }

    public function getData()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $list = $this->interview_group->get_datatables();
        $data = [];

        foreach ($list as $group) {
            $row    = [];
            $row[]  = $group->name;
            $row[]  = $group->level;
            $btn = "
                <a href=" . site_url('recap/recap/list/' . $group->id)  . " class='badge bg-info' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Details'><i class='bx bx-search-alt-2'></i></a>
                <a href=" . site_url('recap/recap/list_convert/' . $group->id)  . " class='badge bg-warning' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Convert'><i class='bx bx-transfer-alt'></i></a>
            ";

            if ((session()->get('logged_in')['role'] ?? '') === "Super Admin") {
                $btn .= "
                    <a href=" . site_url('recap/recap/detail/' . $group->id)  . " class='badge bg-danger' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Details'><i class='bx bx-briefcase-alt-2'></i></a>
                ";
            }

            $row[]  = $btn;
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->interview_group->count_all(),
            'recordsFiltered'   => $this->interview_group->count_filtered(),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function postList_data($interview_group_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getList_data($interview_group_id);
    }

    public function getList_data($interview_group_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $group = $this->interview_group->select('level')->find($interview_group_id);
        $eselon = get_eselon_level($group['level'], false);

        $list = $this->recap->get_datatables($interview_group_id);
        $data = [];

        foreach ($list['final_result'] as $recap) {
            $row    = [];
            $row[]  = $recap['ranking'];
            $row[]  = $recap['full_name'];
            $row[]  = $recap['grade_name'] ?? "-";
            $row[]  = calculate_age($recap['birth_date']);
            foreach ($recap['details'] as $question) {
                $row[] = number_format($question['value'], 2);
            }
            $row[]  = number_format($recap['score'], 2);
            $row[]  = number_format($recap['final_score'], 2);
            $row[]  = $recap['category'];
            $row[]  = "
            <a href=" . site_url('assessment/assessment/result/' . $recap['employee_id'] . '/' . $eselon) . " class='badge bg-success' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Hasil Wawancara'><i class='bx bx-book-open'></i></a>
        ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->recap->count_all($interview_group_id),
            'recordsFiltered'   => $this->recap->count_filtered($interview_group_id),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function postAll_data($eselon = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getAll_data($eselon);
    }

    public function getAll_data($eselon = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $list = $this->recap->get_all_data($eselon);
        $data = [];

        foreach ($list['final_result'] as $recap) {
            $row    = [];
            $row[]  = $recap['ranking'];
            $row[]  = $recap['full_name'];
            $row[]  = $recap['grade_name'] ?? "-";
            $row[]  = calculate_age($recap['birth_date']);
            foreach ($recap['details'] as $d) {
                $row[] = is_array($d) ? number_format($d['value'], 2) : (is_null($d) ? "-" : number_format($d, 2));
            }
            $row[]  = number_format($recap['score'], 2);
            $row[]  = number_format($recap['final_score'], 2);
            $row[]  = $recap['category'];
            $row[]  = "
                <a href=" . site_url('assessment/assessment/result/' . $recap['employee_id'] . '/' . $eselon) . " class='badge bg-success' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Hasil Wawancara'><i class='bx bx-book-open'></i></a>
            ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->recap->count_all_data($eselon),
            'recordsFiltered'   => $this->recap->count_filtered_data($eselon),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getList_convert($interview_group_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Rekapitulasi";

        $group = $this->interview_group->find($interview_group_id);

        $aspect = $this->aspect_question->select('id, question')
            ->where('level', $group['level'])
            ->orderBy('order', 'ASC')
            ->findAll();

        return view('recap/recap/list_convert', [
            'title'              => $title,
            'aspect'             => $aspect,
            'group'              => $group,
            'interview_group_id' => $interview_group_id,
            'uri_name'           => $this->_uri_name,
        ]);
    }

    public function postList_data_convert($interview_group_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getList_data_convert($interview_group_id);
    }

    public function getList_data_convert($interview_group_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $group = $this->interview_group->select('level')->find($interview_group_id);
        $eselon = get_eselon_level($group['level'], false);

        $list = $this->recap->get_convert_datatables($interview_group_id);
        $data = [];

        foreach ($list['final_result'] as $recap) {
            $row    = [];
            $row[]  = $recap['ranking'];
            $row[]  = $recap['full_name'];
            $row[]  = $recap['grade_name'] ?? "-";
            $row[]  = calculate_age($recap['birth_date']);
            foreach ($recap['details'] as $question) {
                $row[] = number_format($question['value'], 2);
            }
            $row[]  = number_format($recap['score'], 2);
            $row[]  = number_format($recap['final_score'], 2);
            $row[]  = $recap['category'];
            $row[]  = "
            <a href=" . site_url('assessment/assessment/convert/' . $recap['employee_id'] . '/' . $eselon) . " class='badge bg-warning' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Convert'><i class='bx bx-transfer-alt'></i></a>
        ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->recap->count_all($interview_group_id),
            'recordsFiltered'   => $this->recap->count_filtered($interview_group_id),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }
}
