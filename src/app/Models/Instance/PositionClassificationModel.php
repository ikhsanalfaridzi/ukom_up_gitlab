<?php

namespace App\Models\Instance;

use App\Models\KV_Model;

class PositionClassificationModel extends KV_Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'instance.position_classifications';
    protected $alias            = 'instance.position_classifications as ipc';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $uniqueFields     = [];
    protected $allowedFields    = ['id', 'classification_id', 'position_id', 'priority'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'classification_id' => 'required',
        'position_id'       => 'required',
        'priority'          => 'permit_empty|max_length[10]',
    ];

    protected $validationMessages = [
        'classification_id' => [
            'required' => 'Silakan pilih bidang jabatan.',
        ],
        'position_id' => [
            'required' => 'Silakan pilih nama jabatan.',
            'max_length' => 'Silakan pilih nama jabatan.',
        ],
        'priority' => [
            'max_length' => 'Prioritas maksimal {param} karakter.',
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
    protected $column_order = ['classification_id'];
    protected $order = ['created_at' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->request = service('request');
    }

    public function get_datatables($classification_id)
    {
        $query = $this->get_datatables_query($classification_id);

        if ($this->request->getVar('length') != -1) {
            $query->limit($this->request->getVar('length'), $this->request->getVar('start'));
        }

        return $query->get()->getResult();
    }

    public function count_filtered($classification_id)
    {
        $query = $this->get_datatables_query($classification_id);

        return $query->countAllResults();
    }

    public function count_all($classification_id)
    {
        $builder = $this->db->table($this->alias)->select('ipc.id')
            ->where('ipc.classification_id', $classification_id);

        return $builder->countAllResults();
    }

    private function get_datatables_query($classification_id)
    {
        $builder = $this->db->table($this->alias)->select('ipc.id, ip.position as position_name, ipc.priority, ipc.created_at')
            ->join('instance.positions ip', 'ipc.position_id = ip.id', 'inner')
            ->where('ipc.classification_id', $classification_id);

        if (isset($this->request->getVar('search')['value']) && !empty($this->request->getVar('search')['value'])) {
            $builder->groupStart();
            $builder->like('LOWER(ip.position)', strtolower($this->request->getVar('search')['value']));
            $builder->orLike('LOWER(ipc.priority)', strtolower($this->request->getVar('search')['value']));
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
