<?php

if (! function_exists('user_permissions')) {
    /**
     * Return a flat list of permission slugs granted to the current user.
     * Cached on the session.
     */
    function user_permissions(): array
    {
        $session = session();
        $cached  = $session->get('permissions');
        if (is_array($cached)) {
            return $cached;
        }

        $userId = $session->get('user_id');
        if (! $userId) {
            return [];
        }

        $db = \Config\Database::connect();
        $rows = $db->table('users u')
            ->select('p.slug')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->join('role_permissions rp', 'rp.role_id = r.id', 'left')
            ->join('permissions p', 'p.id = rp.permission_id', 'left')
            ->where('u.id', $userId)
            ->where('u.deleted_at', null)
            ->get()->getResultArray();

        $slugs = array_values(array_filter(array_unique(array_column($rows, 'slug'))));
        $session->set('permissions', $slugs);
        return $slugs;
    }
}

if (! function_exists('can')) {
    function can(string $permission): bool
    {
        $perms = user_permissions();
        if (in_array('*', $perms, true)) {
            return true;
        }
        // super_admin always allowed
        if (current_role_slug() === 'super_admin') {
            return true;
        }
        return in_array($permission, $perms, true);
    }
}

if (! function_exists('can_any')) {
    function can_any(array $permissions): bool
    {
        if (current_role_slug() === 'super_admin') {
            return true;
        }
        $perms = user_permissions();
        foreach ($permissions as $p) {
            if (in_array($p, $perms, true)) {
                return true;
            }
        }
        return false;
    }
}
