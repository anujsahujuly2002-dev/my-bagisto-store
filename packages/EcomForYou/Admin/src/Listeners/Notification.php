<?php

namespace EcomForYou\Admin\Listeners;

use EcomForYou\Admin\Events\NotificationCreated;
use EcomForYou\Admin\Notifications\AdminNotifications;
use EcomForYou\Notification\Contracts\Notification as NotificationContract;
use EcomForYou\Notification\Repositories\NotificationRepository;
use EcomForYou\Notification\TypeRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Notification
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected NotificationRepository $notificationRepository,
        protected TypeRegistry $typeRegistry
    ) {}

    /**
     * Mark a new admin notification read for the admin whose own action raised it, where its type allows,
     * and broadcast it to the panel.
     */
    public function afterCreated(NotificationContract $notification): void
    {
        if ($notification->audience !== AdminNotifications::AUDIENCE) {
            return;
        }

        if (
            $this->typeRegistry->instance($notification->type)->shouldMarkReadForCauser()
            && $admin = $this->causingAdmin()
        ) {
            $this->notificationRepository->markRead($notification->id, $admin);
        }

        $this->broadcast(new NotificationCreated($notification->id, $notification->type));
    }

    /**
     * The admin whose own action in the panel raised the notification.
     */
    protected function causingAdmin(): ?Model
    {
        if (! is_admin_request()) {
            return null;
        }

        return auth()->guard('admin')->user();
    }

    /**
     * Broadcast the notification without letting an unreachable broadcaster fail what raised it.
     */
    protected function broadcast(NotificationCreated $event): void
    {
        try {
            event($event);
        } catch (\Exception $e) {
            Log::error('Notification Broadcast Failed: '.$e->getMessage());
        }
    }
}
