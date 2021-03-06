<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\wsClient;
use Illuminate\Support\Facades\Auth;

class LogNotification
{
   /**
    * Create the event listener.
    *
    * @return void
    */
   public function __construct()
   {
      //
   }


   /**
    * Handle the event.
    * This listener class is configured in \app\Providers\EventServiceProvider.php
    * to listen to notifications sent.
    * https://laravel.com/docs/8.x/notifications#notification-events
    *
    * @param  NotificationSent  $event
    * @return void
    */
   public function handle(NotificationSent $event)
   {
      // $event->channel
      // $event->notifiable
      // $event->notification
      // $event->response

      $user = $event->notifiable->name;
      $numMessages = Auth::user()->unreadNotifications->count();
      $clientSocket = new wsClient();
      $clientSocket->sendMsg(array('action'=>'notify', 'to'=>$user, 'totalMsg'=>$numMessages));
   }
}
