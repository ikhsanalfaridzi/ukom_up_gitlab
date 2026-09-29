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
 * 1. postData(): hapus dobel cek is_authorized() -- getData() sudah mengecek izin yang sama.
 * 2. getData(): nilai yang ditampilkan (name, description) dan yang disisipkan ke onclick JS
 *    sekarang lewat esc()/esc(...,'js') -- versi asli rawan XSS kalau nama/deskripsi komponen
 *    mengandung karakter HTML/kutip.
 * 3. getData(): 'draw' sekarang di-cast ke (int), konsisten dengan controller assessment lain.
 * 4. postCreate()/postEdit(): aturan unique-name diekstrak ke name_validation_rule() supaya
 *    tidak ditulis dobel; strip_tags() sekarang aman kalau field description tidak dikirim;
 *    save()/update() dibungkus try/catch supaya error DB tidak bocor jadi halaman 500 mentah;
 *    typo pesan "berhsail" -> "berhasil"; ditambahkan set_log_access() setelah sukses
 *    (sebelumnya create/edit di sini tidak tercatat sama sekali di log akses).
 * 5. postDelete() & postDelete_associated(): sebelumnya tidak mengembalikan respons apa pun.
 *    Sekarang dibungkus parent::_execute() -> dapat transaction, response JSON konsisten,
 *    dan log akses, persis pola di Groups.php.
 * 6. postDelete_associated(): logic cascade delete yang tadinya bercabang 3x dengan early
 *    return diserupakan jadi satu alur linear (delete_component_and_related()) -- lebih
 *    ringkas, tetap aman karena whereIn()/where() pada set kosong tidak menghapus apa pun.
 */
class Assessment_component extends KV_Controller
{
    protected $assessment_component;
    protected $component_aspect;
    protected $aspect_question;
    protected $question_instrument;

    function __construct()
    {
        parent::__construct(TRUE);

        $this->assessment_component = new AssessmentComponentModel();
        $this->component_aspect = new ComponentAspectModel();
        $this->aspect_question = new AspectQuestionModel();
        $this->question_instrument = new QuestionInstrumentModel();
        $this->_uri_name = 'assessment/assessment_component';
        $this->_menu_code = 'assessment_component';
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = 'Komponen Penilaian';

        return view('assessment/assessment_component/index', [
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
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $list = $this->assessment_component->get_datatables();
        $data = [];

        foreach ($list as $component) {
            $row    = [];
            $row[]  = esc($component->name);
            $row[]  = $component->is_active === "t" ? "Aktif" : "Tidak Aktif";
            $row[]  = $component->order;
            $row[]  = esc($component->description);
            $row[]  = "
            <a href=" . site_url('assessment/component_aspect/list/' . $component->id)  . " class='badge bg-info' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Details'><i class='bx bx-search-alt-2'></i></a>
            <a href=" . site_url('assessment/assessment_component/form/' . $component->id) . " class='btn btn-warning btn-sm' data-toggle='tooltip' data-placement='bottom' title='Ubah'><i class='bx bx-edit'></i></a>
            <a href='javascript:void(0)' class='btn btn-danger btn-sm' onclick=\"delete_data('" . esc($component->id, 'js') . "','" . esc($component->name, 'js') . "')\" data-toggle='tooltip' data-placement='bottom' title='Hapus'><i class='bx bx-trash'></i></a>
            <a href='javascript:void(0)' class='badge bg-secondary' onclick=\"delete_associated('" . esc($component->id, 'js') . "','" . esc($component->name, 'js') . "')\" data-bs-toggle='tooltip' data-bs-placement='bottom' title='Delete Associated Data'><i class='bx bx-x-circle'></i></a>
            ";
            $data[] = $row;
        }

        $draw = $this->request->getVar('draw');
        $response = [
            'draw'              => is_null($draw) ? 1 : (int) $draw,
            'recordsTotal'      => $this->assessment_component->count_all(),
            'recordsFiltered'   => $this->assessment_component->count_filtered(),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getForm($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert') && !is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $title = "Form Komponen";
        $data = [
            "subtitle"  => "Tambah Data",
            "records"   => NULL,
            "url"       => "assessment/assessment_component/create"
        ];

        if ($id !== NULL) {
            $data['records']    = $this->assessment_component->find($id);

            if (!$data['records']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $data['subtitle']   = "Ubah Data";
            $data['url']        = "assessment/assessment_component/edit/" . $id;
        }

        return view('assessment/assessment_component/form', [
            "title"    => $title,
            "data"     => $data,
            "uri_name" => $this->_uri_name,
        ]);
    }

    /**
     * Aturan validasi nama komponen (harus unik). Diekstrak dari postCreate()/postEdit()
     * supaya pesan error & rule-nya tidak ditulis dua kali secara terpisah.
     *
     * @param string|null $except_id ID yang dikecualikan dari pengecekan unik (dipakai saat edit).
     */
    private function name_validation_rule($except_id = NULL): array
    {
        $unique_rule = 'is_unique[assessment_component.name'
            . (!empty($except_id) ? ',id,' . $except_id : '')
            . ']';

        return [
            'label'  => 'Nama Komponen',
            'rules'  => 'required|min_length[2]|max_length[100]|' . $unique_rule,
            'errors' => [
                'required'      => 'Nama Komponen harus diisi',
                'min_length'    => 'Nama Komponen Minimal memiliki {param} karakter',
                'max_length'    => 'Nama Komponen Maksimal memiliki {param} karakter',
                'is_unique'     => 'Nama Komponen sudah digunakan',
            ],
        ];
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $data = $this->request->getPost();
        $data['description'] = strip_tags($data['description'] ?? '');

        $this->validator->setRules($this->assessment_component->validationRules, $this->assessment_component->validationMessages);

        $rule = $this->name_validation_rule();
        $this->validator->setRule('name', $rule['label'], $rule['rules'], $rule['errors']);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        try {
            $data['id'] = $this->uuid;
            $this->assessment_component->save($data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/assessment_component'))->with('message', 'Data berhasil ditambahkan');
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
        $data['description'] = strip_tags($data['description'] ?? '');

        $component = $this->assessment_component->where(['id' => $id])->first();

        if (!$component) {
            session()->setFlashdata('message', 'Komponen tidak ditemukan');
            return redirect()->back()->withInput();
        }

        $this->validator->setRules($this->assessment_component->validationRules, $this->assessment_component->validationMessages);

        $rule = $this->name_validation_rule($id);
        $this->validator->setRule('name', $rule['label'], $rule['rules'], $rule['errors']);

        if (!$this->validator->run($data)) {
            return redirect()->back()->withInput();
        }

        try {
            $this->assessment_component->update($id, $data);

            if (session()->get('login_user_id')) {
                $this->set_log_access();
            }

            return redirect()->to(site_url('assessment/assessment_component'))->with('message', 'Data berhasil diubah');
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

        return parent::_execute('this', 'delete_component', NULL, ['id' => $id]);
    }

    protected function delete_component($id)
    {
        $this->assessment_component->delete($id);

        return $id;
    }

    public function postDelete_associated($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';

        return parent::_execute('this', 'delete_component_and_related', NULL, ['id' => $id]);
    }

    /**
     * Hapus komponen beserta seluruh data anak (aspek -> pertanyaan -> instrumen jawaban).
     * Urutan dari level paling dalam ke luar supaya tidak melanggar foreign key. Seluruh
     * langkah ini dibungkus 1 transaction oleh parent::_execute() lewat postDelete_associated().
     */
    protected function delete_component_and_related($id)
    {
        $aspect_ids = array_column(
            $this->component_aspect->select('id')->where('assessment_component_id', $id)->findAll(),
            'id'
        );

        if (!empty($aspect_ids)) {
            $question_ids = array_column(
                $this->aspect_question->select('id')->whereIn('component_aspect_id', $aspect_ids)->findAll(),
                'id'
            );

            if (!empty($question_ids)) {
                $this->question_instrument->whereIn('aspect_question_id', $question_ids)->delete();
            }

            $this->aspect_question->whereIn('component_aspect_id', $aspect_ids)->delete();
            $this->component_aspect->whereIn('id', $aspect_ids)->delete();
        }

        $this->assessment_component->delete($id);

        return $id;
    }
}