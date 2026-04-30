<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\MemberModel;
use App\Models\PaymentModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $members  = new MemberModel();
        $payments = new PaymentModel();
        $invoices = new InvoiceModel();
        $db       = \Config\Database::connect();

        // refresh statuses so KPIs are accurate
        $members->refreshStatuses();

        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        $stats = [
            'members_active' => (int) $members->where('status', 'active')->countAllResults(),
            'members_expired' => (int) $members->where('status', 'expired')->countAllResults(),
            'expiring_soon' => (int) $members->where('status', 'active')
                ->where('expiry_date <=', date('Y-m-d', strtotime('+14 days')))
                ->countAllResults(),
            'today_collection' => (float) $db->table('payments')
                ->selectSum('amount', 't')
                ->where('status', 'confirmed')
                ->where('payment_date', $today)
                ->where('deleted_at', null)
                ->get()->getRow('t'),
            'month_collection' => (float) $db->table('payments')
                ->selectSum('amount', 't')
                ->where('status', 'confirmed')
                ->where('payment_date >=', $monthStart)
                ->where('deleted_at', null)
                ->get()->getRow('t'),
            'pending_payments' => (int) $payments->where('status', 'pending')->countAllResults(),
            'outstanding_invoices' => (int) $invoices->where('status', 'issued')->countAllResults(),
        ];

        // collection by month last 12 months
        $monthly = $db->query("
            SELECT DATE_FORMAT(payment_date, '%Y-%m') AS m, COALESCE(SUM(amount),0) AS t
            FROM payments
            WHERE status = 'confirmed'
              AND deleted_at IS NULL
              AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
            GROUP BY m ORDER BY m
        ")->getResultArray();

        $expiring = $members->select('members.*, mp.name AS plan_name')
            ->join('membership_plans mp', 'mp.id = members.plan_id', 'left')
            ->where('members.status', 'active')
            ->where('members.expiry_date <=', date('Y-m-d', strtotime('+30 days')))
            ->orderBy('members.expiry_date', 'ASC')
            ->limit(8)
            ->find();

        $pending = $payments->select('payments.*, m.name AS member_name, m.membership_id')
            ->join('members m', 'm.id = payments.member_id', 'left')
            ->where('payments.status', 'pending')
            ->orderBy('payments.created_at', 'DESC')
            ->limit(8)
            ->find();

        return view('dashboard/index', compact('stats', 'monthly', 'expiring', 'pending'));
    }
}
