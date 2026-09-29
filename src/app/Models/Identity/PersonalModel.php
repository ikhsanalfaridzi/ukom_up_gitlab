<?php

namespace App\Models\Identity;

use App\Models\KV_Model;

class PersonalModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'identity.personal';
    protected $alias            = 'identity.personal AS ip';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['personal_email', 'phone_number', 'wa_number', 'home_number', 'identity_number'];
    protected $allowedFields    = ['id', 'acl_user_id', 'employee_personnel_id', 'full_name', 'front_title', 'back_degree', 'non_academic_degree', 'personal_email', 'phone_number', 'wa_number', 'home_number', 'place_of_birth', 'date_of_birth', 'identity_address', 'address', 'blood_type', 'gender', 'province_id', 'city_id', 'district_id', 'village_id', 'religion_id', 'marital_status_id', 'identity_number', 'family_identity_number', 'photo_path', 'identity_card_path', 'is_verified', 'notes', 'verificator_id', 'verified_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'full_name'              => 'required|max_length[255]',
        'front_title'            => 'permit_empty|max_length[25]',
        'back_degree'            => 'permit_empty|max_length[50]',
        'non_academic_degree'    => 'permit_empty|max_length[50]',
        'personal_email'         => 'required|max_length[100]|valid_email',
        'phone_number'           => 'required|max_length[20]',
        'wa_number'              => 'required|max_length[20]',
        'home_number'            => 'permit_empty|max_length[20]',
        'place_of_birth'         => 'required|max_length[100]',
        'date_of_birth'          => 'required',
        'blood_type'             => 'permit_empty|max_length[2]',
        'identity_address'       => 'required|max_length[1000]',
        'address'                => 'required|max_length[1000]',
        'gender'                 => 'required',
        'identity_number'        => 'required|max_length[16]|is_valid_nik',
        'family_identity_number' => 'required|max_length[16]|is_valid_kk',
        'photo_path'             => 'mime_in[photo_path,image/jpg,image/jpeg,image/png]|max_size[photo_path,2048]',
        'identity_card_path'     => 'mime_in[identity_card_path,image/jpg,image/jpeg,image/png,application/pdf]|max_size[identity_card_path,2048]',
        'province_id'            => 'required',
        'city_id'                => 'required',
        'district_id'            => 'required',
        'village_id'             => 'required',
        'religion_id'            => 'required',
        'marital_status_id'      => 'required',
        'notes'                  => 'permit_empty|max_length[1000]',
    ];
    protected $validationMessages = [
        'full_name' => [
            'required'    => 'Silahkan masukkan nama lengkap.',
            'max_length'  => 'Nama lengkap tidak boleh lebih dari {param} karakter.',
        ],
        'front_title' => [
            'max_length'  => 'Gelar depan tidak boleh lebih dari {param} karakter.',
        ],
        'back_degree' => [
            'max_length'  => 'Gelar belakang tidak boleh lebih dari {param} karakter.',
        ],
        'non_academic_degree' => [
            'max_length'  => 'Gelar non-akademik tidak boleh lebih dari {param} karakter.',
        ],
        'personal_email' => [
            'required'    => 'Silahkan masukkan email pribadi.',
            'max_length'  => 'Email pribadi tidak boleh lebih dari {param} karakter.',
        ],
        'phone_number' => [
            'required'    => 'Silahkan masukkan nomor telepon.',
            'max_length'  => 'Nomor telepon tidak boleh lebih dari {param} karakter.',
        ],
        'wa_number' => [
            'required'    => 'Silahkan masukkan nomor whatsapp.',
            'max_length'  => 'Nomor whatsapp tidak boleh lebih dari {param} karakter.',
        ],
        'home_number' => [
            'max_length'  => 'Nomor rumah tidak boleh lebih dari {param} karakter.',
        ],
        'marital_status' => [
            'max_length'  => 'Status pernikahan tidak boleh lebih dari {param} karakter.',
        ],
        'place_of_birth' => [
            'required'    => 'Silahkan masukkan tempat lahir.',
            'max_length'  => 'Tempat lahir tidak boleh lebih dari {param} karakter.',
        ],
        'date_of_birth' => [
            'required'    => 'Silahkan masukkan tanggal lahir.',
        ],
        'identity_address' => [
            'required'    => 'Silahkan masukkan alamat KTP.',
            'max_length'  => 'Alamat KTP tidak boleh lebih dari {param} karakter.',
        ],
        'address' => [
            'required'    => 'Silahkan masukkan alamat tinggal.',
            'max_length'  => 'Alamat Tinggal tidak boleh lebih dari {param} karakter.',
        ],
        'gender' => [
            'required'    => 'Silahkan pilih jenis kelamin.',
        ],
        'identity_number' => [
            'required'     => 'Silahkan masukkan NIK.',
            'max_length'   => 'NIK tidak boleh lebih dari {param} karakter.',
            'is_valid_nik' => 'Format NIK tidak sesuai.',
        ],
        'family_identity_number' => [
            'required'    => 'Silahkan masukkan Nomor KK.',
            'max_length'  => 'Nomor KK tidak boleh lebih dari {param} karakter.',
            'is_valid_kk' => 'Format KK tidak sesuai.'
        ],
        'province_id' => [
            'required'    => 'Silahkan pilih provinsi alamat tinggal.',
        ],
        'city_id' => [
            'required'    => 'Silahkan pilih kabupaten/kota alamat tinggal.',
        ],
        'district_id' => [
            'required'    => 'Silahkan pilih kecamatan alamat tinggal.',
        ],
        'village_id' => [
            'required'    => 'Silahkan pilih kelurahan alamat tinggal.',
        ],
        'religion_id' => [
            'required'    => 'Silahkan pilih agama.',
        ],
        'marital_status_id' => [
            'required'    => 'Silahkan pilih status perkawinan.',
        ],
        'photo_path' => [
            'mime_in'  => 'Format foto harus berupa JPG, JPEG, atau PNG.',
            'max_size' => 'Ukuran foto tidak boleh lebih dari 2MB.',
        ],
        'identity_card_path' => [
            'mime_in'  => 'Format file KTP harus berupa JPG, JPEG, PNG, atau PDF.',
            'max_size' => 'Ukuran file KTP tidak boleh lebih dari 2MB.',
        ],
        'notes' => [
            'max_length'  => 'Catatan Verifikator tidak boleh lebih dari {param} karakter.',
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
    protected $column_order = ['employee_number'];
    protected $order = ['created_at' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
    }

    // ========== Verification DataTables ==========
    public function get_verification_data($is_verified)
    {
        $query = $this->get_verification_query($is_verified);

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_verification_filtered($is_verified)
    {
        $query = $this->get_verification_query($is_verified);

        return $query->countAllResults();
    }

    public function count_verification_data($is_verified)
    {
        $builder = $this->db->table($this->alias)->select('ip.id')
            ->join('employee.personnel ep', 'ip.employee_personnel_id = ep.id', 'inner')
            ->where('ep.is_verified', $is_verified);

        return $builder->countAllResults();
    }

    private function get_verification_query($is_verified)
    {
        $builder = $this->db->table($this->alias)->select('ip.id, ep.employee_number, ip.full_name as employee_name, ip.identity_number, ip.family_identity_number, ip.gender, ip.place_of_birth, ip.date_of_birth, ip.identity_card_path, ep.is_verified, ip.created_at')
            ->join('employee.personnel ep', 'ip.employee_personnel_id = ep.id', 'inner')
            ->where('ep.is_verified', $is_verified);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ep.employee_number)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ip.full_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ip.identity_number)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ip.family_identity_number)', strtolower($this->request->getVar('search')['value']));
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
