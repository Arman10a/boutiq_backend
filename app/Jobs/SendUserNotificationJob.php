<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Notifications\UserRegisteredNotification;

class SendUserNotificationJob implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        public function __construct(
            protected User $newUser,
            protected UserRegisteredNotification $userRegisteredNotification,
        ){}

        public function handle()
            {
                $this->newUser->notify($this->userRegisteredNotification);
            }
    }
