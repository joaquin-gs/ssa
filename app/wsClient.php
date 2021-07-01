<?php
namespace App;

use Amp\Websocket\Client;

class wsClient {

   public function __construct() {
      //
   }


   // Creates a temporary connection to the WebSocket Server
   // The parameter $to is the user name the server should reply to.
   public function sendMsg($msg) {
      global $x;
      $x = $msg;
      \Amp\Loop::run(
         function() {
            global $x;
            $connection = yield Client\connect('ws://ssa:8090');
            yield $connection->send(json_encode($x));
            yield $connection->close();
            \Amp\Loop::stop();
         }
      );
   }

}
