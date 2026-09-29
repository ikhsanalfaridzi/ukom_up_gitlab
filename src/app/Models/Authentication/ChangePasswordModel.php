<?php

namespace App\Models\Authentication;

use App\Models\KV_Model;

class ChangePasswordModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'acl.users';
    protected $alias            = 'acl.users AS au';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $allowedFields    = ['id', 'password', 'is_change_password'];

    // Validation
    protected $validationRules      = [
        'password'           => 'required|trim|min_length[8]|max_length[200]',
        'confirm_password'   => 'matches[password]',
    ];
    protected $validationMessages   = [
        'password' => [
            'required'      => 'Silahkan masukkan password baru',
            'min_length'    => 'Password baru minimal {param} karakter',
            'max_length'    => 'Password baru maksimal {param} karakter',
        ],
        'confirm_password' => [
            'matches' => 'Konfirmasi password tidak sesuai dengan password baru'
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
