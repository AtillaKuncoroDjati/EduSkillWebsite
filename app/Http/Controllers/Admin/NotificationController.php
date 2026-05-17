<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;

class NotificationController extends Controller
{
    /**
     * Endpoint polling: dipanggil berkala oleh admin panel untuk
     * mengambil notifikasi terbaru beserta jumlah yang belum dibaca.
     */
    public function poll()
    {
        $items = AdminNotification::orderByDesc('updated_at')
            ->limit(20)
            ->get();

        $unread = AdminNotification::where('is_read', false)->count();

        return response()->json([
            'unread_count' => $unread,
            'items' => $items->map(function (AdminNotification $n) {
                return [
                    'id'         => $n->id,
                    'type'       => $n->type,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'link'       => $n->link,
                    'is_read'    => (bool) $n->is_read,
                    'time'       => $n->created_at->diffForHumans(),
                    'updated_at' => $n->updated_at->valueOf(), // epoch ms — penanda urut untuk client
                ];
            }),
        ]);
    }

    public function markRead($id)
    {
        AdminNotification::where('id', $id)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }
}
