<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\createMessage;
use App\wsClient;

class settingsController extends Controller {

   public function __construct()
   {
      $this->middleware('auth');
   }


   public function index() {
      return view('settings');
   }


   public function sendNotification() {
      $currentUser = Auth::user();
      $numMessages = $currentUser->unreadNotifications->count();

      // Connects anonymously to WebSocket Server to send a message.
      $clientSocket = new wsClient();
      $clientSocket->sendMsg(array('action' => 'notify', 'to' => $currentUser->name, 'message' => $numMessages));
   }
}
