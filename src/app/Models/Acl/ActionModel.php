<?php

namespace App\Models\Acl;

use App\Models\KV_Model;

class ActionModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'acl.actions';
    protected $alias            = 'acl.actions AS aa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['action_code'];
    protected $allowedFields    = ['id', 'action_code', 'action_name', 'indicator', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'action_code'   => 'required|max_length[25]',
        'action_name'   => 'required|max_length[50]',
        'indicator'     => 'required|max_length[50]',
        'description'   => 'permit_empty|max_length[1000]',
    ];
    protected $validationMessages   = [
        'action_code' => [
            'required'      => 'Silahkan masukkan kode aksi',
            'max_length'    => 'Kode aksi maksimal {param} karakter',
        ],
        'action_name' => [
            'required'      => 'Silahkan masukkan action name',
            'max_length'    => 'Action name maksimal {param} karakter',
        ],
        'indicator' => [
            'required'      => 'Silahkan pilih indikator',
            'max_length'    => 'Indikator maksimal {param} karakter',
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
    protected $column_order = ['action_code'];
    protected $order = ['updated_at' => 'DESC'];

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
        $builder = $this->db->table($this->alias)->select('aa.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('aa.id, aa.action_code, aa.action_name, aa.indicator, aa.description, aa.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(aa.action_code)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(aa.action_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(aa.indicator)', strtolower($this->request->getVar('search')['value']));
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
