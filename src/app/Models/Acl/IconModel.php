<?php

namespace App\Models\Acl;

use App\Models\KV_Model;

class IconModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'acl.icons';
    protected $alias            = 'acl.icons AS ai';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['icon_name'];
    protected $allowedFields    = ['id', 'icon_name', 'groups', 'pack_name'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'icon_name'   => 'required|max_length[50]',
        'groups'      => 'permit_empty|max_length[255]',
        'pack_name'   => 'required|max_length[255]',
    ];
    protected $validationMessages   = [
        'icon_name' => [
            'required'      => 'Silahkan masukkan nama icon',
            'max_length'    => 'Nama icon maksimal 50 karakter',
        ],
        'groups' => [
            'max_length'    => 'Grup maksimal 255 karakter',
        ],
        'pack_name' => [
            'required'      => 'Silahkan masukkan nama icon pack',
            'max_length'    => 'Nama icon pack maksimal 255 karakter',
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
    protected $column_order = ['icon_name'];
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
        $builder = $this->db->table($this->alias)->select('ai.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('ai.id, ai.icon_name, ai.groups, ai.pack_name, ai.created_at');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ai.icon_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ai.groups)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ai.pack_name)', strtolower($this->request->getVar('search')['value']));
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
