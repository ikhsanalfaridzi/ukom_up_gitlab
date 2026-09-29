<?php

namespace App\Controllers\Assessment;

use App\Controllers\KV_Controller;
use App\Models\Assessment\AspectQuestionModel;
use App\Models\Assessment\AssessmentComponentModel;
use App\Models\Assessment\ComponentAspectModel;
use App\Models\Assessment\QuestionInstrumentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

/**
 * REFACTOR NOTES (dibanding versi asli):
 * 1. getList(): ditambahkan pengecekan "komponen tidak ditemukan" (throw PageNotFoundException)
 *    -- sebelumnya kalau component_id tidak valid, halaman tetap dirender dengan $data = NULL.
 *    Aspect_question::getList() sudah punya pengecekan serupa; sekarang konsisten.
 * 2. postData(): hapus dobel cek is_authorized() -- getData() sudah mengecek izin yang sama.
 * 3. getData(): nilai yang ditampilkan (aspect) dan yang disisipkan ke onclick JS sekarang
 *    lewat esc()/esc(...,'js') -- versi asli rawan XSS.
 * 4. postCreate()/postEdit(): save()/update() dibungkus try/catch; ditambahkan set_log_access()
 *    setelah sukses (sebelumnya tidak tercatat di log akses sama sekali).
 * 5. postDelete(): sebelumnya tidak mengembalikan respons apa pun. Sekarang dibungkus
 *    parent::_execute() -> transaction, response JSON konsisten, log akses.
 * 6. BARU: postDelete_associated(). Sebelumnya file ini TIDAK punya endpoint cascade-delete,
 *    padahal 2 "saudaranya" (Assessment_component, Aspect_question) punya. Tanpa ini, menghapus
 *    aspek yang masih punya pertanyaan/instrumen di bawahnya berisiko gagal karena foreign key
 *    constraint (error mentah, tidak tertangani). Endpoint baru ini butuh route baru kalau mau
 *    dipakai, contoh:
 *      $routes->post('assessment/component_aspect/delete_associated/(:segment)',
 *          'Assessment\Component_aspect::postDelete_associated/$1');
 *    dan tombol aksi baru di view (mengikuti pola tombol "Delete Associated Data" yang sudah
 *    ada di Assessment_component).
 */
class Component_aspect extends KV_Controller
{
    protected $component_aspect;
    protected $assessment_component;
    protected $aspect_question;
    protected $question_instrument;

    function __construct()
    {
        parent::__construct(TRUE);

        $this->component_aspect = new ComponentAspectModel();
        $this->assessment_component = new AssessmentComponentModel();
        $this->aspect_question = new AspectQuestionModel();
        $this->question_instrument = new QuestionInstrumentModel();
        $this->_uri_name = 'assessment/component_aspect';
        $this->_menu_code = 'component_aspect';
    }

    public function getIndex()
    {
        return redirect()->to(site_url('assessment/assessment_component'));
    }

    public function getList($component_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Aspek Penilaian";

        $data = $this->assessment_component->select('id, name, description')->find($component_id);

        if (!$data) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('assessment/component_aspect/list', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
            'data'     => $data,
        ]);
    }

    public function postData($component_id)
    {
        return $this->getData($component_id);
    }

    public function getData($component_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $list = $this->component_aspect->get_datatables($component_id);
        $data = [];

        foreach ($list as $aspect) {
            $row    = [];
            $row[]  = esc($aspect->aspect);
            $row[]  = $aspect->is_active === "t" ? "Aktif" : "Tidak Aktif";
            $row[]  = $aspect->order;
            $row[]  = "
                <a href=" . site_url('assessment/aspect_question/list/' . $aspect->id) . " class='badge bg-info' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Detail'><i class='bx bx-search-alt-2'></i></a>
                <a href=" . site_url('assessment/component_aspect/form/' . $aspect->id) . " class='btn btn-warning btn-sm' data-toggle='tooltip' data-placement='bottom' title='Ubah'><i class='bx bx-edit'></i></a>
                <a href='javascript:void(0)' class='btn btn-danger btn-sm' onclick=\"delete_data('" . esc($aspect->id, 'js') . "','" . esc($aspect->aspect, 'js') . "')\" data-toggle='tooltip' data-placement='bottom' title='Hapus'><i class='bx bx-trash'></i></a>
            ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->component_aspect->count_all($component_id),
            'recordsFiltered'   => $this->component_aspect->count_filtered($component_id),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') && !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $title = "Form Aspek Penilaian";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "assessment/component_aspect/create"
        ];

        $assessment_component_id = $this->request->getGet('component_id');

        if ($id !== NULL) {
            $data['records'] = $this->component_aspect->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $data['subtitle'] = "Ubah Data";
            $data['url'] = "assessment/component_aspect/edit/" . $id;
            $assessment_component_id = $data['records']['assessment_component_id'];
        }

        return view('assessment/component_aspect/form', [
            "title"                   => $title,
            "uri_name"                => $this->_uri_name,
            "data"                    => $data,
            "assessment_component_id" => $assessment_component_id,
        ]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $data = $this->request->getPost();

        $this->validator->setRules($this->component_aspect->validationRules, $this->component_aspect->validationMessages);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        try {
            $data['id'] = $this->uuid;

            $this->component_aspect->save($data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/component_aspect/list/' . $data['assessment_component_id']))->with('message', 'Data berhasil ditambahkan');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('message', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function postEdit($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $data = $this->request->getPost();

        $aspect = $this->component_aspect->where(['id' => $id])->first();

        if (!$aspect) {
            session()->setFlashdata('message', 'Aspek tidak ditemukan');
            return redirect()->back()->withInput();
        }

        $this->validator->setRules($this->component_aspect->validationRules, $this->component_aspect->validationMessages);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        try {
            $this->component_aspect->update($id, $data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/component_aspect/list/' . $aspect['assessment_component_id']))->with('message', 'Data berhasil diubah');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('message', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function postDelete($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';

        return parent::_execute('this', 'delete_aspect', NULL, ['id' => $id]);
    }

    protected function delete_aspect($id)
    {
        $this->component_aspect->delete($id);

        return $id;
    }

    public function postDelete_associated($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';

        return parent::_execute('this', 'delete_aspect_and_related', NULL, ['id' => $id]);
    }

    /**
     * Hapus aspek beserta seluruh pertanyaan & instrumen jawaban di bawahnya.
     * Urutan dari level paling dalam ke luar, dibungkus 1 transaction oleh parent::_execute().
     */
    protected function delete_aspect_and_related($id)
    {
        $question_ids = array_column(
            $this->aspect_question->select('id')->where('component_aspect_id', $id)->findAll(),
            'id'
        );

        if (!empty($question_ids)) {
            $this->question_instrument->whereIn('aspect_question_id', $question_ids)->delete();
        }

        $this->aspect_question->where('component_aspect_id', $id)->delete();
        $this->component_aspect->delete($id);

        return $id;
    }
}