<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    /**
     * Usage: ['filter' => 'permission:member.view']
     *        ['filter' => 'permission:invoice.view,invoice.generate']  (any-of)
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['auth', 'permission']);

        if (! is_logged_in()) {
            return redirect()->to('/login');
        }

        if (! $arguments) {
            return null;
        }
        if (can_any($arguments)) {
            return null;
        }
        return service('response')->setStatusCode(403)
            ->setBody(view('errors/html/error_403', ['message' => 'You do not have permission to access this page.']));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
