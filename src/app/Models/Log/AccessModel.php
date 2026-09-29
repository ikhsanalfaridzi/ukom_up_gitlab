<?php

namespace App\Models\Log;

use CodeIgniter\Model;

class AccessModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'log.access';
    protected $alias            = 'log.access AS la';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $allowedFields    = ['id', 'url', 'acl_user_id', 'ip_address', 'user_data', 'month', 'year'];

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
    protected $column_order = ['url'];
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
        $builder = $this->db->table($this->alias)->select('la.id')
            ->join('acl.users au', 'la.acl_user_id = au.id', 'left');

        return $builder->countAllResults();
    }

    private function get_datatables_query()
    {
        $builder = $this->db->table($this->alias)->select('la.id, au.username, la.url, la.ip_address, la.user_data, la.created_at')
            ->join('acl.users au', 'la.acl_user_id = au.id', 'left');

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(au.username)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(la.types)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(la.ip_address)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(la.url)', strtolower($this->request->getVar('search')['value']));
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
