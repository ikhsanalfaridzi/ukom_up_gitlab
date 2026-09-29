<?php

namespace App\Controllers\User;

use App\Models\User\UserModel;
use App\Controllers\KV_Controller;
use App\Models\Assessment\RawAssessmentModel;
use App\Models\Assessment\StagingAssessmentModel;
use App\Models\Assessment\AssessmentResultModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class User extends KV_Controller
{
    protected $user;
    protected $raw_assessment;
    protected $staging_assessment;
    protected $assessment_result;
    protected $validator;
    protected $uuid;

    function __construct()
    {
        $this->user = new UserModel();
        $this->raw_assessment = new RawAssessmentModel();
        $this->staging_assessment = new StagingAssessmentModel();
        $this->assessment_result = new AssessmentResultModel();
        $this->validator = service('validation');
        $this->uuid = generate_uuid();
        $this->_uri_name = 'user/user';
    }

    public function getIndex()
    {
        $title = "Daftar Pengguna";

        return view('user/index', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
        ]);
    }

    public function postData()
    {
        return $this->getData();
    }

    public function getData()
    {
        $list = $this->user->get_datatables();
        $no = $this->request->getVar('start');
        $data = [];

        foreach ($list as $data_user) {
            $row    = [];
            $row[]  = ++$no;
            $row[]  = $data_user->nama_lengkap;
            $row[]  = $data_user->username;
            $row[]  = $data_user->email;
            $row[]  = $data_user->role;
            $row[]  = "
            <a href=" . site_url('user/user/form/' . $data_user->id) . " class='btn btn-warning btn-sm' data-toggle='tooltip' data-placement='bottom' title='Ubah'><i class='bx bx-edit'></i></a>
            <a href='javascript:void(0)' class='btn btn-danger btn-sm' onclick=\"delete_data('" . $data_user->id . "','" . $data_user->nama_lengkap . "')\" data-toggle='tooltip' data-placement='bottom' title='Hapus'><i class='bx bx-trash'></i></a>
        ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->user->count_all(),
            'recordsFiltered'   => $this->user->count_filtered(),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getForm($id = NULL)
    {
        $title = "Form Pengguna";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "user/user/create"
        ];

        if ($id !== NULL) {
            $data['records']    = $this->user->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $data['subtitle']   = "Ubah Data";
            $data['url']        = "user/user/edit/" . $id;
        }

        return view('user/form', [
            "title"    => $title,
            "data"     => $data,
            "uri_name" => $this->_uri_name,
        ]);
    }

    public function postCreate()
    {
        $input = $this->request->getPost();

        $this->validator->setRules($this->user->validationRules, $this->user->validationMessages);
        $this->validator->setRule('username', 'Username', 'required|min_length[4]|max_length[100]|regex_match[^[a-zA-Z0-9]+$]|is_unique[users.username]');
        $this->validator->setRule('email', 'Email', 'required|min_length[2]|max_length[100]|valid_email|is_unique[users.email]');
        $this->validator->setRule('password', 'Password', 'required|min_length[8]|max_length[100]');

        if ($this->validator->run($input)) {
            $input['id'] = strval($this->uuid);

            $this->user->save($input);

            return redirect()->to(site_url('user/user'))->with('message', 'Data berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput();
        }
    }

    public function postEdit($id)
    {
        $input = $this->request->getPost();

        $credentials = ['id' => $id];

        $data_user = $this->user->where($credentials)->first();

        $data = [
            'nama_lengkap'  => $input['nama_lengkap'],
            'email'         => $input['email'],
            'username'      => $input['username'],
            'role'          => $input['role'],
        ];

        if ($input['password'] !== '') {
            $data['password']           = $input['password'];
            $data['confirm_password']   = $input['password'];
        }

        if ($data_user) {
            $this->validator->setRules($this->user->validationRules, $this->user->validationMessages);
            $this->validator->setRule('username', 'Username', 'required|min_length[4]|max_length[100]|regex_match[^[a-zA-Z0-9]+$]|is_unique[users.username,id,' . $id . ']');
            $this->validator->setRule('email', 'Email', 'required|min_length[2]|max_length[100]|valid_email|is_unique[users.email,id,' . $id . ']');

            if ($this->validator->run($input)) {
                $this->user->update($id, $data);

                return redirect()->to(site_url('user/user'))->with('message', 'Data berhasil diubah');
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
        $this->raw_assessment->where('user_id', $id)->delete();
        $this->staging_assessment->where('user_id', $id)->delete();
        $this->assessment_result->where('user_id', $id)->delete();
        $this->user->delete($id);
    }
}
