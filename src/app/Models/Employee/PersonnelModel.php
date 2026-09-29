<?php

namespace App\Models\Employee;

use App\Libraries\System\SystemLib;
use App\Models\KV_Model;

class PersonnelModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'employee.personnel';
    protected $alias            = 'employee.personnel AS ep';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['employee_number', 'old_employee_number', 'office_email'];
    protected $allowedFields    = ['id', 'employee_number', 'old_employee_number', 'office_email', 'employment_id', 'employment_type_id', 'assortment_id', 'position_id', 'organization_structure_id', 'unor_id', 'is_verified', 'notes', 'verificator_id', 'verified_at', 'responsibility_letter_path'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'employee_number'            => 'required|max_length[18]|is_valid_nip_nrp',
        'old_employee_number'        => 'permit_empty|max_length[18]',
        'office_email'               => 'permit_empty|max_length[100]|valid_email',
        'employment_id'              => 'required',
        'employment_type_id'         => 'required',
        'assortment_id'              => 'required',
        'position_id'                => 'required',
        'organization_structure_id'  => 'required',
        'assignment_letter_path'     => 'permit_empty',
        'unor_id'                    => 'permit_empty|max_length[50]',
        'notes'                      => 'permit_empty|max_length[1000]',
        'responsibility_letter_path' => 'mime_in[responsibility_letter_path,image/jpg,image/jpeg,image/png,application/pdf]|max_size[responsibility_letter_path,2048]',
    ];
    protected $validationMessages = [
        'employee_number' => [
            'required'         => 'Silahkan masukkan NIP/NRP.',
            'max_length'       => 'NIP/NRP baru tidak boleh lebih dari {param} karakter.',
            'is_valid_nip_nrp' => 'Format NIP/NRP tidak sesuai.'
        ],
        'old_employee_number' => [
            'max_length'  => 'NIP/NRP lama tidak boleh lebih dari {param} karakter.',
        ],
        'email' => [
            'max_length'  => 'Email tidak boleh lebih dari {param} karakter.',
            'valid_email' => 'Silahkan masukkan email yang valid.',
        ],
        'employment_id' => [
            'required'    => 'Silahkan pilih status kepegawaian.',
        ],
        'employment_type_id' => [
            'required'    => 'Silahkan pilih tipe pegawai.',
        ],
        'assortment_id' => [
            'required'  => 'Silahkan pilih golongan.',
        ],
        'position_id' => [
            'required'  => 'Silahkan pilih jabatan.',
        ],
        'organization_structure_id' => [
            'required'  => 'Silahkan pilih jabatan unit kerja.',
        ],
        'unor_id' => [
            'max_length'  => 'Unor ID tidak boleh lebih dari {param} karakter.',
        ],
        'notes' => [
            'max_length'  => 'Catatan Verifikator tidak boleh lebih dari {param} karakter.',
        ],
        'responsibility_letter_path' => [
            'mime_in'  => 'Format Surat Pernyataan Tanggung Jawab Mutlak harus berupa JPG, JPEG, PNG, atau PDF.',
            'max_size' => 'Ukuran Surat Pernyataan Tanggung Jawab Mutlak tidak boleh lebih dari 2MB.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // Datatables
    protected $request;
    protected $sys_lib;
    protected $column_order = ['employee_number'];
    protected $order = ['ep.created_at' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
        $this->sys_lib = new SystemLib();
    }

    public function get_datatables()
    {
        $query = $this->get_datatables_query();

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_filtered()
    {
        $query = $this->get_datatables_query();
        return $query->countAllResults();
    }

    public function count_all()
    {
        $builder = $this->db->table($this->alias)->select('ep.id')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'ipr.acl_user_id = au.id', 'left');

        $this->add_complete_filter($builder);

        return $builder->countAllResults();
    }

    private function add_complete_filter($builder)
    {
        $builder->where('ipr.full_name IS NOT NULL')
            ->where('ipr.full_name !=', '');
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)
            ->select('ep.id, ep.employee_number, au.is_active, au.username, ipr.full_name, ea.assortment, eg.grade as tingkatan, ips.position, org.organization')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('employee.grades eg', 'ips.grade_id = eg.id', 'left')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'ipr.acl_user_id = au.id', 'left')
            ->join('instance.organization_structures os', 'ep.organization_structure_id = os.id', 'left')
            ->join('instance.organizations org', 'os.organization_id = org.id', 'left');

        $this->add_complete_filter($builder);

        $user_id = session()->get('login_user_id');
        $allowed_organization_structure_ids = $this->sys_lib->get_excluded_ids('allowed_organization_configuration', TRUE);

        if (!in_array($user_id, $allowed_organization_structure_ids)) {
            $support_ids = $this->sys_lib->get_sys_reference('allowed_organization_support', TRUE);

            if (!empty($allowed_organization_structure_ids)) {
                $builder->whereNotIn('au.id', $allowed_organization_structure_ids);
            }

            if (!empty($support_ids)) {
                $builder->whereNotIn('au.id', $support_ids);
            }
        }

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ep.employee_number)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ipr.full_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ea.assortment)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(eg.grade)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ips.position)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(org.organization)', strtolower($this->request->getVar('search')['value']));
            $builder->groupEnd();
        }

        if ($this->order) {
            $builder->orderBy(key($this->order), current($this->order));
        } else {
            $order_column_index = $this->request->getVar('order')[0]['column'];
            $order_direction = $this->request->getVar('order')[0]['dir'];
            $builder->orderBy($this->column_order[$order_column_index], $order_direction);
        }

        return $builder;
    }
}
