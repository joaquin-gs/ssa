@extends('adminlte::page')

@section('title', 'WebSocket Server Monitor')

@section('content_header')
<h1 class="m-0 text-dark text-center">&nbsp;</h1>
@stop

@section('content')
<div class="row">
   <div class="col-8 offset-2">
      
      <x-card title="Websocket Server monitor" theme="info" theme-mode="outline" icon="fas fa-desktop" removable>
         <div class="card-body">
            <div id="tabs">
               <ul>
                  <li>Activity</li>
                  <li>Server commands</li>
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

               <div class="container">
                  <div class="row">
                     <div class="col-6">
                        <div id="list">
                        </div>
                     </div>
                     <div class="col-6">
                        <label>Send a message:<input type="text" id="message" class="form-control" value=""/></label>
                        <button type="button" id="btnSend" class="btn btn-outline-primary">Send</button>
                     </div>
                  </div>
               </div>

            </div>
         </div>
      </x-card>
   </div>
</div>
@stop

@push('js')
   <script type="text/javascript" src="{{ asset('/js/jqx-all.js') }}"></script>
   <script>
   $(document).ready(function($) {
      $.ajaxSetup({ headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });

      /*
      // Markup text of a toast, just for reference:
      <div class="toast toast-error" aria-live="assertive" style="">
         <div class="toast-progress" style="width: 26.62%;"></div>
         <button type="button" class="toast-close-button" role="button">×</button>
         <div class="toast-title">Messages</div>
         <div class="toast-message">Messages to yourself are not allowed.</div>
      </div>
      */

      toastr.options = {
         "closeButton": true,
         "newestOnTop": true,
         "progressBar": true,
         "preventDuplicates": true,
         "showDuration": "200",
         "hideDuration": "1000",
         "timeOut": "6000",
         "extendedTimeOut": "1000",
         "showEasing": "swing",
         "hideEasing": "linear",
         "showMethod": "fadeIn",
         "hideMethod": "fadeOut"
      }
      var selectedUser = '';

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
         // Add field 'action' to received data.
         for (let index = 0; index < data.length; index++) {
            if (data[index] !== '') {
               elem = {'action':data[index] };
               arr.push(elem);
            }
         }
         // Prepare data source for the grid.
         var source = {
            datatype: 'json',
            localdata: arr,
            datafields: [
               { name: 'action' }
            ]
         };
         var dataAdapter = new $.jqx.dataAdapter(source);
         // Assign new source to the grid.
         $('#grid').jqxGrid({ source: dataAdapter });
      }, 'json')

      $('#grid').jqxGrid({
         theme: 'energyblue',
         height: 415,
         width: '99.8%',
         altrows: true,
         columns: [
            {
               text: '#', sortable: false, filterable: false, editable: false,
               groupable: false, draggable: false, resizable: false,
               datafield: '', columntype: 'number', width: 50,
               cellsrenderer: function (row, column, value) {
                  return "<div style='margin:4px;'>" + (value + 1) + "</div>";
               }
            },
            { text: 'Action', datafield: 'action' },
         ],
      });
      
      
      $('#list').jqxListBox({ 
         width: 250, 
         height: 300
      });
      $('#list').on('select', function (event) {
         var args = event.args;
         if (args) {
            selectedUser = args.item.label;
         }
      });


      $('#tabs').on('tabclick', function (event) {
         var tab = event.args.item;
         if (tab == 2) {
            window.worker.port.postMessage({ action: 'list', username: window.currentUser });
         }
      });
      
      $('#btnSend').on('click', function() {
         if (selectedUser !== '') {
            if (selectedUser !== window.currentUser) {
               var msg = $('#message').val();
               window.worker.port.postMessage({action: 'notify', username: selectedUser, message: msg});
            }
            else {
               toastr["error"]('Messages to yourself are not allowed.', 'Messages');
            }
         }
         else {
            toastr["info"]('Choose a user from the list.', 'Messages');
         }
      });

      $('.card-tools button.btn.btn-tool').on('click', function() {
         window.location = '/';
      });
   });

   function processMessage(msg) {
      if (msg.data.cmd == 'list') {
         if ($('#list').length) {
            $('#list').jqxListBox({ source: msg.data.message });
         }
      }
   }
   </script>
@endpush

@push('css')
   <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('css/jqx.base.css') }}">
   <link rel="stylesheet" type="text/css" href="{{ asset('css/jqx.energyblue.css') }}">
   <style>
      #tabs .container { padding-top: 15px; }

      .toast { opacity: 1 !important; }

      .btn-tool { border: 1px solid darkgray; }

      #grid .jqx-grid-column-header, 
      .jqx-tabs-title, 
      .card-title { font-weight: bold; }
   </style>
@endpush
