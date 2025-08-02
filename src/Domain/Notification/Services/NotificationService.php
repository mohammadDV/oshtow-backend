<?php

namespace Domain\Notification\Services;

use Domain\Notification\Models\Notification;
use Domain\User\Models\User;
use Application\Api\User\Notifications\EmailNotification;

class NotificationService
{
    const PROFILE = 'profile';
    const PASSENGER = 'passenger';
    const SENDER = 'sender';
    const CHAT = 'chat';
    const CLAIM = 'claim';
    const WALLET = 'wallet';
    const WITHDRAWAL = 'withdrawal';

    /**
     * Create and send notification
     * @param array $info
     * @param User $user
     * @param bool $hasEmail
     * @return LengthAwarePaginator
     */
    static public function create(array $info, User $user, bool $hasEmail = true)
    {

        Notification::create([
            'title' => $info['title'],
            'content' => $info['content'],
            'status' => 1,
            'user_id' => $user->id,
            'model_id' => $info['id'] ?? null,
            'model_type' => $info['type'] ?? null,
        ]);


        if ($hasEmail) {
            $user->notify(new EmailNotification($info['title'], $info['content']));
        }

    }

}