<?php

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        $session = session();
        if (! $session->get('user_id')) {
            return null;
        }
        return $session->get('user') ?: null;
    }
}

if (! function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        $id = session()->get('user_id');
        return $id ? (int) $id : null;
    }
}

if (! function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return (bool) session()->get('user_id');
    }
}

if (! function_exists('current_role_slug')) {
    function current_role_slug(): ?string
    {
        $u = current_user();
        return $u['role_slug'] ?? null;
    }
}
