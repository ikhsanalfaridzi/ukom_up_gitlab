<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class OrganizationContactModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.organization_contacts';
    protected $alias            = 'instance.organization_contacts AS ioc';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $allowedFields    = ['id', 'organization_id', 'email', 'phone_number', 'fax_number', 'address', 'latitude', 'longitude', 'vector'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'email'        => 'permit_empty|max_length[100]|valid_email',
        'phone_number' => 'permit_empty|max_length[15]|regex_match[/^[0-9+\s\-]+$/]',
        'fax_number'   => 'permit_empty|max_length[15]|regex_match[/^[0-9+\s\-]+$/]',
        'address'      => 'permit_empty|max_length[255]',
        'latitude'     => 'permit_empty|max_length[30]',
        'longitude'    => 'permit_empty|max_length[30]',
        'vector'       => 'permit_empty|max_length[255]',
    ];
    protected $validationMessages = [
        'email' => [
            'max_length'  => 'Email tidak boleh lebih dari 100 karakter.',
            'valid_email' => 'Silakan masukkan alamat email dengan format yang valid.',
        ],
        'phone_number' => [
            'max_length'  => 'Nomor telepon tidak boleh lebih dari 15 karakter.',
            'regex_match' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda tambah (+), atau tanda hubung (-).',
        ],
        'fax_number' => [
            'max_length'  => 'Nomor faks tidak boleh lebih dari 15 karakter.',
            'regex_match' => 'Nomor faks hanya boleh berisi angka, spasi, tanda tambah (+), atau tanda hubung (-).',
        ],
        'address' => [
            'max_length' => 'Alamat tidak boleh melebihi 255 karakter. Silakan gunakan alamat yang lebih pendek.',
        ],
        'latitude' => [
            'max_length' => 'Nilai latitude tidak boleh lebih dari 30 karakter.',
        ],
        'longitude' => [
            'max_length' => 'Nilai longitude tidak boleh lebih dari 30 karakter.',
        ],
        'vector' => [
            'max_length' => 'Vector tidak boleh melebihi 255 karakter.',
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
}
