<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\wsClient;

class wsMonitor extends Controller
{
   /**
    * Create a new controller instance.
    *
    * @return void
    */
   public function __construct()
   {
      $this->middleware('auth');
   }


   /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
   public function index()
   {
      return view('wsMonitor');
   }


   public function sendNotification() {
      $currentUser = Auth::user();
      $numMessages = $currentUser->unreadNotifications->count();

      // Connects anonymously to WebSocket Server to send a message.
      $clientSocket = new wsClient();
      $clientSocket->sendMsg(array('action' => 'notify', 'to' => $currentUser->name, 'message' => $numMessages));
   }


   public function getLog() {
      $fileContent = Storage::disk('logs')->get('websocket.log');
      $fileContent = explode('[', $fileContent);
      return response()->json($fileContent);
   }

}
