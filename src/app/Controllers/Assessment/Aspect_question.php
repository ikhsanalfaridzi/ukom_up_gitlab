<?php

namespace App\Controllers\Assessment;

use App\Controllers\KV_Controller;
use App\Models\Assessment\ComponentAspectModel;
use App\Models\Assessment\AspectQuestionModel;
use App\Models\Assessment\QuestionInstrumentModel;
use App\Models\Assessment\AssessmentComponentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class Aspect_question extends KV_Controller
{
    protected $aspect_question;
    protected $component_aspect;
    protected $assessment_component;
    protected $question_instrument;

    function __construct()
    {
        parent::__construct(TRUE);

        $this->aspect_question = new AspectQuestionModel();
        $this->component_aspect = new ComponentAspectModel();
        $this->assessment_component = new AssessmentComponentModel();
        $this->question_instrument = new QuestionInstrumentModel();
        $this->_uri_name = 'assessment/aspect_question';
        $this->_menu_code = 'aspect_question';
    }

    public function getIndex()
    {
        return redirect()->to(site_url('assessment/assessment_component'));
    }

    public function getList($aspect_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = "Detail Aspek Penilaian";

        $aspect = $this->component_aspect->select('id, assessment_component_id, aspect')->find($aspect_id);

        if (!$aspect) {
            throw PageNotFoundException::forPageNotFound();
        }

        $component = $this->assessment_component->select('id, name')->find($aspect['assessment_component_id']);

        $data = [
            'id'                      => $aspect['id'],
            'name'                    => $component['name'] ?? '-',
            'aspect'                  => $aspect['aspect'],
            'assessment_component_id' => $aspect['assessment_component_id'],
        ];

        return view('assessment/aspect_question/index', [
            'title'    => $title,
            'uri_name' => $this->_uri_name,
            'data'     => $data,
        ]);
    }

    /**
     * Sebelumnya postData() dan getData() sama-sama cek is_authorized() -- dobel
     * pengecekan untuk request yang sama. Cukup didelegasikan, izin tetap dicek
     * satu kali di dalam getData().
     */
    public function postData($aspect_id)
    {
        return $this->getData($aspect_id);
    }

    public function getData($aspect_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $list = $this->aspect_question->get_datatables($aspect_id);
        $data = [];

        foreach ($list as $question) {
            $row    = [];
            $row[]  = esc($question->question);
            $row[]  = esc($question->assessment_criteria);
            $row[]  = $question->level;
            $row[]  = $question->order;
            $row[]  = $question->is_active === "t" ? "Aktif" : "Tidak Aktif";
            // ID & teks pertanyaan disisipkan ke dalam string JS (onclick), jadi dipakai
            // esc(..., 'js') -- bukan cuma esc() HTML biasa -- supaya tanda kutip/karakter
            // spesial pada pertanyaan tidak bisa merusak/menyisipkan script (XSS).
            $row[]  = "
                <a href=" . site_url('assessment/question_instrument/list/' . $question->id) . " class='badge bg-info' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Detail'><i class='bx bx-search-alt-2'></i></a>
                <a href=" . site_url('assessment/aspect_question/form/' . $question->component_aspect_id . '/' . $question->id) . " class='btn btn-warning btn-sm' data-toggle='tooltip' data-placement='bottom' title='Ubah'><i class='bx bx-edit'></i></a>
                <a href='javascript:void(0)' class='btn btn-danger btn-sm' onclick=\"delete_data('" . esc($question->id, 'js') . "','" . esc($question->question, 'js') . "')\" data-toggle='tooltip' data-placement='bottom' title='Hapus'><i class='bx bx-trash'></i></a>
                <a href='javascript:void(0)' class='badge bg-secondary' onclick=\"delete_associated('" . esc($question->id, 'js') . "','" . esc($question->question, 'js') . "')\" data-bs-toggle='tooltip' data-bs-placement='bottom' title='Delete Associated Data'><i class='bx bx-x-circle'></i></a>
            ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->aspect_question->count_all($aspect_id),
            'recordsFiltered'   => $this->aspect_question->count_filtered($aspect_id),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getForm($aspect_id, $id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') && !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $title = "Form Pertanyaan";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "assessment/aspect_question/create"
        ];

        $component_aspect_id = $aspect_id;

        if ($id !== NULL) {
            $data['records'] = $this->aspect_question->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $data['subtitle'] = "Ubah Data";
            $data['url'] = "assessment/aspect_question/edit/" . $id;
            $component_aspect_id = $data['records']['component_aspect_id'];
        }

        return view('assessment/aspect_question/form', [
            "title"               => $title,
            "uri_name"            => $this->_uri_name,
            "data"                => $data,
            "component_aspect_id" => $component_aspect_id,
        ]);
    }

    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $data = $this->request->getPost();

        $this->validator->setRules($this->aspect_question->validationRules, $this->aspect_question->validationMessages);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        // Dibungkus try/catch: sebelumnya kalau save() gagal di level DB (bukan gagal
        // validasi), exception akan bocor mentah jadi halaman error 500, bukan pesan
        // yang bisa dipahami user.
        try {
            $data['id'] = $this->uuid;

            $this->aspect_question->save($data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/aspect_question/list/' . $data['component_aspect_id']))->with('message', 'Data berhasil ditambahkan');
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

        $question = $this->aspect_question->where(['id' => $id])->first();

        if (!$question) {
            session()->setFlashdata('message', 'Pertanyaan tidak ditemukan');
            return redirect()->back()->withInput();
        }

        $this->validator->setRules($this->aspect_question->validationRules, $this->aspect_question->validationMessages);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        try {
            $this->aspect_question->update($id, $data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/aspect_question/list/' . $question['component_aspect_id']))->with('message', 'Data berhasil diubah');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('message', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Sebelumnya method ini tidak mengembalikan respons apa pun -- kalau tombol hapus
     * memanggil ini lewat AJAX, user tidak pernah dapat konfirmasi sukses/gagal, dan
     * error dari database (misal FK constraint) akan bocor jadi halaman error mentah.
     * Sekarang dibungkus parent::_execute() -- otomatis dapat transaction, response
     * JSON konsisten (status/message), dan log akses -- persis pola di Groups.php.
     */
    public function postDelete($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';

        return parent::_execute('this', 'delete_question', NULL, ['id' => $id]);
    }

    protected function delete_question($id)
    {
        $this->aspect_question->delete($id);

        return $id;
    }

    public function postDelete_associated($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';

        return parent::_execute('this', 'delete_question_and_related', NULL, ['id' => $id]);
    }

    protected function delete_question_and_related($id)
    {
        $this->question_instrument->where('aspect_question_id', $id)->delete();
        $this->aspect_question->delete($id);

        return $id;
    }
}
