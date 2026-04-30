<?php

namespace App\Controllers;

use App\Libraries\ExcelExporter;

class Reports extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $month = $this->request->getGet('month') ?: date('Y-m');

        $summary = $db->query("
            SELECT
              (SELECT COUNT(*) FROM members WHERE deleted_at IS NULL AND status='active')  AS active_members,
              (SELECT COUNT(*) FROM members WHERE deleted_at IS NULL AND status='expired') AS expired_members,
              (SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed' AND deleted_at IS NULL AND DATE_FORMAT(payment_date,'%Y-%m')=?) AS month_collection,
              (SELECT COUNT(*) FROM invoices WHERE deleted_at IS NULL AND status='issued') AS outstanding_count,
              (SELECT COALESCE(SUM(total),0) FROM invoices WHERE deleted_at IS NULL AND status='issued') AS outstanding_total
        ", [$month])->getRowArray();

        $byPlan = $db->query("
            SELECT mp.name, COALESCE(SUM(p.amount),0) AS total, COUNT(p.id) AS cnt
            FROM payments p
            JOIN members m ON m.id = p.member_id
            LEFT JOIN membership_plans mp ON mp.id = m.plan_id
            WHERE p.status='confirmed' AND p.deleted_at IS NULL
              AND DATE_FORMAT(p.payment_date,'%Y-%m') = ?
            GROUP BY mp.id ORDER BY total DESC
        ", [$month])->getResultArray();

        return view('reports/index', compact('summary', 'byPlan', 'month'));
    }

    public function exportMonthly()
    {
        if (! can('report.export')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $month = $this->request->getGet('month') ?: date('Y-m');
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT r.receipt_no, m.membership_id, m.name AS member, r.amount, r.issued_at, p.method, p.reference_no
            FROM receipts r
            JOIN members m ON m.id = r.member_id
            LEFT JOIN payments p ON p.id = r.payment_id
            WHERE DATE_FORMAT(r.issued_at, '%Y-%m') = ?
            ORDER BY r.issued_at
        ", [$month])->getResultArray();

        ExcelExporter::download(
            "monthly_report_{$month}.xlsx",
            ['Receipt No', 'Membership ID', 'Member', 'Amount', 'Issued', 'Method', 'Reference'],
            array_map(fn ($r) => array_values($r), $rows)
        );
    }

    public function exportOutstanding()
    {
        if (! can('report.export')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT i.invoice_no, m.membership_id, m.name AS member, i.total, i.issued_at, i.due_at,
                   DATEDIFF(CURDATE(), i.due_at) AS days_overdue
            FROM invoices i
            JOIN members m ON m.id = i.member_id
            WHERE i.deleted_at IS NULL AND i.status = 'issued'
            ORDER BY i.due_at
        ")->getResultArray();
        ExcelExporter::download(
            'outstanding_' . date('Ymd_His') . '.xlsx',
            ['Invoice No', 'Membership ID', 'Member', 'Total', 'Issued', 'Due', 'Days Overdue'],
            array_map(fn ($r) => array_values($r), $rows)
        );
    }
}
