<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function loginForm()
    {
        if (is_logged_in()) {
            return redirect()->to('/');
        }
        return view('auth/login');
    }

    public function login()
    {
        $login    = trim((string) $this->request->getPost('login'));
        $password = (string) $this->request->getPost('password');

        $users = new UserModel();
        $user  = $users->findForLogin($login);
        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->with('error', 'Invalid credentials.')->withInput();
        }

        $full = $users->withRole((int) $user['id']);
        $session = session();
        $session->regenerate();
        $session->set([
            'user_id'       => (int) $full['id'],
            'user'          => [
                'id'        => (int) $full['id'],
                'name'      => $full['name'],
                'email'     => $full['email'],
                'username'  => $full['username'],
                'role_id'   => (int) $full['role_id'],
                'role_slug' => $full['role_slug'],
                'role_name' => $full['role_name'],
            ],
            'last_activity' => time(),
            'idle_timeout'  => 60 * 120,
        ]);
        $users->update($full['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        (new AuditLogModel())->insert([
            'user_id'    => (int) $full['id'],
            'action'     => 'login',
            'entity'     => 'users',
            'entity_id'  => (string) $full['id'],
            'ip'         => $this->request->getIPAddress(),
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 240),
        ]);

        return redirect()->to('/')->with('success', 'Welcome back, ' . $full['name'] . '!');
    }

    public function logout()
    {
        $userId = current_user_id();
        if ($userId) {
            (new AuditLogModel())->insert([
                'user_id'   => $userId,
                'action'    => 'logout',
                'entity'    => 'users',
                'entity_id' => (string) $userId,
                'ip'        => $this->request->getIPAddress(),
            ]);
        }
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Logged out.');
    }

    public function forgotForm()
    {
        return view('auth/forgot');
    }

    public function forgot()
    {
        $email = trim((string) $this->request->getPost('email'));
        $users = new UserModel();
        $user  = $users->where('email', $email)->first();

        // Always show success for privacy.
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $users->update($user['id'], [
                'reset_token'      => $token,
                'reset_expires_at' => $expires,
            ]);
            log_message('info', 'Password reset for {email}: {url}', [
                'email' => $email,
                'url'   => site_url('reset-password/' . $token),
            ]);
        }
        return redirect()->to('/login')
            ->with('success', 'If that email exists, a reset link has been issued.');
    }

    public function resetForm(string $token)
    {
        return view('auth/reset', ['token' => $token]);
    }

    public function reset(string $token)
    {
        $password = (string) $this->request->getPost('password');
        $confirm  = (string) $this->request->getPost('password_confirm');
        if (strlen($password) < 8 || $password !== $confirm) {
            return redirect()->back()->with('error', 'Passwords must match and be at least 8 characters.');
        }
        $users = new UserModel();
        $user  = $users->where('reset_token', $token)
                       ->where('reset_expires_at >=', date('Y-m-d H:i:s'))
                       ->first();
        if (! $user) {
            return redirect()->to('/forgot-password')->with('error', 'Invalid or expired reset link.');
        }
        $users->update($user['id'], [
            'password_hash'    => password_hash($password, PASSWORD_BCRYPT),
            'reset_token'      => null,
            'reset_expires_at' => null,
        ]);
        return redirect()->to('/login')->with('success', 'Password updated. Please sign in.');
    }
}
