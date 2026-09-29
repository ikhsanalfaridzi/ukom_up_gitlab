<?php

namespace App\Controllers\Interview;

use App\Controllers\KV_Controller;
use App\Models\Interview\EmployeeInterviewResponseModel;
use App\Models\Employee\PersonnelModel;

class Interview_employee extends KV_Controller
{
    protected $response_model;
    protected $personnel;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->response_model = new EmployeeInterviewResponseModel();
        $this->personnel      = new PersonnelModel();
        $this->_uri_name      = 'interview_employee';
        $this->_uri_name_view = 'Interview/interview_employee';
        $this->_menu_code     = 'interview_employee';
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $employee_id = session()->get('login_personnel_id');

        $employee = $this->db->table('employee.personnel ep')
            ->select('ep.id, ep.employee_number, ip.full_name, g.grade as grade_name, pos.position as position_name')
            ->join('identity.personal ip', 'ip.employee_personnel_id = ep.id', 'left')
            ->join('instance.positions pos', 'pos.id = ep.position_id', 'left')
            ->join('employee.grades g', 'g.id = pos.grade_id', 'left')
            ->where('ep.id', $employee_id)
            ->get()
            ->getRowArray();

        $questions = $this->db->table('assessment.aspect_question aq')
            ->select('aq.id, aq.question, aq.assessment_criteria, ca.aspect, ac.name as component_name')
            ->join('assessment.component_aspect ca', 'ca.id = aq.component_aspect_id', 'left')
            ->join('assessment.assessment_component ac', 'ac.id = ca.assessment_component_id', 'left')
            ->where('aq.is_active', true)
            ->where('ac.is_active', true)
            ->orderBy('ac.order, ca.order, aq.order')
            ->get()
            ->getResultArray();

        $existing = $this->response_model->where('employee_id', $employee_id)->findAll();
        $existing_map = [];
        foreach ($existing as $row) {
            $existing_map[$row['aspect_question_id']] = $row['response'];
        }

        return view('Interview/interview_employee/index', [
            'title'         => 'Isi Wawancara',
            'uri_name'      => $this->_uri_name,
            'employee'      => $employee,
            'questions'     => $questions,
            'existing'      => $existing_map,
        ]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $employee_id = session()->get('login_personnel_id');
        $responses   = $this->request->getPost('response') ?? [];

        $this->response_model->where('employee_id', $employee_id)->delete();

        $rows = [];
        foreach ($responses as $question_id => $answer) {
            if (!empty(trim($answer))) {
                $rows[] = [
                    'id'                  => generate_uuid(),
                    'employee_id'         => $employee_id,
                    'aspect_question_id'  => $question_id,
                    'response'            => $answer,
                ];
            }
        }

        if (!empty($rows)) {
            $this->response_model->insertBatch($rows);
        }

        return redirect()->to('interview_employee')->with('success', 'Jawaban wawancara berhasil disimpan.');
    }
}
