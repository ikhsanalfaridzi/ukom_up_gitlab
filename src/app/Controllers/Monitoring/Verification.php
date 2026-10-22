<?php

namespace App\Controllers\Monitoring;

use App\Controllers\KV_Controller;
use App\Models\Monitoring\VerificationModel;

class Verification extends KV_Controller
{
    protected $verification;

    private const STATUS_OPTIONS = [
        'draft'     => 'Draft',
        'menunggu'  => 'Menunggu Verifikasi',
        'disetujui' => 'Disetujui',
        'ditolak'   => 'Ditolak/Perbaikan',
    ];

    function __construct()
    {
        parent::__construct(TRUE);

        $this->_title          = 'Monitoring Verifikasi';
        $this->_uri_name       = 'monitoring/verification';
        $this->_uri_name_view  = 'monitoring/verification';
        $this->_menu_code      = 'monitoring_verification';

        $this->verification = new VerificationModel();
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');
        $jenis = $this->request->getGet('jenis');

        return view('monitoring/verification/index', [
            'title'        => $this->_title,
            'uri_name'     => $this->_uri_name,
            'summary'      => $this->verification->get_summary($start, $end, $jenis),
            'filter_start' => $start,
            'filter_end'   => $end,
            'filter_jenis' => $jenis,
            'can_update'   => is_authorized($this->_menu_code, 'update'),
        ]);
    }

    public function getStatus()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return $this->response->setJSON(['error' => 'Akses ditolak.'])->setStatusCode(403);
        }

        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');
        $jenis = $this->request->getGet('jenis');

        return $this->response->setJSON($this->verification->get_summary($start, $end, $jenis));
    }

    public function postData()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return $this->response->setJSON(['error' => 'Akses ditolak.'])->setStatusCode(403);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid Request'])->setStatusCode(400);
        }

        $start  = $this->request->getPost('start_date');
        $end    = $this->request->getPost('end_date');
        $jenis  = $this->request->getPost('jenis');
        $status = $this->request->getPost('status');
        $search = $this->request->getPost('search')['value'] ?? NULL;

        $limit  = (int) ($this->request->getPost('length') ?? 10);
        $offset = (int) ($this->request->getPost('start') ?? 0);

        $rows = $this->verification->get_datatables($start, $end, $jenis, $status, $search, $limit, $offset);
        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'name'          => esc($row['name']),
                'jenis'         => $this->jenis_badge($row['jenis']),
                'pengusul'      => esc($row['pengusul']),
                'peserta'       => number_format((int) $row['peserta']),
                'terverifikasi' => number_format((int) $row['terverifikasi']),
                'created_at'    => date('d-m-Y', strtotime($row['created_at'])),
                'status'        => $this->status_badge($row['verification_status']),
                'action'        => $this->status_action($row['id'], $row['verification_status']),
            ];
        }

        return $this->response->setJSON([
            'draw'            => (int) $this->request->getPost('draw'),
            'recordsTotal'    => $this->verification->count_all($start, $end, $jenis),
            'recordsFiltered' => $this->verification->count_filtered($start, $end, $jenis, $status, $search),
            'data'            => $data,
        ]);
    }

    public function postUpdate_status($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return $this->response->setJSON(['success' => FALSE, 'message' => 'Akses ditolak.'])->setStatusCode(403);
        }

        if (empty($id)) {
            return $this->response->setJSON(['success' => FALSE, 'message' => 'Data tidak ditemukan.'])->setStatusCode(400);
        }

        $status = $this->request->getPost('status');

        if (!array_key_exists($status, self::STATUS_OPTIONS)) {
            return $this->response->setJSON(['success' => FALSE, 'message' => 'Status tidak valid.'])->setStatusCode(400);
        }

        $updated = $this->verification->update_status($id, $status);

        return $this->response->setJSON([
            'success' => (bool) $updated,
            'message' => $updated ? 'Status verifikasi berhasil diperbarui.' : 'Gagal memperbarui status verifikasi.',
        ]);
    }

    private function status_badge($status): string
    {
        $label  = self::STATUS_OPTIONS[$status] ?? ucfirst((string) $status);
        $colors = [
            'draft'     => 'secondary',
            'menunggu'  => 'warning',
            'disetujui' => 'success',
            'ditolak'   => 'danger',
        ];
        $color = $colors[$status] ?? 'secondary';

        return '<span class="badge bg-label-' . $color . '">' . esc($label) . '</span>';
    }

    private function jenis_badge($jenis): string
    {
        $color = $jenis === 'PDJL' ? 'info' : 'primary';

        return '<span class="badge bg-label-' . $color . '">' . esc($jenis) . '</span>';
    }

    private function status_action($id, $current): string
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return '';
        }

        $options = '';

        foreach (self::STATUS_OPTIONS as $value => $label) {
            $selected = $value === $current ? ' selected' : '';
            $options .= '<option value="' . $value . '"' . $selected . '>' . esc($label) . '</option>';
        }

        return '<select class="form-select form-select-sm status-select" data-id="' . esc($id) . '" style="min-width: 165px;">' . $options . '</select>';
    }
}
