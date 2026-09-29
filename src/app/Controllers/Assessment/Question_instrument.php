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
 * Instrumen Penilaian (question_instrument) -- level paling bawah dari hierarki:
 * Komponen Penilaian -> Aspek -> Pertanyaan -> Instrumen.
 *
 * Referensi struktur dari Component_aspect versi lama; disesuaikan dengan konvensi
 * project sekarang: auth (is_authorized/access_denied), parent::_execute() untuk
 * transaction + response JSON konsisten, set_log_access(), esc()/esc(...,'js').
 *
 * Instrumen adalah level leaf, jadi delete & delete_associated berperilaku sama
 * (menghapus instrumen itu sendiri). Method tetap terpisah supaya UI konsisten
 * dengan halaman Komponen/Aspek/Pertanyaan.
 */
class Question_instrument extends KV_Controller
{
    protected $question_instrument;
    protected $aspect_question;
    protected $component_aspect;
    protected $assessment_component;

    public function __construct()
    {
        parent::__construct(TRUE);

        $this->question_instrument = new QuestionInstrumentModel();
        $this->aspect_question = new AspectQuestionModel();
        $this->component_aspect = new ComponentAspectModel();
        $this->assessment_component = new AssessmentComponentModel();
        $this->_uri_name = 'assessment/question_instrument';
        $this->_menu_code = 'aspect_question';
    }

    public function getIndex()
    {
        return redirect()->to(site_url('assessment/assessment_component'));
    }

    public function getList($aspect_question_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $question = $this->aspect_question->select('id, component_aspect_id, question, assessment_criteria, level')->find($aspect_question_id);

        if (!$question) {
            throw PageNotFoundException::forPageNotFound();
        }

        $aspect = $this->component_aspect->select('id, assessment_component_id, aspect')->find($question['component_aspect_id']);
        $component = $this->assessment_component->select('id, name')->find($aspect['assessment_component_id'] ?? NULL);

        $data = [
            'id'                    => $question['id'],
            'question'              => $question['question'],
            'assessment_criteria'   => $question['assessment_criteria'],
            'level'                 => $question['level'],
            'aspect'                => $aspect['aspect'] ?? '-',
            'component_name'        => $component['name'] ?? '-',
            'component_aspect_id'   => $question['component_aspect_id'],
        ];

        $title = 'Instrumen Penilaian';

        return view('assessment/question_instrument/list', [
            'title'     => $title,
            'uri_name'  => $this->_uri_name,
            'data'      => $data,
        ]);
    }

    public function postData($aspect_question_id)
    {
        return $this->getData($aspect_question_id);
    }

    public function getData($aspect_question_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $list = $this->question_instrument->get_datatables($aspect_question_id);
        $data = [];

        foreach ($list as $instrument) {
            $row    = [];
            $row[]  = esc($instrument->instrument);
            $row[]  = $instrument->value;
            $row[]  = "
                <a href=" . site_url('assessment/question_instrument/form/' . $instrument->aspect_question_id . '/' . $instrument->id) . " class='btn btn-warning btn-sm' data-toggle='tooltip' data-placement='bottom' title='Ubah'><i class='bx bx-edit'></i></a>
                <a href='javascript:void(0)' class='btn btn-danger btn-sm' onclick=\"delete_data('" . esc($instrument->id, 'js') . "','" . esc($instrument->instrument, 'js') . "')\" data-toggle='tooltip' data-placement='bottom' title='Hapus'><i class='bx bx-trash'></i></a>
                <a href='javascript:void(0)' class='badge bg-secondary' onclick=\"delete_associated('" . esc($instrument->id, 'js') . "','" . esc($instrument->instrument, 'js') . "')\" data-bs-toggle='tooltip' data-bs-placement='bottom' title='Delete Associated Data'><i class='bx bx-x-circle'></i></a>
            ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->question_instrument->count_all($aspect_question_id),
            'recordsFiltered'   => $this->question_instrument->count_filtered($aspect_question_id),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getForm($aspect_question_id, $id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') && !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $question = $this->aspect_question->select('id, question')->find($aspect_question_id);

        if (!$question) {
            throw PageNotFoundException::forPageNotFound();
        }

        $title = "Form Instrumen Penilaian";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "assessment/question_instrument/create"
        ];

        if ($id !== NULL) {
            $data['records'] = $this->question_instrument->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $data['subtitle']   = "Ubah Data";
            $data['url']        = "assessment/question_instrument/edit/" . $id;
            $aspect_question_id = $data['records']['aspect_question_id'];
        }

        return view('assessment/question_instrument/form', [
            "title"               => $title,
            "uri_name"            => $this->_uri_name,
            "data"                => $data,
            "aspect_question_id"  => $aspect_question_id,
            "question"            => $question,
        ]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $data = $this->request->getPost();
        $data['value'] = str_replace(',', '.', $data['value'] ?? '');

        $this->validator->setRules($this->question_instrument->validationRules, $this->question_instrument->validationMessages);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        try {
            $data['id'] = $this->uuid;
            $this->question_instrument->save($data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/question_instrument/list/' . $data['aspect_question_id']))->with('message', 'Data berhasil ditambahkan');
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
        $data['value'] = str_replace(',', '.', $data['value'] ?? '');

        $instrument = $this->question_instrument->where(['id' => $id])->first();

        if (!$instrument) {
            session()->setFlashdata('message', 'Instrumen tidak ditemukan');
            return redirect()->back()->withInput();
        }

        $this->validator->setRules($this->question_instrument->validationRules, $this->question_instrument->validationMessages);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        try {
            $this->question_instrument->update($id, $data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/question_instrument/list/' . $instrument['aspect_question_id']))->with('message', 'Data berhasil diubah');
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

        return parent::_execute('this', 'delete_instrument', NULL, ['id' => $id]);
    }

    protected function delete_instrument($id)
    {
        $this->question_instrument->delete($id);

        return $id;
    }

    public function postDelete_associated($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';

        return parent::_execute('this', 'delete_instrument_associated', NULL, ['id' => $id]);
    }

    /**
     * Instrumen tidak punya data anak, jadi "delete associated" sama dengan hapus
     * instrumen itu sendiri. Method terpisah supaya alur UI konsisten dengan
     * halaman Komponen/Aspek/Pertanyaan.
     */
    protected function delete_instrument_associated($id)
    {
        $this->question_instrument->delete($id);

        return $id;
    }
}