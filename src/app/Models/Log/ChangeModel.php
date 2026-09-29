<?php

namespace App\Models\Log;

use CodeIgniter\Model;

class ChangeModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'log.change';
    protected $alias            = 'log.change AS lc';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $allowedFields    = ['id', 'acl_user_id', 'table_name', 'types', 'query', 'notes', 'ip_address', 'user_data', 'month', 'year'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
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
    protected $column_order = ['acl_user_id'];
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
        $builder = $this->db->table($this->alias)->select('lc.id')
            ->join('acl.users au', 'lc.acl_user_id = au.id', 'inner');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('lc.id, au.username, lc.table_name, lc.types, lc.query, lc.notes, lc.ip_address, lc.user_data, lc.created_at')
            ->join('acl.users au', 'lc.acl_user_id = au.id', 'inner');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(au.username)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(lc.table_name)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(lc.types)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(lc.ip_address)', strtolower($this->request->getVar('search')['value']));
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
