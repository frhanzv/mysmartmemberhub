<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    public function index()
    {
        $userId = current_user_id();
        $rows = (new NotificationModel())
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->paginate(20);
        $pager = (new NotificationModel())->pager;
        return view('notifications/index', compact('rows', 'pager'));
    }

    public function markAllRead()
    {
        (new NotificationModel())->markRead((int) current_user_id());
        return redirect()->to('notifications')->with('success', 'All notifications marked as read.');
    }
}
