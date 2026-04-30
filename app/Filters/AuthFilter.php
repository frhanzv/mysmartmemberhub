<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (! $session->get('user_id')) {
            return redirect()->to('/landing');
        }

        // session timeout (idle)
        $timeout = (int) ($session->get('idle_timeout') ?: 7200);
        $last    = (int) ($session->get('last_activity') ?: time());
        if (time() - $last > $timeout) {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Session timed out. Please log in again.');
        }
        $session->set('last_activity', time());

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
