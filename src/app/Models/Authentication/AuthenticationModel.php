<?php

namespace App\Models\Authentication;

use App\Models\KV_Model;

class AuthenticationModel extends KV_Model
{
    // Validation
    protected $validationRules      = [
        'username'   => 'required|alpha_numeric|regex_match[/^[a-zA-Z0-9_]{1,100}$/]',
        // 'password'   => 'required|regex_match[/^[a-zA-Z0-9!@#$%^&*()+=-_]+$/]',
        'password'   => 'required|max_length[50]',
    ];
    protected $validationMessages   = [
        'username' => [
            'required'    => 'Silahkan masukkan username',
            'regex_match' => 'Username must be 1-100 characters long and contain only letters, numbers, and underscores',
        ],
        'password' => [
            'required'    => 'Silahkan masukkan password',
            'regex_match' => 'Password can contain only letters, numbers, and the following special characters: !@#$%^&*()+=-_',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
