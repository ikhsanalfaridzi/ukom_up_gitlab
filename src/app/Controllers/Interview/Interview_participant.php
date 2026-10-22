<?php

/**
 * Grades Feature from Interview Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-19
 */

namespace App\Controllers\Interview;    

use App\Controllers\KV_Controller;
use App\Models\Interview\InterviewGroupModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\Interview\InterviewParticipantModel;

class Interview_participant extends KV_Controller
{
    protected $interview_group;
    protected $interview_participant;

    function __construct()
    {
        parent::__construct(TRUE);

        $this->_uri_name = 'interview_participant';
        $this->_uri_name_view = 'Interview/interview_participant';
        $this->_menu_code = 'interview_participant';
        $this->interview_group = new InterviewGroupModel();
        $this->interview_participant = new InterviewParticipantModel();
    }

    public function getList($id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Peserta Wawancara";

        $data = $this->interview_group->select('id, name, level')->find($id);

        return view('Interview/interview_participant/index', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
            'data'     => $data,
        ]);
    }

    public function getDetail($id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Peserta Wawancara";

        $data = $this->interview_group->select('id, name, level')->find($id);

        return view('Interview/interview_participant/detail', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
            'data'     => $data,
        ]);
    }

    public function postData($id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getData($id);
    }

    public function postList_data($id, $level = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getList_data($id, $level);
    }

    public function getData($id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $list = $this->interview_participant->get_datatables($id);
        $data = [];

        foreach ($list as $employee) {
            $row    = [];
            $row[]  = $employee->employee_number;
            $row[]  = $employee->full_name;
            $row[]  = $employee->grade_name;
            $row[]  = $employee->position_grade_name;
            $row[]  = $employee->position_name;
            $row[]  = "
                <a href='javascript:void(0)' class='btn btn-danger btn-sm' onclick=\"delete_data('" . $employee->id . "','" . $employee->full_name . "')\" data-toggle='tooltip' data-placement='bottom' title='Hapus'><i class='bx bx-trash'></i></a>
            ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->interview_participant->count_all($id),
            'recordsFiltered'   => $this->interview_participant->count_filtered($id),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getList_data($id, $level = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $eselon = get_eselon_level($level, false);

        $list = $this->interview_participant->get_datatables($id);
        $data = [];

        foreach ($list as $employee) {
            $row    = [];
            $row[]  = $employee->employee_number;
            $row[]  = $employee->full_name;
            $row[]  = $employee->grade_name;
            $row[]  = $employee->position_grade_name;
            $row[]  = $employee->position_name;
            $row[]  = "
                <a href=" . site_url('assessment/assessment/evaluation/' . $employee->employee_id . '/' . $eselon . '/' . $employee->id) . " class='badge bg-info' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Wawancara'><i class='bx bx-check-circle'></i></a>
                <a href=" . site_url('assessment/assessment/result/' . $employee->employee_id . '/' . $eselon . '/' . $employee->id) . " class='badge bg-success' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Hasil Wawancara'><i class='bx bx-book-open'></i></a>
                <a href=" . site_url('assessment/assessment/convert/' . $employee->employee_id . '/' . $eselon . '/' . $employee->id) . " class='badge bg-warning' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Convert'><i class='bx bx-transfer-alt'></i></a>
            ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->interview_participant->count_all($id),
            'recordsFiltered'   => $this->interview_participant->count_filtered($id),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getForm($interview_group_id, $id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') && !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $title = "Form Peserta wawancara";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "interview/interview_participant/create"
        ];

        $employees = $this->db->table('employee.personnel ep')
            ->select('ep.id, ipr.full_name')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->where('au.is_active', '1')
            ->where('ep.employee_number IS NOT NULL')
            ->where('ep.employee_number !=', '')
            ->where('ipr.full_name IS NOT NULL')
            ->where('ipr.full_name !=', '')
            ->orderBy('ipr.full_name', 'ASC')
            ->get()
            ->getResultArray();
        $employee_list = format_array($employees, 'full_name');

        if ($id !== NULL) {
            $data['records']    = $this->interview_participant->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $data['subtitle']   = "Ubah Data";
            $data['url']        = "interview/interview_participant/edit/" . $id;
        }

        return view('Interview/interview_participant/form', [
            "title"                => $title,
            "uri_name"             => $this->_uri_name,
            "data"                 => $data,
            "employee_list"        => $employee_list,
            "interview_group_id"   => $interview_group_id,
        ]);
    }

    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $data = $this->request->getPost();

        $existing = $this->interview_participant->where('interview_group_id', $data['interview_group_id'])
            ->where('employee_id', $data['employee_id'])
            ->first();

        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Pegawai sudah terdaftar di kelompok ini.');
        }

        $this->validator->setRules($this->interview_participant->validationRules, $this->interview_participant->validationMessages);

        if ($this->validator->run($data)) {
            $data['id'] = $this->uuid;

            $this->interview_participant->save($data);

            return redirect()->to(site_url('interview/interview_participant/list/' . $data['interview_group_id']))->with('message', 'Data berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput();
        }
    }

    public function postDelete($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        // $this->interview_participant->delete($id);
        try {
            $this->interview_participant->delete($id);
            log_message('debug', 'Data berhasil dihapus untuk ID: ' . $id);
        } catch (\Exception $e) {
            log_message('error', 'Kesalahan saat menghapus data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
