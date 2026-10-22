<?php

namespace App\Controllers\Home;

use App\Controllers\KV_Controller;
use App\Models\Recap\RecapModel;
use Config\Database;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Dashboard extends KV_Controller
{
    function __construct()
    {
        parent::__construct(TRUE);
        $this->_title          = 'Laman';
        $this->_uri_name       = 'home/dashboard';
        $this->_uri_name_view  = 'dashboard';
        $this->_menu_code      = 'dashboard';
    }

    public function getIndex()
    {
        $db = Database::connect();

        // Filter periode (berdasarkan tanggal dibuat kelompok wawancara)
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        // Ringkasan UKKJ & PDJL
        $recap   = new RecapModel();
        $summary = $recap->get_summary($start, $end);

        // Riwayat UKKJ (tren per tahun, rincian per gelombang, breakdown jenjang target)
        $ukkj_history = $recap->get_ukkj_history($start, $end);

        // Riwayat PDJL (tren per tahun, rincian per gelombang, distribusi asal jabatan)
        $pdjl_history = $recap->get_pdjl_history($start, $end);

        // Rasio kelulusan, rata-rata komponen, dan status verifikasi berkas
        $ratio = [
            'UKKJ' => [
                'dinilai'     => $summary['UKKJ']['dinilai'],
                'lulus'       => $summary['UKKJ']['lulus'],
                'tidak_lulus' => $summary['UKKJ']['tidak_lulus'],
            ],
            'PDJL' => [
                'dinilai'     => $summary['PDJL']['dinilai'],
                'lulus'       => $summary['PDJL']['lulus'],
                'tidak_lulus' => $summary['PDJL']['tidak_lulus'],
            ],
        ];

        $ratio_years    = [
            'UKKJ' => $ukkj_history['years'],
            'PDJL' => $pdjl_history['years'],
        ];
        $avg_component  = $recap->get_avg_component($start, $end);
        $verification   = $recap->get_verification_status($start, $end);

        // Analitik wilayah/instansi
        $region_breakdown = $recap->get_region_breakdown($start, $end);

        $this->_additional_display['summary']          = json_encode($summary);
        $this->_additional_display['summary_data']     = $summary;
        $this->_additional_display['ukkj_history']     = json_encode($ukkj_history);
        $this->_additional_display['ukkj_history_data'] = $ukkj_history;
        $this->_additional_display['pdjl_history']     = json_encode($pdjl_history);
        $this->_additional_display['pdjl_history_data'] = $pdjl_history;
        $this->_additional_display['ratio_data']       = json_encode($ratio);
        $this->_additional_display['ratio_stat']       = $ratio;
        $this->_additional_display['ratio_years']      = json_encode($ratio_years);
        $this->_additional_display['avg_component_jenis'] = json_encode($avg_component);
        $this->_additional_display['verification']     = json_encode($verification);
        $this->_additional_display['verification_data'] = $verification;
        $this->_additional_display['region_breakdown'] = json_encode($region_breakdown);
        $this->_additional_display['region_breakdown_data'] = $region_breakdown;
        $this->_additional_display['filter_start']     = $start;
        $this->_additional_display['filter_end']       = $end;

        // Summary cards
        // Jumlah pegawai dihitung sama dengan data pegawai (hanya yang punya identitas),
        // agar dashboard konsisten dengan jumlah record di halaman Data Pegawai.
        $sum_employee = $db->table('employee.personnel ep')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->where('ipr.full_name IS NOT NULL')
            ->where('ipr.full_name !=', '')
            ->countAllResults(false);

        $sum_assessment = $db->table('assessment.assessment_result')
            ->select('COUNT(DISTINCT employee_id) as total')
            ->get()
            ->getRowArray()['total'] ?? 0;

        $sum_groups = $db->table('assessment.interview_groups')->countAllResults(false);

        $sum_responses = $db->table('assessment.employee_interview_response')->countAllResults(false);

        // Chart 1: Pegawai by Grade (Tingkatan)
        $sum_grade = $db->query("
            SELECT g.grade AS name, COUNT(ep.id) AS total
            FROM employee.personnel ep
            LEFT JOIN instance.positions pos ON ep.position_id = pos.id
            LEFT JOIN employee.grades g ON pos.grade_id = g.id
            WHERE g.grade IS NOT NULL
            GROUP BY g.grade
            ORDER BY COUNT(ep.id) DESC
        ")->getResultArray();

        // Chart 2: Pegawai by Instansi (Top 6)
        $sum_instance = $db->query("
            SELECT io.organization AS name, COUNT(ep.id) AS total
            FROM employee.personnel ep
            LEFT JOIN instance.organization_structures ios ON CAST(ep.organization_structure_id AS VARCHAR) = CAST(ios.id AS VARCHAR)
            LEFT JOIN instance.organizations io ON ios.organization_id = io.id
            WHERE io.organization IS NOT NULL
            GROUP BY io.organization
            ORDER BY total DESC
            LIMIT 6
        ")->getResultArray();

        // Chart 3: Peserta Wawancara per Kelompok (Top 8)
        $sum_kelompok = $db->query("
            SELECT ig.name, COUNT(DISTINCT ip.employee_id) AS total
            FROM assessment.interview_groups ig
            LEFT JOIN assessment.interview_participants ip ON ig.id = ip.interview_group_id
            GROUP BY ig.name
            ORDER BY total DESC
            LIMIT 8
        ")->getResultArray();

        // Chart 4: Rata-rata Skor per Komponen Penilaian
        $avg_score_component = $db->query("
            SELECT ac.name, ROUND(AVG(sa.value)::numeric, 2) as avg_score
            FROM assessment.staging_assessment sa
            JOIN assessment.component_aspect ca ON sa.component_aspect_id = ca.id
            JOIN assessment.assessment_component ac ON ca.assessment_component_id = ac.id
            GROUP BY ac.name
            ORDER BY ac.name
        ")->getResultArray();

        $this->_additional_display['sum_employee']        = $sum_employee;
        $this->_additional_display['sum_assessment']      = $sum_assessment;
        $this->_additional_display['sum_groups']          = $sum_groups;
        $this->_additional_display['sum_responses']       = $sum_responses;
        $this->_additional_display['sum_grade']           = json_encode(array_filter($sum_grade, fn($r) => !empty($r['name'])));
        $this->_additional_display['sum_instance']        = json_encode(array_filter($sum_instance, fn($r) => !empty($r['name'])));
        $this->_additional_display['sum_kelompok']        = json_encode(array_filter($sum_kelompok, fn($r) => !empty($r['name'])));
        $this->_additional_display['avg_score_component'] = json_encode($avg_score_component);

        return parent::_index();
    }

    private function get_report_data($start, $end)
    {
        $recap = new RecapModel();

        return [
            'summary' => $recap->get_summary($start, $end),
            'ukkj'    => $recap->get_ukkj_history($start, $end),
            'pdjl'    => $recap->get_pdjl_history($start, $end),
            'start'   => $start,
            'end'     => $end,
        ];
    }

    private function report_period($start, $end)
    {
        if (empty($start) && empty($end)) {
            return 'Seluruh periode';
        }

        return (!empty($start) ? $start : 'awal') . ' s.d. ' . (!empty($end) ? $end : 'sekarang');
    }

    private function style_sheet_header($sheet, $range)
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    private function build_history_sheet($spreadsheet, $title, $history, $dist_key, $dist_title, $dist_label)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);

        $sheet->setCellValue('A1', $title . ' - Riwayat per Gelombang');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $headers = ['Tahun', 'Gelombang', 'Tanggal', 'Pendaftar', 'Dinilai', 'Lulus', 'Tidak Lulus', 'Belum Dinilai'];
        $col     = 'A';

        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $col++;
        }

        $this->style_sheet_header($sheet, 'A3:H3');

        $row = 4;

        foreach ($history['groups'] as $group) {
            $sheet->fromArray([
                $group['year'],
                $group['name'],
                $group['date'],
                $group['pendaftar'],
                $group['dinilai'],
                $group['lulus'],
                $group['tidak_lulus'],
                $group['belum_dinilai'],
            ], null, 'A' . $row);
            $row++;
        }

        if (empty($history['groups'])) {
            $sheet->setCellValue('A4', 'Belum ada data pada periode ini.');
            $row = 5;
        }

        $row++;
        $sheet->setCellValue('A' . $row, 'Tren per Tahun');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $year_headers = ['Tahun', 'Pendaftar', 'Dinilai', 'Lulus', 'Tidak Lulus', 'Belum Dinilai'];
        $col          = 'A';

        foreach ($year_headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        $this->style_sheet_header($sheet, 'A' . $row . ':F' . $row);
        $row++;

        foreach ($history['years'] as $year => $stat) {
            $sheet->fromArray([
                (string) $year,
                $stat['pendaftar'],
                $stat['dinilai'],
                $stat['lulus'],
                $stat['tidak_lulus'],
                $stat['belum_dinilai'],
            ], null, 'A' . $row);
            $row++;
        }

        $row++;
        $sheet->setCellValue('A' . $row, $dist_title);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue('A' . $row, $dist_label);
        $sheet->setCellValue('B' . $row, 'Jumlah');
        $this->style_sheet_header($sheet, 'A' . $row . ':B' . $row);
        $row++;

        foreach ($history[$dist_key] as $label => $count) {
            $sheet->setCellValue('A' . $row, $label);
            $sheet->setCellValue('B' . $row, $count);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(15);
    }

    public function getExport_excel()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $start  = $this->request->getGet('start');
        $end    = $this->request->getGet('end');
        $data   = $this->get_report_data($start, $end);
        $period = $this->report_period($start, $end);

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Laporan Riwayat Statistik UKKJ & PDJL');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Periode');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->setCellValue('B2', $period);

        $row = 4;

        foreach (['UKKJ' => $data['summary']['UKKJ'], 'PDJL' => $data['summary']['PDJL']] as $jenis => $stat) {
            $sheet->setCellValue('A' . $row, $jenis);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;

            $headers = ['Pendaftar', 'Dinilai', 'Lulus', 'Tidak Lulus', 'Belum Dinilai', 'Kelompok'];
            $col     = 'A';

            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $col++;
            }

            $this->style_sheet_header($sheet, 'A' . $row . ':F' . $row);
            $row++;

            $values = [
                $stat['pendaftar'],
                $stat['dinilai'],
                $stat['lulus'],
                $stat['tidak_lulus'],
                $stat['belum_dinilai'],
                $stat['kelompok'],
            ];
            $col = 'A';

            foreach ($values as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }

            $row += 2;
        }

        foreach (['A' => 18, 'B' => 14, 'C' => 12, 'D' => 14, 'E' => 15, 'F' => 12] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $this->build_history_sheet($spreadsheet, 'Riwayat UKKJ', $data['ukkj'], 'jenjang', 'Breakdown Jenjang Target', 'Jenjang');
        $this->build_history_sheet($spreadsheet, 'Riwayat PDJL', $data['pdjl'], 'jabatan', 'Distribusi Asal Jabatan', 'Asal Jabatan');

        $writer = new Xlsx($spreadsheet);
        $file   = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($file);
        $content = file_get_contents($file);
        @unlink($file);
        $spreadsheet->disconnectWorksheets();

        $filename = 'riwayat-statistik-ukkj-pdjl-' . date('Ymd-His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }

    public function getExport_pdf()
    {
        if (!is_authorized($this->_menu_code, 'view')) {
            return access_denied();
        }

        $start  = $this->request->getGet('start');
        $end    = $this->request->getGet('end');
        $data   = $this->get_report_data($start, $end);
        $period = $this->report_period($start, $end);

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('dashboard/export_pdf', [
            'summary' => $data['summary'],
            'ukkj'    => $data['ukkj'],
            'pdjl'    => $data['pdjl'],
            'period'  => $period,
            'printed' => date('d-m-Y H:i'),
        ]));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'riwayat-statistik-ukkj-pdjl-' . date('Ymd-His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }
}
