<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---------------------------------------------------------------------
// Public (no auth)
// ---------------------------------------------------------------------
$routes->get('login',                'Auth::loginForm');
$routes->post('login',               'Auth::login');
$routes->get('logout',               'Auth::logout');
$routes->get('forgot-password',      'Auth::forgotForm');
$routes->post('forgot-password',     'Auth::forgot');
$routes->get('reset-password/(:any)', 'Auth::resetForm/$1');
$routes->post('reset-password/(:any)', 'Auth::reset/$1');

// ---------------------------------------------------------------------
// Authenticated
// ---------------------------------------------------------------------
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    $routes->get('/', 'Dashboard::index');

    // ---------- Members ----------
    $routes->group('members', ['filter' => 'permission:member.view'], static function ($r) {
        $r->get('/',                  'Members::index');
        $r->get('export',             'Members::export');
        $r->get('import',             'Members::importForm');
        $r->post('import',            'Members::import');
        $r->get('create',             'Members::create');
        $r->post('store',             'Members::store');
        $r->get('(:num)',             'Members::show/$1');
        $r->get('(:num)/edit',        'Members::edit/$1');
        $r->post('(:num)/update',     'Members::update/$1');
        $r->post('(:num)/delete',     'Members::delete/$1');
        $r->post('(:num)/renew',      'Members::renew/$1');
    });

    // ---------- Plans ----------
    $routes->group('plans', ['filter' => 'permission:plan.manage'], static function ($r) {
        $r->get('/',                  'Plans::index');
        $r->get('create',             'Plans::create');
        $r->post('store',             'Plans::store');
        $r->get('(:num)/edit',        'Plans::edit/$1');
        $r->post('(:num)/update',     'Plans::update/$1');
        $r->post('(:num)/delete',     'Plans::delete/$1');
    });

    // ---------- Payments ----------
    $routes->group('payments', ['filter' => 'permission:payment.view,payment.create'], static function ($r) {
        $r->get('/',                  'Payments::index');
        $r->get('export',             'Payments::export');
        $r->get('create',             'Payments::create');
        $r->post('store',             'Payments::store');
        $r->get('(:num)',             'Payments::show/$1');
        $r->post('(:num)/approve',    'Payments::approve/$1');
        $r->post('(:num)/reject',     'Payments::reject/$1');
        $r->post('(:num)/reverse',    'Payments::reverse/$1');
        $r->post('(:num)/delete',     'Payments::delete/$1');
    });

    // ---------- Invoices ----------
    $routes->group('invoices', ['filter' => 'permission:invoice.view'], static function ($r) {
        $r->get('/',                  'Invoices::index');
        $r->get('export',             'Invoices::export');
        $r->get('(:num)',             'Invoices::show/$1');
        $r->get('(:num)/pdf',         'Invoices::pdf/$1');
        $r->post('(:num)/email',      'Invoices::email/$1');
        $r->post('(:num)/einvoice/submit',  'Invoices::einvoiceSubmit/$1');
        $r->post('(:num)/einvoice/cancel',  'Invoices::einvoiceCancel/$1');
        $r->post('(:num)/einvoice/refresh', 'Invoices::einvoiceRefresh/$1');
        $r->post('generate-for-member/(:num)', 'Invoices::generateForMember/$1');
    });

    // ---------- Receipts ----------
    $routes->group('receipts', ['filter' => 'permission:receipt.view'], static function ($r) {
        $r->get('/',                  'Receipts::index');
        $r->get('export',             'Receipts::export');
        $r->get('(:num)',             'Receipts::show/$1');
        $r->get('(:num)/pdf',         'Receipts::pdf/$1');
    });

    // ---------- Reports ----------
    $routes->group('reports', ['filter' => 'permission:report.view'], static function ($r) {
        $r->get('/',                       'Reports::index');
        $r->get('export/monthly',          'Reports::exportMonthly');
        $r->get('export/outstanding',      'Reports::exportOutstanding');
    });

    // ---------- Users ----------
    $routes->group('users', ['filter' => 'permission:user.manage'], static function ($r) {
        $r->get('/',                  'Users::index');
        $r->get('create',             'Users::create');
        $r->post('store',             'Users::store');
        $r->get('(:num)/edit',        'Users::edit/$1');
        $r->post('(:num)/update',     'Users::update/$1');
        $r->post('(:num)/delete',     'Users::delete/$1');
    });

    // ---------- Roles ----------
    $routes->group('roles', ['filter' => 'permission:role.manage'], static function ($r) {
        $r->get('/',                  'Roles::index');
        $r->get('(:num)/edit',        'Roles::edit/$1');
        $r->post('(:num)/update',     'Roles::update/$1');
    });

    // ---------- Settings ----------
    $routes->group('settings', ['filter' => 'permission:setting.manage'], static function ($r) {
        $r->get('/',                  'Settings::index');
        $r->post('update',            'Settings::update');

        // Dropdown options management
        $r->get('dropdown-options',                 'DropdownOptions::index');
        $r->get('dropdown-options/create',          'DropdownOptions::create');
        $r->post('dropdown-options/store',          'DropdownOptions::store');
        $r->get('dropdown-options/(:num)/edit',     'DropdownOptions::edit/$1');
        $r->post('dropdown-options/(:num)/update',  'DropdownOptions::update/$1');
        $r->post('dropdown-options/(:num)/delete',  'DropdownOptions::delete/$1');
    });

    // ---------- Audit log ----------
    $routes->group('audit-log', ['filter' => 'permission:audit.view'], static function ($r) {
        $r->get('/',                  'AuditLog::index');
    });

    // ---------- Notifications ----------
    $routes->get('notifications',                 'Notifications::index');
    $routes->post('notifications/mark-all-read',  'Notifications::markAllRead');
});
