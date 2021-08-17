@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">You are logged in!</p>
                </div>
            </div>
        </div>
        <div class="col-6">
           <button type="button" id="sendMsg">Create a notification</button>
        </div>
    </div>
@stop

@push('js')
<script>
   $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
   });

   $('#sendMsg').on('click', function() {
      $.post('/sendMessage', { name: window.currentUser, message: 'Lorem ipsum dolor sit amet...'});
   });

</script>
@endpush
