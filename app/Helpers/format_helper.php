<?php

if (! function_exists('money')) {
    function money($amount, string $prefix = 'RM '): string
    {
        return $prefix . number_format((float) $amount, 2, '.', ',');
    }
}

if (! function_exists('fdate')) {
    function fdate($value, string $format = 'd M Y'): string
    {
        if (! $value) {
            return '-';
        }
        $ts = is_numeric($value) ? (int) $value : strtotime((string) $value);
        return $ts ? date($format, $ts) : '-';
    }
}

if (! function_exists('fdatetime')) {
    function fdatetime($value, string $format = 'd M Y H:i'): string
    {
        return fdate($value, $format);
    }
}

if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'active'    => 'success',
            'expired'   => 'secondary',
            'suspended' => 'warning',
            'pending'   => 'warning',
            'confirmed' => 'success',
            'rejected'  => 'danger',
            'paid'      => 'success',
            'issued'    => 'info',
            'draft'     => 'secondary',
            'cancelled' => 'danger',
            'disabled'  => 'secondary',
        ];
        $color = $map[$status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . esc(ucfirst($status)) . '</span>';
    }
}

if (! function_exists('flash_alerts')) {
    function flash_alerts(): string
    {
        $session = session();
        $out = '';
        foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $cls) {
            if ($msg = $session->getFlashdata($key)) {
                $out .= '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
                      . esc($msg)
                      . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            }
        }
        return $out;
    }
}
