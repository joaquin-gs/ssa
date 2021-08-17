<?php
namespace App\Http\Controllers;

use Ratchet\ConnectionInterface;   /* Provides functions send() and close() */
use Ratchet\WebSocket\MessageComponentInterface;   /* Provides events onOpen(), onClose(), onError(), onMessage() */
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\HomeController;

class WebSocketController implements MessageComponentInterface {
   // Colors used in the console output.
   private $inverse = "\033[7m";
   private $bold = "\033[1m";
   private $reset = "\033[0m";
   private $red = "\033[91m"; 
   private $green = "\033[92m";
   private $blue = "\033[34m";
   private $yellow = "\033[93m";
   private $cyan = "\033[96m";
   private $white = "\033[97m";
   private $gray = "\033[37m";

   protected $client;
   protected $clientId;
   protected $names;

   public function __construct() {
      $this->client = [];
      $this->clientId = [];
      $this->names= [];
      echo " WebSocket server started... " . PHP_EOL;
   }
   
   
   public function onOpen(ConnectionInterface $conn) {
      // Store the new connection to send messages to.
      array_push($this->client, $conn);
      array_push($this->clientId, $conn->resourceId);
      echo " \n";
      $this->Log("  New connection ({$conn->resourceId}) established on {$this->green}" . date('Y/m/d h:i:sa') . "{$this->gray}\n");
   }


   /**
    * @method onMessage
    * @param  ConnectionInterface $conn
    * @param  string              $msg
    * The $msg parameter is a JSON string indicating a username 
    * that is about to be registered in the WebSocket Server or
    * can be the list of users the message should be sent to. 
    * Examples: 
    *   {'username': 'John'}
    *   {'to': 'John,Jane,Paul,Rebecca,...', 'message': 'Lorem ipsum dolor sit amet, consectetur adipiscing elit'}
    * 
    * Additional functionality can be given to the WebSocket Server by adding different
    * fields in the $msg parameter JSON string and evaluating them inside this event.
    */   
   public function onMessage(ConnectionInterface $from, $msg) {
      // The message sent to the server.
      $data = json_decode($msg);

      // The following line is for debugging purposes only
      $this->Log("   Message received: " . $msg);

      if (isset($data->action)) {
         switch ($data->action) {
            case "connect":
               // Adds a new user to the $names array.
               if ($data->username != '') {
                  array_push($this->names, $data->username);
                  $this->Log("  " . $data->username . " has joined." . PHP_EOL);

                  $totMsg = HomeController::getTotalUnreadMessages($data->username);
                  $from->send(json_encode(array('to'=>$data->username, 'totalMsg'=>$totMsg)));
                  $this->showConnectedUsers();
               }
               break;

            case "getTotalUnreadMessages":
               $totMsg = HomeController::getTotalUnreadMessages($data->username);
               $from->send(json_encode(array('to'=>$data->username, 'totalMsg'=>$totMsg)));
               break;
            
            case "disconnect":
               // Removes a user from the $names array.
               $index = array_search($data->username, $this->names);
               if ($index !== false) {
                  unset($this->names[$index]);
                  $this->Log("  " . $data->username . " has left." . PHP_EOL);

                  $this->showConnectedUsers();
               }
               else {
                  $this->Log("{$data->username} not found in WebSocket Server user list.");
               }
               break;
               
            case "notify":
               if (isset($data->to)) {
                  foreach ($this->client as $conn) {
                     if ($conn->resourceId != $from->resourceId) {
                        // Send the message to all connections except to the sender.
                        $conn->send(json_encode(array('to'=>$data->to, 'totalMsg'=>$data->totalMsg)));
                     }
                  }
               }
               else {
                  $this->Log("Notification could not be delivered due to lack of 'to' field.");
               }

            case "list":
               $from->send(json_encode(array('cmd' => 'list', 'to' => $data->to, 'message' => $this->names)));
               break;

            case "help":
               echo PHP_EOL;
               echo "{$this->inverse}Actions available in this WebSocket Server.{$this->reset}" . PHP_EOL;
               echo "COMMAND     PARAMETERS" . PHP_EOL;
               echo "----------  --------------------------------" . PHP_EOL;
               echo "{$this->yellow}connect     {$this->cyan}<string>{$this->white} username, {$this->cyan}<string>{$this->white} tab{$this->gray}" . PHP_EOL;
               echo "  Javascript example: { action: 'connect', username: <user-name>, tab: [window.location.href] }" . PHP_EOL;
               echo "  The 'tab' parameter is used to keep track of the opened tabs in the browser." . PHP_EOL;
               echo PHP_EOL;
               echo "{$this->yellow}disconnect {$this->cyan} <string>{$this->white} username{$this->gray}" . PHP_EOL; 
               echo "  Javascript example: {action: 'disconnect', username: <user-name>}" . PHP_EOL;
               echo PHP_EOL;
               echo "{$this->yellow}notify      {$this->cyan}<string>{$this->white} to {$this->cyan}<string>{$this->white} message{$this->gray}" . PHP_EOL;
               echo "  Javascript example: {action: 'notify', to: <user-name>, message: <your-message>}" . PHP_EOL;
               echo PHP_EOL;
               echo "{$this->yellow}list{$this->gray}     " . PHP_EOL;
               echo "  Displays the list of connected users." . PHP_EOL;
               echo "  Javascript example: {action: 'list'}" . PHP_EOL;
               echo PHP_EOL;
               echo "{$this->yellow}help{$this->gray}     " . PHP_EOL;
               echo "  Displays the this help text." . PHP_EOL;
               echo "  Javascript example: {action: 'help'}" . PHP_EOL;
               echo PHP_EOL;
               break;

            default:
               $this->Log("Unrecognized command or action: " . $data->action);
         }
      }
   }
   
   
   public function onClose(ConnectionInterface $conn) {
      // Remove connection.
      unset($this->clientId[$conn->resourceId]);
      unset($this->client[$conn]);
      echo " \n";
      $this->Log("  Connection ({$conn->resourceId}) ended.\n");
      echo " \n";
   }
   
   
   public function onError(ConnectionInterface $conn, \Exception $e) {
      $this->Log("WebSocket Server error: {$e->getMessage()}\n");
      $conn->close();
   }


   private function showConnectedUsers() {
      $len = count($this->names);
      if ($len > 0) {
         //echo PHP_EOL;
         echo "  " . $len . " user" . (($len > 1) ? "s " : " ") . "connected:" . PHP_EOL;
         $i = 1;
         foreach ($this->names as $index => $name) {
            echo "   {$this->cyan}{$i}{$this->white} : {$this->bold}{$name}{$this->reset}" . PHP_EOL;
            $i++;
         }
      }
   }


   private function Log(string $msg) {
      echo $msg . PHP_EOL;
      //echo PHP_EOL;

      $msg = str_replace([PHP_EOL, $this->inverse,$this->bold,$this->reset,$this->red,$this->green,$this->blue,$this->yellow,$this->cyan,$this->white,$this->gray], "", $msg);
      Log::channel('wss')->info(trim($msg));
   }

}