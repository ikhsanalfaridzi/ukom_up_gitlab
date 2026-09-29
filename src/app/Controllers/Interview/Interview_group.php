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
use App\Models\Instance\OrganizationModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Interview_group extends KV_Controller
{
    protected $interview_group;
    protected $organization;

    function __construct()
    {
        parent::__construct(TRUE);

        $this->_uri_name = 'interview/interview_group';
        $this->_uri_name_view = 'Interview/interview_group';
        $this->_menu_code = 'interview_group';
        $this->interview_group = new InterviewGroupModel();
        $this->organization = new OrganizationModel();
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Kelompok Wawancara";

        return view('interview/interview_group/index', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
        ]);
    }

    public function getList()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Peserta Wawancara";

        return view('interview/interview_group/list', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
        ]);
    }

    public function postData()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getData();
    }

    public function postList_data()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return $this->getList_data();
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
            $row[]  = "
                <a href=" . site_url('interview/interview_participant/list/' . $group->id)  . " class='badge bg-info' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Details'><i class='bx bx-search-alt-2'></i></a>
                <a href=" . site_url('interview/interview_group/form/' . $group->id) . " class='btn btn-warning btn-sm' data-toggle='tooltip' data-placement='bottom' title='Ubah'><i class='bx bx-edit'></i></a>
                <a href='javascript:void(0)' class='btn btn-danger btn-sm' onclick=\"delete_data('" . $group->id . "','" . $group->name . "')\" data-toggle='tooltip' data-placement='bottom' title='Hapus'><i class='bx bx-trash'></i></a>
            ";
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

    public function getList_data()
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
            $row[]  = "
                <a href=" . site_url('interview/interview_participant/detail/' . $group->id)  . " class='btn btn-sm btn-icon btn-primary' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Detail Peserta'><i class='bx bx-search-alt-2'></i></a>
            ";
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

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') && !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $title = "Form Kelompok Wawancara";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "interview/interview_group/create"
        ];

        $group_list = format_array($this->interview_group->findAll(), 'name');

        $instance_code_list = $this->organization
            ->where('organization_code !=', NULL)
            ->where('organization_code !=', '')
            ->orderBy('organization', 'ASC')
            ->findAll();

        if ($id !== NULL) {
            $data['records']    = $this->interview_group->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $data['subtitle']   = "Ubah Data";
            $data['url']        = "interview/interview_group/edit/" . $id;
        }

        return view('interview/interview_group/form', [
            "title"           => $title,
            "uri_name"        => $this->_uri_name,
            "data"            => $data,
            "interview_group_list"   => $group_list,
            "instance_code_list"     => $instance_code_list,
        ]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $input = $this->request->getPost();

        $this->validator->setRules($this->interview_group->validationRules, $this->interview_group->validationMessages);

        if ($this->validator->run($input)) {
            $input['id'] = strval($this->uuid);

            $this->interview_group->save($input);

            return redirect()->to(site_url('interview/interview_group'))->with('message', 'Data berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput();
        }
    }

    public function postEdit($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $input = $this->request->getPost();

        $credentials = ['id' => $id];

        $group = $this->interview_group->where($credentials)->first();

        if ($group) {
            $this->validator->setRules($this->interview_group->validationRules, $this->interview_group->validationMessages);

            if ($this->validator->run($input)) {
                $this->interview_group->update($id, $input);

                return redirect()->to(site_url('interview/interview_group'))->with('message', 'Data berhasil diubah');
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
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->interview_group->delete($id);
    }
}
