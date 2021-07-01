@extends('adminlte::page')

@section('title', 'Settings')

@section('content_header')
<h1 class="m-0 text-dark">Settings</h1>
@stop

@section('content')
<div class="row">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <p class="mb-0">System settings configuration</p>
         </div>
      </div>
   </div>
</div>

<div class="row">
   <div class="col">
      <button type="button" id="notifyUser">Notify user</button>
   </div>
   <div class="col">
      <button type="button" id="getHelp">Get help</button>
   </div>
   <div class="col">
      <button type="button" id="userList">Connected users</button>
   </div>
</div>
@stop

@push('js')
<script>
$(document).ready(function($) {
   $.ajaxSetup({ headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });

   $('#notifyUser').on('click', function() {
      jQuery.post('/admin/settings/sendNotification');
   });

   $(document).on('click', '#getHelp', function() {
      window.worker.port.postMessage({ action: 'help' });
   });

   $('#userList').on('click', function() {
      window.worker.port.postMessage({action: 'list'});
   });
});
</script>
@endpush
