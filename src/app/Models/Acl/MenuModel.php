<?php

namespace App\Models\Acl;

use App\Models\KV_Model;

class MenuModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'acl.menu';
    protected $alias            = 'acl.menu AS am';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['menu_code'];
    protected $allowedFields    = ['id', 'menu_code', 'menu_name', 'parent_id', 'module_id', 'url', 'sequence_no', 'icon_name', 'is_display', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'menu_code'   => 'required|max_length[50]',
        'menu_name'   => 'required|max_length[250]',
        'parent_id'   => 'trim|permit_empty',
        'module_id'   => 'required',
        'url'         => 'permit_empty|max_length[100]',
        'sequence_no' => 'permit_empty',
        'icon_name'   => 'permit_empty|max_length[50]',
        'is_display'  => 'permit_empty',
        'description' => 'permit_empty|max_length[1000]',
    ];
    protected $validationMessages   = [
        'menu_code' => [
            'required'      => 'Silahkan masukkan kode menu',
            'max_length'    => 'Kode menu maksimal {param} karakter',
        ],
        'menu_name' => [
            'required'      => 'Silahkan masukkan nama menu',
            'max_length'    => 'Nama menu maksimal {param} karakter',
        ],
        'module_id' => [
            'required'      => 'Silahkan pilih nama modul',
        ],
        'url' => [
            'required'      => 'Silahkan masukkan url menu',
            'max_length'    => 'Url menu maksimal {param} karakter',
        ],
        'icon_name' => [
            'max_length'    => 'Nama icon maksimal {param} karakter',
        ],
        'description' => [
            'max_length'    => 'Deskripsi maksimal {param} karakter',
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
    protected $column_order = ['menu_code'];
    protected $order = ['am.updated_at' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
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
        $builder = $this->db->table($this->alias)->select('am.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table('acl.menu am')
            ->select('am.id, am.menu_code, am.menu_name, am2.menu_name as parent_name, sm.module_name, am.url, am.sequence_no, am.icon_name, am.is_display, am.created_at')
            ->join('acl.menu am2', 'am.parent_id = am2.id', 'left')
            ->join('sysapp.module sm', 'am.module_id = sm.id', 'left');


        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(am.menu_code)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(am.menu_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(am2.menu_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(sm.module_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(am.url)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('CAST(am.sequence_no as TEXT)', $this->request->getVar('search')['value']);
            $builder->orLike('LOWER(am.icon_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(am.is_display)', strtolower($this->request->getVar('search')['value']));
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
