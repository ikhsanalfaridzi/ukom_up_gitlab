<?php

namespace App\Controllers\Home;

use App\Controllers\KV_Controller;
use Config\Database;

class Dashboard extends KV_Controller
{
    function __construct()
    {
        parent::__construct(TRUE);
        $this->_title          = 'Dashboard';
        $this->_uri_name       = 'home/dashboard';
        $this->_uri_name_view  = 'dashboard';
        $this->_menu_code      = 'dashboard';
    }

    public function getIndex()
    {
        $db = Database::connect();

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
}
