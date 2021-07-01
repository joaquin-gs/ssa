<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;
use App\wsClient;

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
      
      //$clientSocket = new wsClient();
      //$clientSocket->connect(array('action' => 'notify', 'to' => 'Anatolio,Joaquin,Caralampio', 'message' => $event->notification->data));
   }
}
