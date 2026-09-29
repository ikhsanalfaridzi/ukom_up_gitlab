<?php

/**
 * Personnel Feature from Employee Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-08
 */

namespace App\Controllers\EmployeeManagement;

use App\Controllers\KV_Controller;
use App\Models\EmployeeManagement\EmployeeModel;
use App\Models\Ukom\GradeModel;
use App\Models\Ukom\PositionModel;
use App\Models\Instance\PositionGradeModel;
use App\Models\Assessment\RawAssessmentModel;
use App\Models\Assessment\StagingAssessmentModel;
use App\Models\Assessment\AssessmentResultModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class Employee extends KV_Controller
{
    protected $employee;
    protected $grade;
    protected $position;
    protected $position_grade;
    protected $raw_assessment;
    protected $staging_assesment;
    protected $assesment_result;
    protected $validator;
    protected $uuid;

    function __construct()
    {
        $this->employee = new EmployeeModel();
        $this->grade = new GradeModel();
        $this->position = new PositionModel();
        $this->position_grade = new PositionGradeModel();
        $this->raw_assessment = new RawAssessmentModel();
        $this->staging_assesment = new StagingAssessmentModel();
        $this->assesment_result = new AssessmentResultModel();
        $this->validator = service('validation');
        $this->uuid = generate_uuid() . time();
        $this->_uri_name = 'employee/personnel';
        $this->_uri_name_view = 'employee/personnel';
    }

    public function getIndex()
    {
        $title = "Data Pegawai";

        return view('employee/personnel/index', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
        ]);
    }

    public function getData()
    {
        $list = $this->employee->get_datatables(); 
        $data = [];

        foreach ($list as $employee) {
            $data[] = [
                'id'           => $employee->id,
                'username'     => $employee->employee_number,
                'full_name'    => $employee->full_name,
                'position'     => $employee->position_name ?? "-",
                'organization' => $employee->instance_name ?? "-",
                'is_active'    => $employee->is_active,
            ];
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : $draw,
            'recordsTotal'      => $this->employee->count_all(),
            'recordsFiltered'   => $this->employee->count_filtered(),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getForm($id = NULL)
    {
        $title = "Form Pegawai";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "employee/create"
        ];

        $grade = $this->grade->select('id, name')->findAll();
        $position = $this->position->select('id, name')->findAll();
        $position_grade = $this->position_grade->select('id, grade')->findAll();

        $grade_list = format_array($grade, 'name');
        $position_list = format_array($position, 'name');
        $position_grade_list = [];
        foreach ($position_grade as $pg) {
            $position_grade_list[$pg['id']] = $pg['grade'];
        }

        $personnel = [];
        $personal = [];
        $user = [];
        $group_ids = '';
        $organization_options = [];
        $position_options = [];
        $group_options = [];

        if ($id !== NULL) {
            $data['records']    = $this->employee->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $personnel = $data['records'];

            $data['subtitle']   = "Ubah Data";
            $data['url']        = "employee/edit/" . $id;
        }

        return view('employee/personnel/form', [
            "title"                => $title,
            "data"                 => $data,
            "grade_list"           => $grade_list,
            "position_list"        => $position_list,
            "position_grade_list"  => $position_grade_list,
            "uri_name"             => $this->_uri_name,
        ]);
    }

    public function postCreate()
    {
        $input = $this->request->getPost();

        $this->validator->setRules($this->employee->validationRules, $this->employee->validationMessages);

        if ($this->validator->run($input)) {
            $input['id'] = strval($this->uuid);

            $this->employee->save($input);

            return redirect()->to(site_url('employee'))->with('message', 'Data berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput();
        }
    }

    public function postEdit($id)
    {
        $input = $this->request->getPost();

        $credentials = ['id' => $id];

        $employee = $this->employee->where($credentials)->first();
        $input['id'] = $employee['id'];

        if ($employee) {
            $this->validator->setRules($this->employee->validationRules, $this->employee->validationMessages);

            if ($this->validator->run($input)) {
                $this->employee->update($id, $input);

                return redirect()->to(site_url('employee'))->with('message', 'Data berhasil diubah');
            } else {
                return redirect()->back()->withInput();
            }
        } else {
            session()->setFlashdata('message', 'Error ID');
            return redirect()->back()->withInput();
        }
    }

    public function postDelete($id)
    {
        $data = $this->employee->find($id);

        $this->raw_assessment->where('employee_id', $id)->delete();
        $this->staging_assesment->where('employee_id', $id)->delete();
        $this->assesment_result->where('employee_id', $id)->delete();
        $this->employee->delete($id);
    }
}