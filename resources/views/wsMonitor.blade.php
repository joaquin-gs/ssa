@extends('adminlte::page')

@section('title', 'WebSocket Server Monitor')

@section('content_header')
<h1 class="m-0 text-dark text-center">WebSocket Server Monitor</h1>
@stop

@section('content')
<div class="row">
   <div class="col-8 offset-2">
      <div class="card text-center">
         <div class="card-body">
            <div id="tabs">
               <ul>
                  <li>Activity</li>
                  <li>Server help</li>
                  <li>Connected users</li>
               </ul>

               <div>
                  <div id="grid"></div>
               </div>
               
               <div>
                  <p>Actions available in this WebSocket Server</p>
                  <p>COMMAND     PARAMETERS</p>
                  <p>----------  --------------------------------</p>
                  <p><strong>connect</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;string&gt;username, &lt;string&gt; tab</p>
                  <p>  Javascript example: { action: 'connect', username: &lt;user-name&gt;, tab: [window.location.href] }</p>
                  <p>  The 'tab' parameter is used to keep track of the opened tabs in the browser.</p>
                  <br/>
                  <p><strong>disconnect</strong>&nbsp;&nbsp;&lt;string&gt; username</p>
                  <p>  Javascript example: {action: 'disconnect', username: <user-name>}</p>
                  <br/>
                  <p><strong>notify</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;string&gt; to &lt;string&gt; message</p>
                  <p>  Javascript example: {action: 'notify', to: &lt;user-name&gt;, message: &lt;your-message&gt;}</p>
                  <br/>
                  <p><strong>list</strong></p>
                  <p>  Displays the list of connected users.</p>
                  <p>  Javascript example: {action: 'list'}</p>
                  <br/>
                  <p><strong>help</strong></p>
                  <p>  Displays the this help text.</p>
                  <p>  Javascript example: {action: 'help'}</p>
               </div>

               <div>
               </div>

               <!--div class="row justify-content-center">
                  <div class="col-sm-3">
                     <button type="button" class="btn btn-outline-primary" id="notifyUser">Notify user</button>
                  </div>
                  <div class="col-sm-3">
                     <button type="button" class="btn btn-outline-primary" id="getHelp">Get help</button>
                  </div>
                  <div class="col-sm-3">
                     <button type="button" class="btn btn-outline-primary" id="userList">Connected users</button>
                  </div>
               </div-->

            </div>
         </div>
      </div>
   </div>
</div>
@stop

@push('js')
   <script type="text/javascript" src="{{ asset('/js/jqx-all.js') }}"></script>
   <script>
   $(document).ready(function($) {
      $.ajaxSetup({ headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });
      $('#tabs').jqxTabs({ width: '99%', height: 450, position: 'top'}); 

      $('#notifyUser').on('click', function() {
         jQuery.post('/admin/settings/sendNotification');
      });

      $(document).on('click', '#getHelp', function() {
         window.worker.port.postMessage({ action: 'help' });
      });

      $('#userList').on('click', function() {
         window.worker.port.postMessage({action: 'list'});
      });

      var arr = [];

      $.post('wsMonitor/getLog', function(data, status, xhr) {
         console.log(data);
         // Add field 'action' to received data.
         for (let index = 0; index < data.length; index++) {
            if (data[index] !== '') {
               elem = {'action':data[index] };
               arr.push(elem);
            }
         }
         // Prepare data source
         var source = {
            datatype: 'json',
            localdata: arr,
            datafields: [
               { name: 'action' }
            ]
         };
         var dataAdapter = new $.jqx.dataAdapter(source);
         // Assign new source to the jqxGrid.
         $('#grid').jqxGrid({ source: dataAdapter });
      }, 'json')

      $('#grid').jqxGrid({
         theme: 'energyblue',
         height: 395,
         width: '100%',
         altrows: true,
         columns: [
            { text: 'Action', datafield: 'action' },
         ],
      });

   });
   </script>
@endpush

@push('css')
   <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('css/jqx.base.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('css/jqx.energyblue.css') }}">
@endpush
