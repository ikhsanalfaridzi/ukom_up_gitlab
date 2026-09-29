<?php

namespace App\Controllers\User;

use App\Controllers\KV_Controller;

class Grup_akses extends KV_Controller
{
    public function getIndex()
    {
        $title = "Grup Akses";

        return view('user/grup_akses', [
            'title'    => $title,
            'uri_name' => 'user/grup_akses',
        ]);
    }
}
