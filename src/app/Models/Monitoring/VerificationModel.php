<?php

namespace App\Models\Monitoring;

use CodeIgniter\Model;

class VerificationModel extends Model
{
    protected $table      = 'assessment.interview_groups';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    private const STATUSES = ['draft', 'menunggu', 'disetujui', 'ditolak'];

    private function group_joins(): string
    {
        return "LEFT JOIN (
                    SELECT DISTINCT ON (ip.interview_group_id) ip.interview_group_id, io.organization
                    FROM assessment.interview_participants ip
                    JOIN employee.personnel ep ON ep.id = ip.employee_id
                    JOIN instance.organization_structures os ON os.id = ep.organization_structure_id
                    JOIN instance.organizations io ON io.id = os.organization_id
                    WHERE io.organization IS NOT NULL
                    GROUP BY ip.interview_group_id, io.organization
                    ORDER BY ip.interview_group_id, COUNT(*) DESC
                ) org ON org.interview_group_id = ig.id
                LEFT JOIN (
                    SELECT ip.interview_group_id,
                           COUNT(DISTINCT ip.employee_id) AS peserta,
                           COUNT(DISTINCT ep.id) FILTER (WHERE ep.is_verified = '1') AS terverifikasi
                    FROM assessment.interview_participants ip
                    LEFT JOIN employee.personnel ep ON ep.id = ip.employee_id
                    GROUP BY ip.interview_group_id
                ) p ON p.interview_group_id = ig.id";
    }

    private function filters($start = NULL, $end = NULL, $jenis = NULL, $status = NULL, $search = NULL): array
    {
        $where  = ['1 = 1'];
        $params = [];

        if (!empty($start)) {
            $where[]  = 'ig.created_at >= ?';
            $params[] = $start . ' 00:00:00';
        }

        if (!empty($end)) {
            $where[]  = 'ig.created_at <= ?';
            $params[] = $end . ' 23:59:59';
        }

        if (!empty($jenis)) {
            $where[]  = 'ig.jenis = ?';
            $params[] = $jenis;
        }

        if (!empty($status)) {
            $where[]  = 'ig.verification_status = ?';
            $params[] = $status;
        }

        if ($search !== NULL && trim($search) !== '') {
            $term     = '%' . trim($search) . '%';
            $where[]  = "(ig.name ILIKE ? OR ig.instance_code ILIKE ? OR COALESCE(org.organization, ig.instance_code) ILIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        return [implode(' AND ', $where), $params];
    }

    public function get_summary($start = NULL, $end = NULL, $jenis = NULL): array
    {
        $sql = "SELECT ig.verification_status AS status, COUNT(*) AS total
                FROM assessment.interview_groups ig";
        $params = [];

        if (!empty($start)) {
            $sql .= " WHERE ig.created_at >= ?";
            $params[] = $start . ' 00:00:00';
        }

        if (!empty($end)) {
            $sql .= empty($params) ? " WHERE" : " AND";
            $sql .= " ig.created_at <= ?";
            $params[] = $end . ' 23:59:59';
        }

        if (!empty($jenis)) {
            $sql .= empty($params) ? " WHERE" : " AND";
            $sql .= " ig.jenis = ?";
            $params[] = $jenis;
        }

        $sql .= " GROUP BY ig.verification_status";

        $rows   = $this->db->query($sql, $params)->getResultArray();
        $result = ['draft' => 0, 'menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0, 'total' => 0];

        foreach ($rows as $row) {
            $status = in_array($row['status'], self::STATUSES, TRUE) ? $row['status'] : 'menunggu';
            $count  = (int) $row['total'];

            $result[$status] += $count;
            $result['total'] += $count;
        }

        return $result;
    }

    public function count_all($start = NULL, $end = NULL, $jenis = NULL, $status = NULL): int
    {
        [$where, $params] = $this->filters($start, $end, $jenis, $status);

        $sql = "SELECT COUNT(*) AS total
                FROM assessment.interview_groups ig
                " . $this->group_joins() . "
                WHERE " . $where;

        return (int) $this->db->query($sql, $params)->getRow()->total;
    }

    public function count_filtered($start = NULL, $end = NULL, $jenis = NULL, $status = NULL, $search = NULL): int
    {
        [$where, $params] = $this->filters($start, $end, $jenis, $status, $search);

        $sql = "SELECT COUNT(*) AS total
                FROM assessment.interview_groups ig
                " . $this->group_joins() . "
                WHERE " . $where;

        return (int) $this->db->query($sql, $params)->getRow()->total;
    }

    public function get_datatables($start = NULL, $end = NULL, $jenis = NULL, $status = NULL, $search = NULL, $limit = 10, $offset = 0): array
    {
        [$where, $params] = $this->filters($start, $end, $jenis, $status, $search);

        $sql = "SELECT ig.id, ig.name, ig.instance_code, ig.level, ig.jenis, ig.created_at, ig.verification_status,
                       COALESCE(org.organization, ig.instance_code) AS pengusul,
                       COALESCE(p.peserta, 0) AS peserta,
                       COALESCE(p.terverifikasi, 0) AS terverifikasi
                FROM assessment.interview_groups ig
                " . $this->group_joins() . "
                WHERE " . $where . "
                ORDER BY ig.created_at DESC, ig.name ASC
                LIMIT ? OFFSET ?";

        $params[] = (int) $limit;
        $params[] = (int) $offset;

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function update_status($id, $status): bool
    {
        if (!in_array($status, self::STATUSES, TRUE)) {
            return FALSE;
        }

        return $this->db->table('assessment.interview_groups')
            ->where('id', $id)
            ->update([
                'verification_status' => $status,
                'updated_id'          => session()->get('login_user_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
    }
}
