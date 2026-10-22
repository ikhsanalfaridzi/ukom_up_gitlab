<?php

/**
 * Position Feature from Instance Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.1 (refactored)
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-04
 *
 * REFACTOR NOTES (dibanding versi asli):
 * 1. Menambahkan is_authorized() check yang hilang di getConvert(), getPdf(), getPdf_convert().
 * 2. postCreate() dibungkus parent::_execute() -> otomatis transBegin/transCommit/transRollback,
 *    jadi insert raw_assessment + hitung staging + hitung result jadi 1 transaksi utuh.
 * 3. Mengganti array_shift() berurutan dengan getPost('nama_field') -> tidak lagi bergantung
 *    urutan field di form HTML.
 * 4. process_results() sekarang inisialisasi $average_count / $average_value di awal method.
 *
 * CATATAN: bagian getResult()/getConvert()/getPdf()/getPdf_convert() -- termasuk semua kode
 * yang belum terpakai di dalamnya seperti $component_convert_tempt, $baseline, $total_baseline,
 * $max_score_target, $total_value_target -- SENGAJA DIBIARKAN PERSIS SEPERTI KODE ASLI dan
 * TIDAK disentuh/dipindah/digabung sama sekali, kecuali penambahan cek is_authorized() di
 * getConvert(), getPdf(), dan getPdf_convert() yang sebelumnya belum ada.
 */

namespace App\Controllers\Assessment;

use Dompdf\Dompdf;
use App\Controllers\KV_Controller;
use App\Models\Assessment\AssessmentModel;
use App\Models\Assessment\AssessmentComponentModel;
use App\Models\Assessment\ComponentAspectModel;
use App\Models\Assessment\AspectQuestionModel;
use App\Models\Assessment\QuestionInstrumentModel;
use App\Models\Assessment\AssessmentResultModel;
use App\Models\Assessment\RawAssessmentModel;
use App\Models\Assessment\StagingAssessmentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class Assessment extends KV_Controller
{
    protected $assessment;
    protected $assessment_component;
    protected $component_aspect;
    protected $aspect_question;
    protected $question_instrument;
    protected $raw_assessment;
    protected $staging_assessment;
    protected $assessment_result;
    protected $uuid;

    function __construct()
    {
        parent::__construct(TRUE);

        $this->assessment = new AssessmentModel();
        $this->assessment_component = new AssessmentComponentModel();
        $this->component_aspect = new ComponentAspectModel();
        $this->aspect_question = new AspectQuestionModel();
        $this->question_instrument = new QuestionInstrumentModel();
        $this->raw_assessment = new RawAssessmentModel();
        $this->staging_assessment = new StagingAssessmentModel();
        $this->assessment_result = new AssessmentResultModel();
        $this->_title          = 'Penilaian';
        $this->_uri_name       = 'assessment/assessment';
        $this->_uri_name_view  = 'assessment/assessment';
        $this->_menu_code      = 'assessment_scoring';
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        return parent::_index();
    }

    public function getData($eselon = NULL, $is_grade_active = 0)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        if (empty($eselon)) {
            $response = [
                'draw' => $this->request->getVar('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
            return $this->response->setJSON($response);
        }

        $list = $this->assessment->get_datatables($eselon, $is_grade_active);

        $data = [];
        foreach ($list as $employee) {
            $row = [];
            $row[] = $employee->employee_number ? esc($employee->employee_number) : '-';
            $row[] = $employee->full_name ? '<span class="fw-semibold">' . esc(strtoupper($employee->full_name)) . '</span>' : '-';
            $row[] = $employee->grade_name ? esc($employee->grade_name) : '-';
            $row[] = $employee->position_grade ? '<span class="badge bg-info rounded-pill">' . esc($employee->position_grade) . '</span>' : '-';
            $row[] = $employee->position_name ? esc($employee->position_name) : '-';
            $row[] = "
            <a href=" . site_url('assessment/assessment/evaluation/' . $employee->id . '/' . $eselon . '/0') . " class='badge bg-info rounded-pill' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Wawancara'><i class='bx bx-check-circle'></i></a>
            <a href=" . site_url('assessment/assessment/result/' . $employee->id) . " class='badge bg-success rounded-pill' data-bs-toggle='tooltip' data-bs-placement='bottom' title='Hasil Wawancara'><i class='bx bx-book-open'></i></a>
        ";

            $data[] = $row;
        }

        $response = [
            'draw'              => $this->request->getVar('draw'),
            'recordsTotal'      => $this->assessment->count_all($eselon, $is_grade_active),
            'recordsFiltered'   => $this->assessment->count_filtered($eselon, $is_grade_active),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function postData($eselon = NULL, $is_grade_active = 0)
    {
        return $this->getData($eselon, $is_grade_active);
    }

    public function getEvaluation($employee_id, $type, $interview_participant_id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $user_id = session()->get('login_user_id');

        $title = 'Pertanyaan Wawancara';
        $record = [
            "records" => NULL,
            "url"     => "assessment/assessment/create"
        ];

        $level = get_eselon_level($type);

        $employee_data = $this->get_employee_data($employee_id, $interview_participant_id);

        $aspect_question = $this->aspect_question->select('aspect_question.question, aspect_question.assessment_criteria, aspect_question.id as aspect_question_id, ca.aspect, ac.name as component')
            ->join('assessment.component_aspect ca', 'ca.id = aspect_question.component_aspect_id', 'left')
            ->join('assessment.assessment_component ac', 'ac.id = ca.assessment_component_id', 'left')
            ->orderBy('ac.order', 'ASC')
            ->orderBy('ca.order', 'ASC')
            ->where('aspect_question.level', $level)
            ->where('aspect_question.is_active', true)
            ->where('ca.is_active', true)
            ->where('ac.is_active', true)
            ->findAll();

        $data = [];
        foreach ($aspect_question as $item) {
            $component = $item['component'];
            $aspect = $item['aspect'];

            if (!isset($data[$component])) {
                $data[$component] = [];
            }

            if (!isset($data[$component][$aspect])) {
                $data[$component][$aspect] = [
                    'aspect'                => $item['aspect'],
                    'assessment_criteria'   => $item['assessment_criteria'],
                    'aspect_question_id'    => $item['aspect_question_id'],
                    'questions'             => [],
                ];
            }

            $data[$component][$aspect]['questions'][] = [
                'question'              => $item['question'],
                'assessment_criteria'   => $item['assessment_criteria'],
                'aspect_question_id'    => $item['aspect_question_id'],
                'question_instrument'   => $this->set_instrumen($item['aspect_question_id']),
            ];
        }

        return view('assessment/assessment/evaluation', [
            'title'                      => $title,
            'data'                       => $data,
            'record'                     => $record,
            'employee_data'              => $employee_data,
            'employee_id'                => $employee_id,
            'user_id'                    => $user_id,
            'type'                       => $type,
            'interview_participant_id'   => $interview_participant_id,
            'uri_name'                   => $this->_uri_name,
        ]);
    }

    private function get_employee_data($employee_id, $interview_participant_id = NULL)
    {
        $raw_assessment_cond = 'CAST(ra.employee_id AS VARCHAR) = CAST(ep.id AS VARCHAR)';

        if (!empty($interview_participant_id)) {
            $raw_assessment_cond .= ' AND ra.interview_participant_id = ' . $this->db->escape($interview_participant_id);
        }

        $builder = $this->db->table('employee.personnel ep')
            ->select('ep.id, ep.employee_number, ipr.full_name, ea.assortment as grade_name, ips.position as position_name, eg.grade as position_grade, io.organization as instance_name, ipr.date_of_birth as birth_date, ipr.photo_path as profile_image, ra.created_at, ra.type')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'au.id = ipr.acl_user_id', 'left')
            ->join('ukom.raw_assessment ra', $raw_assessment_cond, 'left')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('instance.organization_structures ios', 'CAST(ep.organization_structure_id AS VARCHAR) = CAST(ios.id AS VARCHAR)', 'left')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'left')
            ->where('ep.id', $employee_id);

        return $builder->get()->getRowArray();
    }

    private function set_instrumen($unsur_penilaian)
    {
        $query = $this->question_instrument->select('question_instrument.id, question_instrument.instrument, question_instrument.value, aq.question as question')
            ->join('assessment.aspect_question aq', 'aq.id = question_instrument.aspect_question_id')
            ->where('question_instrument.aspect_question_id', $unsur_penilaian)
            ->orderBy('question_instrument.value', 'DESC')
            ->findAll();

        $options = [];
        $options += ['' => ''];
        for ($i = 0; $i < count($query); $i++) {
            $options += [
                $query[$i]['id']  => $query[$i]['instrument'] . ' - (' . $query[$i]['value'] . ')',
            ];
        }

        return $options;
    }

    /**
     * Endpoint AJAX untuk menyimpan jawaban wawancara.
     *
     * Perubahan dari versi asli:
     * - Validasi input diambil by name (getPost('nama_field')), bukan array_shift() berurutan,
     *   supaya tidak rapuh terhadap perubahan urutan field di form.
     * - Seluruh proses (delete+insert raw_assessment, hitung staging, hitung result) dibungkus
     *   parent::_execute() sehingga jadi satu transaksi database yang utuh (auto rollback kalau
     *   ada satu langkah yang gagal).
     */
    public function postCreate()
    {

        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid Request']);
        }

        $this->message = 'Data berhasil disimpan';

        return parent::_execute('this', 'save_evaluation_answers', NULL, []);
    }

    /**
     * Logic penyimpanan jawaban wawancara, dipanggil di dalam transaction oleh _execute().
     * Method ini yang menggantikan bagian tengah postCreate() versi lama.
     */
    protected function save_evaluation_answers()
    {
        $user_id                  = $this->request->getPost('user_id');
        $employee_id               = $this->request->getPost('employee_id');
        $type                      = $this->request->getPost('type');
        $interview_participant_id  = $this->request->getPost('interview_participant_id');
        $evaluator                 = $this->request->getPost('evaluator');

        // Sisa field POST (di luar 5 field kontrol di atas) adalah jawaban per pertanyaan.
        $post = $this->request->getPost();
        unset(
            $post['user_id'],
            $post['employee_id'],
            $post['type'],
            $post['interview_participant_id'],
            $post['evaluator']
        );

        $hasil = format_raw($post, $type, $user_id, $employee_id, $evaluator, $interview_participant_id);

        $builder = $this->raw_assessment->where('employee_id', $employee_id)->where('user_id', $user_id);

        if (!empty($interview_participant_id)) {
            $builder->where('interview_participant_id', $interview_participant_id);
        }

        $builder->delete();

        $this->raw_assessment->insertBatch($hasil);

        $this->count_staging_assessment($type, $employee_id, $interview_participant_id);
        $this->count_assessment_result($type, $employee_id, $interview_participant_id);

        return [
            'employee_id' => $employee_id,
            'type'        => $type,
            'data'        => $hasil,
        ];
    }

    // public function getRecalculate()
    // {
    //     $employee_ids = [
    //         '03d4d428-1971-461d-8868-41e8e6f20cf81739674938',
    //         '0421ea3b-1ee5-42a0-aa02-a781c98fda3a1739754949',
    //         '06135075-e09c-3e56-bda0-780296725bd8',
    //         '08ce9428-be52-4d6b-8583-49b06c6f8b5c1739756467',
    //         '08eaf153-5b34-42ec-a856-e102de9d2c951739672863',
    //         '0b8c5a3f-31f1-4d66-b740-17f7fc6e849f1739672770',
    //         '18428740-68f9-432c-b16d-c37a43167b3b1739756918',
    //         '1974dc87-40a6-4872-824f-d39a5a24d40a1739753492',
    //         '218dc812-418b-4e10-a1b2-fad0ed38ffa51739754155',
    //         '24f39590-fa57-4bcf-a87c-8520553c156c1739673411',
    //         '2d7e9ae1-99f4-4dd4-ace0-c2eeb097e7e21739757743',
    //         '328f8c6f-1c56-36c9-b221-1c13a73900d4',
    //         '3a12c771-afd1-4072-817c-ecced93623201739674454',
    //         '3a1f2f57-fbbb-4330-b8c9-82459f875c211739753737',
    //         '3a67c62e-665b-4597-9fdc-6f3f592f6e741739756565',
    //         '3d2673ad-c54d-400a-89cc-26257127e9891739754092',
    //         '3e4c6e25-bf48-49ed-9589-98addae1964f1739754717',
    //         '3e6a8ac6-8e68-425e-927f-d8bcb52fe4981739676013',
    //         '4795737b-baf9-4b47-bdde-2d3df05afc231739675042',
    //         '4842da3e-5ce6-433c-8b28-92a950895da81739755194',
    //         '497a32b8-83db-4dc6-bd8f-8da8f968ffe61739754799',
    //         '4a60ad6b-136f-4c0b-b157-f3f19c400ebb1739673981',
    //         '4f972cd1-2372-494e-b369-9d462c6d66ed1739756654',
    //         '593fb7f5-188d-409a-8c65-b95f0fefaba71739683578',
    //         '5fc96e52-0e14-4e14-9492-5c5cd0abb84d1739757874',
    //         '63a2a138-1362-4efa-8e5d-8e6dda986e971739683688',
    //         '6454473e-f821-4854-be75-b58cb117d9561739757545',
    //         '690a0d0b-c672-4cc3-befe-305d38c805101739785691',
    //         '69ed8298-b9e3-4e45-8147-6a6a1c51153a1739754275',
    //         '6be0101e-95bf-48ea-9c14-a7d95d020e301739755037',
    //         '70fb198f-0e4c-4df8-8de1-500b4ff320071739757476',
    //         '79d7cf97-ef97-4e05-8ebe-b479fb5431551739757054',
    //         '85b5e9f3-186e-4a25-827a-bdb0f67a02051739684214',
    //         '87e79e46-ad1e-4401-88f6-f83a081f074e1739756326',
    //         '8e99d4be-cca5-487d-b436-216fc2efdf4f1739755287',
    //         '916913d7-15c6-4aa7-aff3-38e775ca9cbe1739684403',
    //         '99d91caf-eacf-35bf-a766-938c00d2a257',
    //         '9eaabd77-8db3-4ce8-bfee-6db11a3d4b961739673004',
    //         'a0b9bb1b-2a96-41fb-b7ee-30462faea14f1739756846',
    //         'a85af35c-0ac6-4ac4-b193-9d34c677e4821739754596',
    //         'b0faa0e7-2156-4991-9930-46de79facbd71739684679',
    //         'b19ea107-e908-4b21-9058-b0b04cb51b3e1739756768',
    //         'b3760172-6c9e-4733-8b9f-b0e6f5ccb8af1739684419',
    //         'b6ea6038-0e35-4866-840e-c28a608cce901739674801',
    //         'b7ecf411-4893-42d5-bb11-964cd6124acf1739757810',
    //         'b86ed6b8-408e-4f87-a3b6-38ca4e6fe15b1739672414',
    //         'bfcf8099-3555-39c2-bc3b-b2853d3cbcf5',
    //         'bff568a5-1d57-4761-96fa-20bc4402d37b1739754347',
    //         'c2ef7da2-9300-4d0b-9790-30bf9834fd681739753617',
    //         'c469903c-c29f-477c-9990-be9e3f68d0a81739677538',
    //         'c7debe6d-5d0f-39d6-875a-4a70e3f70da0',
    //         'c9b5cdaf-6960-42a5-823b-96cb9d46117b1739757649',
    //         'cd242393-b8a2-4d38-9d24-daa4817b9a281739755792',
    //         'd1dcc6c1-937a-4a87-a121-370f6b0558511739755479',
    //         'd1e86843-65ce-40da-81c8-5ad9ca7674d21739675432',
    //         'd6695470-a21f-4521-b03d-4b60f0da29c01739757365',
    //         'daf1bb77-a9d1-46d8-a503-eb34e152d9461739757938',
    //         'e1158c32-9947-4163-88b1-cb7bd76e7fd91739753379',
    //         'e23729a5-cf61-49b4-ad8e-cb30fdb909651739672286',
    //         'eb1aae2a-beea-4964-90fb-fb175eedaa3b1739753828',
    //         'eba53a58-199e-4195-907d-25847bf0ff4f1739755359',
    //         'eca424b0-ff98-3e05-b6d0-ad110b4c53ac',
    //         'ed13cb99-91d4-4ea9-89fa-86529e104c9d1739672967',
    //         'efe25a98-db79-48fb-b80f-a9d3e606c87a1739754006',
    //         'f7b4db57-e779-4200-98e2-b820b1e9caf41739675556',
    //         'f7fdea8e-569b-4efc-b530-f1b4f5d54b831739673018',
    //         'f98e8bf5-2134-4b87-8735-fd653098693c1739761411',
    //         'fcbc62a1-9a2d-4257-91f7-cddd5cf274f51739754878',
    //     ];

    //     foreach ($employee_ids as $employee_id) {
    //         $this->count_staging_assessment('ES3', $employee_id);
    //         $this->count_assessment_result('ES3', $employee_id);
    //     }

    //     return 'Ok';
    // }

    private function count_staging_assessment($type, $employee_id, $interview_participant_id = NULL)
    {
        $query = $this->raw_assessment->select('raw_assessment.aspect_question_id, ca.aspect, aq.component_aspect_id, qi.value')
            ->join('question_instrument qi', 'raw_assessment.question_instrument_id = qi.id', 'inner')
            ->join('aspect_question aq', 'raw_assessment.aspect_question_id = aq.id', 'inner')
            ->join('component_aspect ca', 'aq.component_aspect_id = ca.id', 'inner')
            ->where('employee_id', $employee_id)
            ->where('aq.is_active', true)
            ->where('ca.is_active', true);

        if (!empty($interview_participant_id)) {
            $query->where('interview_participant_id', $interview_participant_id);
        }

        $query = $query->find();

        $result = [];
        foreach ($query as $item) {
            $component_aspect_id = $item['component_aspect_id'];

            if (!isset($result[$component_aspect_id])) {
                $result[$component_aspect_id] = 0;
            }

            $result[$component_aspect_id] += $item['value'];
        }

        $final_result = [];
        foreach ($result as $key => $item) {
            $final_result[] = [
                'id'                        => generate_uuid(),
                'type'                      => $type,
                'employee_id'               => $employee_id,
                'component_aspect_id'       => $key,
                'value'                     => $item,
                'interview_participant_id'  => $interview_participant_id,
            ];
        }

        if (!empty($query)) {
            $builder = $this->staging_assessment->where('employee_id', $employee_id);

            if (!empty($interview_participant_id)) {
                $builder->where('interview_participant_id', $interview_participant_id);
            }

            $builder->delete();

            $this->staging_assessment->insertBatch($final_result);

            return 1;
        }

        return 0;
    }

    private function count_assessment_result($type, $employee_id, $interview_participant_id = NULL)
    {
        $query = $this->staging_assessment->select('staging_assessment.value, staging_assessment.component_aspect_id, ca.assessment_component_id')
            ->join('assessment.component_aspect ca', 'ca.id = staging_assessment.component_aspect_id', 'left')
            ->join('assessment.assessment_component ac', 'ac.id = ca.assessment_component_id', 'left')
            ->where('staging_assessment.employee_id', $employee_id)
            ->where('ca.is_active', true)
            ->where('ac.is_active', true);

        if (!empty($interview_participant_id)) {
            $query->where('staging_assessment.interview_participant_id', $interview_participant_id);
        }

        $query = $query->find();

        $result = [];
        foreach ($query as $item) {
            $assessment_component_id = $item['assessment_component_id'];

            if (!isset($result[$assessment_component_id])) {
                $result[$assessment_component_id] = 0;
            }

            $result[$assessment_component_id] += $item['value'];
        }

        $final_result = [];
        foreach ($result as $key => $item) {
            $final_result[] = [
                'id'                        => generate_uuid(),
                'type'                      => $type,
                'employee_id'               => $employee_id,
                'assessment_component_id'   => $key,
                'value'                     => $item,
                'interview_participant_id'  => $interview_participant_id,
            ];
        }

        if (!empty($query)) {
            $builder = $this->assessment_result->where('employee_id', $employee_id);

            if (!empty($interview_participant_id)) {
                $builder->where('interview_participant_id', $interview_participant_id);
            }

            $builder->delete();

            $this->assessment_result->insertBatch($final_result);

            return 1;
        }

        return 0;
    }

    public function getResult($employee_id, $eselon = NULL, $interview_participant_id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = 'Berita Acara Hasil Uji Kompetensi Melalui Wawancara';

        $data = $this->get_employee_data($employee_id, $interview_participant_id);

        $raw_result = $this->get_raw_result($employee_id, $interview_participant_id);
        $final_result = $this->assessment_result->get_final_assessment_result($employee_id, $interview_participant_id);

        $level = get_eselon_level($eselon);

        $baseline = $this->question_instrument->select('question_instrument.aspect_question_id, aq.question, aq.level as max_value')
            ->join('assessment.aspect_question aq', 'question_instrument.aspect_question_id = aq.id')
            ->where('aq.is_active', true)
            ->where('aq.level', $level)
            ->groupBy('question_instrument.aspect_question_id, aq.question, aq.level')
            ->findAll();

        $max_instrument = $this->aspect_question->select('MAX(qi.value) as max_value, COUNT(DISTINCT aspect_question.id) as max_question')
            ->join('assessment.question_instrument qi', 'qi.aspect_question_id = aspect_question.id', 'left')
            ->where('aspect_question.level', $level)
            ->first();

        $total_q_per_aspect = $this->assessment_component->select('assessment_component.name, COUNT(ca.id) as total')
            ->join('assessment.component_aspect ca', 'assessment_component.id = ca.assessment_component_id', 'inner')
            ->join('assessment.aspect_question aq', 'aq.component_aspect_id = ca.id', 'inner')
            ->where('aq.level', $level)
            ->groupBy('assessment_component.name')
            ->findAll();


        $max_score = $max_instrument['max_value'] * $max_instrument['max_question'];
        $max_score_target = ($level * $max_instrument['max_question']);

        $score = $this->assessment_result->get_final_score($employee_id, $interview_participant_id);
        $evaluator = $this->assessment_result->get_evaluator($employee_id, $interview_participant_id);

        $total_evaluator = count($evaluator);

        $component_convert_tempt = array();
        foreach ($total_q_per_aspect as $row_key => $row_value) {
            $total_q_per_aspect[$row_key]['toal_value'] = ($row_value['total'] * $total_evaluator * $max_instrument['max_value']);

            $component_convert_tempt[$row_value['name']] = array(
                'nilai_temp_awal' => 0,
                'nilai_temp_akhir' => 0,
                'nilai_temp' => $total_q_per_aspect[$row_key]['toal_value'],
                'jumlah_pertanyaan' => $row_value['total'],
                'jumlah_evaluator' => $total_evaluator,
                'jumlah_pertanyaan' => $row_value['total'],
            );
        }

        foreach ($final_result as $key => $row) {
            $component_convert_tempt[$row['name']]['nilai_temp_awal'] = $row['value'];
            $component_convert_tempt[$row['name']]['nilai_temp_akhir'] = ($row['value'] / $total_evaluator);
            $final_result[$key]['value'] = ($row['value'] / $total_evaluator);
        }

        $total_baseline = array_sum(array_column($baseline, 'max_value'));
        $total_value = array_sum(array_column($score, 'score'));

        $total_value_convert = (($total_value / $max_score) * 100);
        $total_value_target = (($total_value_convert / 100) * $max_score_target);

        $category = get_category($total_value_convert, false);

        return view('assessment/assessment/result', [
            'title'                     => $title,
            'data'                      => $data,
            'raw_result'                => $raw_result,
            'final_result'              => $final_result,
            'final_value'               => $total_value,
            'final_score'               => number_format($total_value_convert, 2),
            'category'                  => $category,
            'evaluator'                 => $evaluator,
            'employee_id'               => $employee_id,
            'interview_participant_id'  => $interview_participant_id,
            'uri_name'                  => $this->_uri_name,
        ]);
    }

    public function getConvert($employee_id, $eselon = NULL, $interview_participant_id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = 'Berita Acara Hasil Uji Kompetensi Melalui Wawancara';

        $data = $this->get_employee_data($employee_id, $interview_participant_id);

        $raw_result = $this->get_raw_result($employee_id, $interview_participant_id);
        $final_result = $this->assessment_result->get_final_assessment_result($employee_id, $interview_participant_id);

        $level = get_eselon_level($eselon);

        $total_q_per_aspect = $this->assessment_component->select('assessment_component.name, COUNT(ca.id) as total')
            ->join('assessment.component_aspect ca', 'assessment_component.id = ca.assessment_component_id', 'inner')
            ->join('assessment.aspect_question aq', 'aq.component_aspect_id = ca.id', 'inner')
            ->where('aq.level', $level)
            ->groupBy('assessment_component.name')
            ->findAll();

        $score = $this->assessment_result->get_final_score($employee_id, $interview_participant_id);
        $evaluator = $this->assessment_result->get_evaluator($employee_id, $interview_participant_id);

        $new_score = [];
        foreach ($score as $value) {
            $new_score[] = convert_scale($value['score'], '5', $level);
        }

        $total_evaluator = count($evaluator);

        $component_convert_tempt = array();
        foreach ($total_q_per_aspect as $row_key => $row_value) {
            $total_q_per_aspect[$row_key]['total_value'] = ($row_value['total'] * $total_evaluator * $level);

            $component_convert_tempt[$row_value['name']] = array(
                'nilai_temp_awal'   => 0,
                'nilai_temp_akhir'  => 0,
                'nilai_temp'        => $total_q_per_aspect[$row_key]['total_value'],
                'jumlah_pertanyaan' => $row_value['total'],
                'jumlah_evaluator'  => $total_evaluator,
                'jumlah_pertanyaan' => $row_value['total'],
            );
        }

        foreach ($final_result as $key => $row) {
            $component_convert_tempt[$row['name']]['nilai_temp_awal'] = $row['value'];
            $component_convert_tempt[$row['name']]['nilai_temp_akhir'] = ($row['value'] / $total_evaluator);
            $final_result[$key]['value'] = (convert_scale($row['value'], '5', $level) / $total_evaluator);
        }

        // Final Value
        $total_score = array_sum($new_score);
        $total_score_100 = (($total_score / 33) * 100);
        $category = get_category($total_score_100, false);

        return view('assessment/assessment/convert_result', [
            'title'                     => $title,
            'data'                      => $data,
            'raw_result'                => $raw_result,
            'final_result'              => $final_result,
            'final_value'               => $total_score,
            'final_score'               => number_format($total_score_100, 2),
            'category'                  => $category,
            'evaluator'                 => $evaluator,
            'employee_id'               => $employee_id,
            'interview_participant_id'  => $interview_participant_id,
            'uri_name'                  => $this->_uri_name,
        ]);
    }

    public function getPdf_convert($employee_id, $interview_participant_id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        // Fetch employee data
        $data = $this->get_employee_data($employee_id, $interview_participant_id);

        // Fetch assessment results
        $raw_result = $this->get_raw_result($employee_id, $interview_participant_id);
        $final_result = $this->assessment_result->get_final_assessment_result($employee_id, $interview_participant_id);

        $level = get_eselon_level($data['type']);

        if ($level == "Undefined Type") {
            $level = "5";
        }

        // Calculate total values and score
        $max_instrument = $this->aspect_question->select('MAX(qi.value) as max_value, COUNT(DISTINCT aspect_question.id) as max_question')
            ->join('assessment.question_instrument qi', 'qi.aspect_question_id = aspect_question.id', 'left')
            ->where('aspect_question.level', $level)
            ->first();

        $total_q_per_aspect = $this->assessment_component->select('assessment_component.name, COUNT(ca.id) as total')
            ->join('assessment.component_aspect ca', 'assessment_component.id = ca.assessment_component_id', 'inner')
            ->join('assessment.aspect_question aq', 'aq.component_aspect_id = ca.id', 'inner')
            ->where('aq.level', $level)
            ->groupBy('assessment_component.name')
            ->findAll();

        $score = $this->assessment_result->get_final_score($employee_id, $interview_participant_id);
        $evaluator = $this->assessment_result->get_evaluator($employee_id, $interview_participant_id);

        $new_score = [];
        foreach ($score as $value) {
            $new_score[] = convert_scale($value['score'], '5', $level);
        }

        $total_evaluator = count($evaluator);

        $component_convert_tempt = array();
        foreach ($total_q_per_aspect as $row_key => $row_value) {
            $total_q_per_aspect[$row_key]['total_value'] = ($row_value['total'] * $total_evaluator * $level);

            $component_convert_tempt[$row_value['name']] = array(
                'nilai_temp_awal'   => 0,
                'nilai_temp_akhir'  => 0,
                'nilai_temp'        => $total_q_per_aspect[$row_key]['total_value'],
                'jumlah_pertanyaan' => $row_value['total'],
                'jumlah_evaluator'  => $total_evaluator,
                'jumlah_pertanyaan' => $row_value['total'],
            );
        }

        foreach ($final_result as $key => $row) {
            $component_convert_tempt[$row['name']]['nilai_temp_awal'] = $row['value'];
            $component_convert_tempt[$row['name']]['nilai_temp_akhir'] = ($row['value'] / $total_evaluator);
            $final_result[$key]['value'] = (convert_scale($row['value'], '5', $level) / $total_evaluator);
        }

        // Final Value
        $total_score = array_sum($new_score);
        $total_score_100 = (($total_score / 33) * 100);
        $category = get_category($total_score_100, false);

        // Prepare profile image
        $profile_image = $data['profile_image'] !== NULL ? base_url('uploads/employee/profile_image/' . $data['profile_image']) : base_url('vlz/images/users/no-photo.jpg');
        $profile = convert_to_base64($profile_image);

        // Set the filename for the PDF
        $filename = "Hasil Wawancara -" . $data['full_name'];

        // Start output buffering
        ob_start();

        // Load the HTML view for the PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('assessment/assessment/convert_pdf', [
            'data'          => $data,
            'profile'       => $profile,
            'raw_result'    => $raw_result,
            'final_result'  => $final_result,
            'total_value'   => $total_score,
            'final_score'   => number_format($total_score_100, 2),
            'category'      => $category,
            'evaluator'     => $evaluator,
            'employee_id'   => $employee_id,
        ]));

        // Set the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Send the generated PDF to the browser
        $dompdf->stream($filename, ["Attachment" => true]); // true to force download, false to open in browser

        // Clear the output buffer
        ob_end_flush();
    }

    public function getPdf($employee_id, $interview_participant_id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        // Fetch employee data
        $data = $this->get_employee_data($employee_id, $interview_participant_id);

        // Fetch assessment results
        $raw_result = $this->get_raw_result($employee_id, $interview_participant_id);
        $final_result = $this->assessment_result->get_final_assessment_result($employee_id, $interview_participant_id);

        $level = get_eselon_level($data['type']);

        if ($level == "Undefined Type") {
            $level = "5";
        }

        // Get baseline values
        $baseline = $this->question_instrument->select('question_instrument.aspect_question_id, aq.question, aq.level as max_value')
            ->join('assessment.aspect_question aq', 'question_instrument.aspect_question_id = aq.id')
            ->where('aq.is_active', true)
            ->where('aq.level', $level)
            ->groupBy('question_instrument.aspect_question_id, aq.question, aq.level')
            ->findAll();

        // Calculate total values and score
        $max_instrument = $this->aspect_question->select('MAX(qi.value) as max_value, COUNT(DISTINCT aspect_question.id) as max_question')
            ->join('assessment.question_instrument qi', 'qi.aspect_question_id = aspect_question.id', 'left')
            ->where('aspect_question.level', $level)
            ->first();

        $total_q_per_aspect = $this->assessment_component->select('assessment_component.name, COUNT(ca.id) as total')
            ->join('assessment.component_aspect ca', 'assessment_component.id = ca.assessment_component_id', 'inner')
            ->join('assessment.aspect_question aq', 'aq.component_aspect_id = ca.id', 'inner')
            ->where('aq.level', $level)
            ->groupBy('assessment_component.name')
            ->findAll();


        $max_score = $max_instrument['max_value'] * $max_instrument['max_question'];
        $max_score_target = ($level * $max_instrument['max_question']);

        $score = $this->assessment_result->get_final_score($employee_id, $interview_participant_id);
        $evaluator = $this->assessment_result->get_evaluator($employee_id, $interview_participant_id);

        $total_evaluator = count($evaluator);

        $component_convert_tempt = array();
        foreach ($total_q_per_aspect as $row_key => $row_value) {
            $total_q_per_aspect[$row_key]['toal_value'] = ($row_value['total'] * $total_evaluator * $max_instrument['max_value']);

            $component_convert_tempt[$row_value['name']] = array(
                'nilai_temp_awal' => 0,
                'nilai_temp_akhir' => 0,
                'nilai_temp' => $total_q_per_aspect[$row_key]['toal_value'],
                'jumlah_pertanyaan' => $row_value['total'],
                'jumlah_evaluator' => $total_evaluator,
                'jumlah_pertanyaan' => $row_value['total'],
            );
        }

        foreach ($final_result as $key => $row) {
            $component_convert_tempt[$row['name']]['nilai_temp_awal'] = $row['value'];
            $component_convert_tempt[$row['name']]['nilai_temp_akhir'] = ($row['value'] / $total_evaluator);
            $final_result[$key]['value'] = ($row['value'] / $total_evaluator);
        }

        $total_baseline = array_sum(array_column($baseline, 'max_value'));
        $total_value = array_sum(array_column($score, 'score'));
        // $total_value_convert = ((($total_value / $max_score) * $level) / $level) * 100;
        $total_value_convert = (($total_value / $max_score) * 100);
        $total_value_target = (($total_value_convert / 100) * $max_score_target);

        $category = get_category($total_value_convert, false);

        // Prepare profile image
        $profile_image = $data['profile_image'] !== NULL ? base_url('uploads/employee/profile_image/' . $data['profile_image']) : base_url('vlz/images/users/no-photo.jpg');
        $profile = convert_to_base64($profile_image);

        // Set the filename for the PDF
        $filename = "Hasil Wawancara -" . $data['full_name'];

        // Start output buffering
        ob_start();

        // Load the HTML view for the PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('assessment/assessment/pdf', [
            'data'             => $data,
            'profile'       => $profile,
            'raw_result'    => $raw_result,
            'final_result'  => $final_result,
            'total_value'   => $total_value,
            'final_score'   => number_format($total_value_convert, 2),
            'category'      => $category,
            'evaluator'     => $evaluator,
            'employee_id'   => $employee_id,
        ]));

        // Set the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Send the generated PDF to the browser
        $dompdf->stream($filename, ["Attachment" => true]); // true to force download, false to open in browser

        // Clear the output buffer
        ob_end_flush();
    }

    public function getHistory($employee_id)
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $title = 'Riwayat Wawancara';

        $data = $this->get_employee_data($employee_id);

        $query = $this->db->table('ukom.raw_assessment ra')
            ->select('ra.interview_participant_id, ig.name as group_name, ig.level, ra.type, MIN(ra.created_at) as tanggal, COUNT(DISTINCT ra.evaluator) as jumlah_evaluator')
            ->join('assessment.interview_participants ip', 'CAST(ip.id AS VARCHAR) = CAST(ra.interview_participant_id AS VARCHAR)', 'left', false)
            ->join('assessment.interview_groups ig', 'ip.interview_group_id = ig.id', 'left')
            ->where('ra.employee_id', $employee_id)
            ->where('ra.interview_participant_id !=', NULL)
            ->groupBy('ra.interview_participant_id, ig.name, ig.level, ra.type')
            ->orderBy('tanggal', 'DESC')
            ->get()
            ->getResultArray();

        $history = [];
        foreach ($query as $item) {
            $scores = $this->assessment_result->get_final_score($employee_id, $item['interview_participant_id']);
            $score_values = array_column($scores, 'score');

            $history[] = [
                'interview_participant_id'  => $item['interview_participant_id'],
                'group_name'                => $item['group_name'],
                'level'                     => $item['level'],
                'type'                      => $item['type'],
                'tanggal'                   => $item['tanggal'],
                'jumlah_evaluator'          => $item['jumlah_evaluator'],
                'score'                     => count($score_values) ? number_format(array_sum($score_values) / count($score_values), 2) : '-',
            ];
        }

        return view('assessment/assessment/history', [
            'title'             => $title,
            'data'              => $data,
            'history'           => $history,
            'employee_id'       => $employee_id,
            'uri_name'          => $this->_uri_name,
        ]);
    }

    private function get_raw_result($employee_id, $interview_participant_id = NULL)
    {
        $query = $this->raw_assessment->get_assessment_result($employee_id, $interview_participant_id);

        $final_result = $this->process_results($query);

        return $final_result;
    }

    private function process_results($query)
    {
        $result = [];
        $average_count = [];
        $average_value = [];

        foreach ($query as $item) {
            if (!isset($average_count[$item['question']])) {
                $average_count[$item['question']] = 0;
            }
            if (!isset($average_value[$item['question']])) {
                $average_value[$item['question']] = 0;
            }
            $average_count[$item['question']]++;
            $average_value[$item['question']] += $item['value'];

            $component = $item['component'];
            $aspect = $item['aspect'];

            if (!isset($result[$component])) {
                $result[$component] = [];
            }

            if (!isset($result[$component][$aspect])) {
                $result[$component][$aspect] = [
                    'aspect'                => $item['aspect'],
                    'questions'             => [],
                ];
            }

            $result[$component][$aspect]['questions'][$item['question']] = [
                'question'  => $item['question'],
                'value'   => number_format(($item['value'] !== NULL ? ($average_value[$item['question']] / $average_count[$item['question']]) : 0), 2)
            ];
        }

        return $result;
    }
}
