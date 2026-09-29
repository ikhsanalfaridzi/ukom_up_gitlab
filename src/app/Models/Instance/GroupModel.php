<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class GroupModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.groups';
    protected $alias            = 'instance.groups as ig';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = ['group'];
    protected $allowedFields    = ['id', 'group', 'parent_id', 'sequence_number', 'sub_type', 'is_access_province', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'group'             => 'required|alpha_numeric_punct|max_length[100]',
        'sub_type'          => 'required',
        'sequence_number'   => 'required|integer|greater_than[0]',
        'parent_id'         => 'permit_empty',
    ];

    protected $validationMessages = [
        'group' => [
            'required'              => 'Silakan masukkan nama grup.',
            'alpha_numeric_punct'   => 'Grup hanya boleh mengandung huruf, angka, spasi, dan tanda baca tertentu.',
            'max_length'            => 'Grup tidak boleh lebih dari 100 karakter.',
        ],
        'sub_type' => [
            'required' => 'Silakan pilih tingkatan/kategori.',
        ],
        'sequence_number' => [
            'required'      => 'Silakan masukkan nomor urut.',
            'integer'       => 'Nomor urut harus berupa angka.',
            'greater_than'  => 'Nomor urut harus lebih besar dari 0.',
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
    protected $column_order = ['sub_type', 'sequence_number'];
    protected $order = ['created_at' => 'DESC'];

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
        $builder = $this->db->table($this->alias)->select('ig.id');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)
            ->select('ig.id, ig.group, ig.sequence_number, ig.sub_type, ig2.group as group_parent, ig.created_at')
            ->join('instance.groups ig2', 'ig.parent_id = ig2.id', 'left');


        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ig.group)', strtolower($this->request->getVar('search')['value']));
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
