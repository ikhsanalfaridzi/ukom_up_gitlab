<?php

/**
 * Payroll Feature from Instance Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-13
 */

namespace App\Controllers\Instance;

use App\Controllers\KV_Controller;
use App\Models\Instance\OrganizationModel;
use App\Models\Instance\PayrollModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;
use Exception;

class Payroll extends KV_Controller
{
    protected $payroll;
    protected $organization;

    function __construct()
    {
        parent::__construct(TRUE);
        $this->db           = Database::connect();
        $this->payroll      = new PayrollModel();
        $this->organization = new OrganizationModel();
        $this->_title       = 'Gaji & Payroll';
        $this->_uri_name    = 'instance/payroll';
        $this->_uri_name_view = 'instance/payroll';
        $this->_menu_code   = 'instance_payroll';
    }

    public function getIndex()
    {
        return parent::_index();
    }

    public function postData()
    {
        if ($this->request->isAJAX()) {
            try {
                $list = $this->payroll->get_datatables();
                $data = [];

                foreach ($list as $item) {
                    $data[] = [
                        'employee_personnel_id' => $item->employee_personnel_id,
                        'salary_id'             => $item->salary_id,
                        'full_name'             => $item->full_name,
                        'organization'          => $item->organization,
                        'salary'                => !empty($item->salary) ? 'Rp ' . number_format($item->salary, 0, ',', '.') : '-',
                        'created_at'            => !empty($item->created_at) ? date('d-m-Y H:i', strtotime($item->created_at)) : '-',
                        'document_path'         => $item->document_path,
                        'has_salary'            => !empty($item->salary_id),
                    ];
                }

                $response = [
                    'draw'            => $this->request->getVar('draw'),
                    'recordsTotal'    => $this->payroll->count_all(),
                    'recordsFiltered' => $this->payroll->count_filtered(),
                    'data'            => $data
                ];
            } catch (\Throwable $e) {
                log_message('error', 'Payroll postData error: ' . $e->getMessage());
                $response = [
                    'draw'            => $this->request->getVar('draw'),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => []
                ];
            }

            return $this->response->setJSON($response);
        }

        return $this->response->setJSON(['error' => 'Invalid Request']);
    }

    public function postDataTop4()
    {
        if ($this->request->isAJAX()) {
            try {
                $list = $this->payroll->get_top_4_data();
                $data = [];

                foreach ($list as $item) {
                    $data[] = [
                        'id'            => $item->salary_id,
                        'full_name'     => $item->full_name,
                        'organization'  => $item->organization,
                        'salary'        => 'Rp ' . number_format($item->salary, 0, ',', '.'),
                        'created_at'    => $item->created_at ? date('d-m-Y H:i', strtotime($item->created_at)) : '-',
                        'document_path' => $item->document_path,
                    ];
                }

                $response = [
                    'draw'            => 1,
                    'recordsTotal'    => count($data),
                    'recordsFiltered' => count($data),
                    'data'            => $data
                ];
            } catch (\Throwable $e) {
                log_message('error', 'Payroll postDataTop4 error: ' . $e->getMessage());
                $response = [
                    'draw'            => 1,
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => []
                ];
            }

            return $this->response->setJSON($response);
        }

        return $this->response->setJSON(['error' => 'Invalid Request']);
    }

    public function getForm($id = NULL)
    {
        $employees = $this->db->table('employee.personnel ep')
            ->select('ep.id, COALESCE(ip.full_name, ep.employee_number) AS full_name')
            ->join('identity.personal ip', 'ip.employee_personnel_id = ep.id', 'left')
            ->get()
            ->getResultArray();

        $employee_options = [];
        foreach ($employees as $emp) {
            $employee_options[$emp['id']] = $emp['full_name'];
        }

        $organizations = $this->organization->select('id, organization')->findAll();
        $organization_options = format_options($organizations, 'organization');

        $benefit_options = [
            'Tunjangan Kinerja'   => 'Tunjangan Kinerja',
            'Tunjangan Transport' => 'Tunjangan Transport',
            'Tunjangan Makan'     => 'Tunjangan Makan',
            'Tunjangan Keluarga'  => 'Tunjangan Keluarga',
            'Tunjangan Jabatan'   => 'Tunjangan Jabatan',
        ];

        $selected_benefits = [];
        $result = [];

        if (!empty($id)) {
            $existing = $this->payroll->find($id);
            if ($existing) {
                $result = $existing;
                if (!empty($existing['benefits'])) {
                    $decoded = json_decode($existing['benefits'], true);
                    if (is_array($decoded)) {
                        $selected_benefits = $decoded;
                    }
                }
            }
        } else {
            $preselectEmp = $this->request->getGet('employee_personnel_id');
            if ($preselectEmp) {
                $result['employee_personnel_id'] = $preselectEmp;
                $empRecord = $this->db->table('employee.personnel ep')
                    ->select('ios.organization_id')
                    ->join('instance.organization_structures ios', 'ios.id = ep.organization_structure_id', 'left')
                    ->where('ep.id', $preselectEmp)
                    ->get()
                    ->getRowArray();
                if ($empRecord && !empty($empRecord['organization_id'])) {
                    $result['organization_id'] = $empRecord['organization_id'];
                }
            }
        }

        $this->_additional_display['employee_options']     = $employee_options;
        $this->_additional_display['organization_options'] = $organization_options;
        $this->_additional_display['benefit_options']      = $benefit_options;
        $this->_additional_display['selected_benefits']   = $selected_benefits;

        return parent::_form($this->payroll, ['id' => $id, 'result' => $result]);
    }

    public function postCreate()
    {
        $validation = [
            'rules'   => $this->payroll->validationRules,
            'message' => $this->payroll->validationMessages,
        ];

        $this->message = 'Data berhasil disimpan';

        return parent::_execute('this', 'insert_payroll', $this->_uri_name, [], $validation);
    }

    protected function insert_payroll()
    {
        $data = $this->_set_model_values($this->payroll, 'post');
        $data['id'] = $this->uuid;

        $benefitsInput = $this->request->getPost('benefits');
        $data['benefits'] = is_array($benefitsInput) ? json_encode($benefitsInput) : json_encode([]);

        $file = $this->request->getFile('document_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/payroll';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $data['document_path'] = 'uploads/payroll/' . $newName;
        }

        if (!$this->payroll->save($data)) {
            throw new Exception('Gagal menyimpan data gaji.');
        }

        return $data['id'];
    }

    public function postEdit($id)
    {
        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $validation = [
            'rules'   => $this->payroll->validationRules,
            'message' => $this->payroll->validationMessages,
        ];

        $this->message = 'Data berhasil diperbarui';

        return parent::_execute('this', 'update_payroll', $this->_uri_name, ['id' => $id], $validation);
    }

    protected function update_payroll($id)
    {
        $data = $this->_set_model_values($this->payroll, 'post');
        $data['id'] = $id;

        $benefitsInput = $this->request->getPost('benefits');
        $data['benefits'] = is_array($benefitsInput) ? json_encode($benefitsInput) : json_encode([]);

        $file = $this->request->getFile('document_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/payroll';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $data['document_path'] = 'uploads/payroll/' . $newName;
        }

        if (!$this->payroll->save($data)) {
            throw new Exception('Gagal mengedit data gaji.');
        }

        return $id;
    }

    public function getDelete($id = NULL)
    {
        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $existing = $this->payroll->find($id);
        if ($existing && !empty($existing['document_path']) && file_exists(FCPATH . $existing['document_path'])) {
            @unlink(FCPATH . $existing['document_path']);
        }

        $this->payroll->delete($id);

        return redirect()->to(site_url($this->_uri_name))->with('success', 'Data berhasil dihapus');
    }

    public function getDetail($id = NULL)
    {
        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $detail = $this->payroll->get_detail_by_id($id);
        if (!$detail) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->_data['detail'] = $detail;
        $this->_data['uri_name'] = $this->_uri_name;
        return view($this->_uri_name_view . '/detail', $this->_data);
    }
}
