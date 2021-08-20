<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Http\Controllers\WebSocketController;
use React\Socket\SecureServer;
use React\Socket\Server;
use React\EventLoop\Factory;

class WebSocketServer extends Command
{
   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'websocket:start';

   /**
    * The console command description.
    *
    * @var string
    */
   protected $description = 'The websocket server';


   /**
    * Create a new command instance.
    *
    * @return void
    */
   public function __construct()
   {
      parent::__construct();
   }


   /**
    * Execute the console command.
    *
    * @return int
    */
   public function handle()
   {
      // $loop   = Factory::create();
      // $webSock = new SecureServer(
      //    new Server('0.0.0.0:8090', $loop),
      //    $loop,
      //    array(
      //       'local_cert'        => 'certificate.crt', // path to your cert
      //       'local_pk'          => 'private.key', // path to your server private key
      //       'allow_self_signed' => TRUE, // Allow self signed certs (should be false in production)
      //       'verify_peer' => FALSE
      //    )
      // );

      // // Ratchet magic
      // $webServer = new IoServer(
      //    new HttpServer(
      //       new WsServer(
      //          new WebSocketController()
      //       )
      //    ),
      //    $webSock
      // );

      // $loop->run();

      $server = IoServer::factory(new HttpServer(new WsServer(new WebSocketController())), 8090);
      $server->run();
   }
}
