<?php

use App\Libraries\SettingsService;

if (! function_exists('module_enabled')) {
    /**
     * Check whether a feature module is enabled in system settings.
     *
     * Lookup order:
     *   1. `module.<name>.enabled` (canonical key managed by Super Admin via /settings)
     *   2. `<name>.enabled`        (legacy keys, e.g. einvoice.enabled)
     *
     * Defaults to true when the key is absent so a fresh install with no settings
     * row never silently disables a feature.
     */
    function module_enabled(string $name): bool
    {
        $primary = SettingsService::get('module.' . $name . '.enabled');
        if ($primary !== null && $primary !== '') {
            return (string) $primary === '1' || strtolower((string) $primary) === 'true';
        }
        $legacy = SettingsService::get($name . '.enabled');
        if ($legacy !== null && $legacy !== '') {
            return (string) $legacy === '1' || strtolower((string) $legacy) === 'true';
        }
        return true;
    }
}

if (! function_exists('module_disabled_response')) {
    /**
     * Render a standard "module disabled" page (404). Used by ModuleFilter and
     * any controller action that needs to short-circuit a disabled feature.
     */
    function module_disabled_response(string $module): \CodeIgniter\HTTP\ResponseInterface
    {
        $body = view('errors/html/error_404', [
            'message' => 'The "' . $module . '" module is currently disabled by the administrator.',
        ]);
        return service('response')->setStatusCode(404)->setBody($body);
    }
}
