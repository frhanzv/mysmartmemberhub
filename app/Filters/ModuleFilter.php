<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Gate route groups behind a module toggle.
 *
 *   ['filter' => 'module:payments']
 *   ['filter' => 'module:einvoice']
 *
 * When the corresponding `module.<name>.enabled` setting is false, the route
 * returns a 404 with a friendly "module disabled" message.
 */
class ModuleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('module');

        if (empty($arguments)) {
            return null;
        }
        // Argument is one or more module names (any-of-disabled blocks the route).
        foreach ((array) $arguments as $name) {
            if (! module_enabled($name)) {
                return module_disabled_response($name);
            }
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
