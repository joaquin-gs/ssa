@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@if($layoutHelper->isLayoutTopnavEnabled())
   @php( $def_container_class = 'container' )
@else
   @php( $def_container_class = 'container-fluid' )
@endif

@section('adminlte_css')
   @stack('css')
   @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
<div class="wrapper">

   {{-- Top Navbar --}}
   @if($layoutHelper->isLayoutTopnavEnabled())
      @include('adminlte::partials.navbar.navbar-layout-topnav')
   @else
      @include('adminlte::partials.navbar.navbar')
   @endif

   {{-- Left Main Sidebar --}}
   @if(!$layoutHelper->isLayoutTopnavEnabled())
      @include('adminlte::partials.sidebar.left-sidebar')
   @endif

   {{-- Content Wrapper --}}
   <div class="content-wrapper {{ config('adminlte.classes_content_wrapper') ?? '' }}">

      {{-- Content Header --}}
      <div class="content-header">
         <div class="{{ config('adminlte.classes_content_header') ?: $def_container_class }}">
            @yield('content_header')
         </div>
      </div>

      {{-- Main Content --}}
      <div class="content">
         <div class="{{ config('adminlte.classes_content') ?: $def_container_class }}">
            @yield('content')
         </div>
      </div>

   </div>

   {{-- Footer --}}
   @hasSection('footer')
      @include('adminlte::partials.footer.footer')
   @endif

   {{-- Right Control Sidebar --}}
   @if(config('adminlte.right_sidebar'))
      @include('adminlte::partials.sidebar.right-sidebar')
   @endif

</div>
@stop

@section('adminlte_js')
   <script>
      $(document).ready(function($) {
         window.currentUser = "{{ Auth::user()->name }}";
         let tabID = null;

         // The worker variable is declared as a property
         // of the window object in order to make it global.
         var worker = new SharedWorker("{{ asset('/js/worker.js') }}");
         worker.port.start();

         worker.port.onmessage = function(message) {
            // Update notifications link on top navigation bar.
            if (message.data.totalMsg) {
               $('#notifications a.nav-link span').text(message.data.totalMsg);
            }
            if (message.data.type == 'CONNECTION') {
               tabID = message.data.tabID;
               worker.port.postMessage({ action: 'connect', username: window.currentUser });
            }
         };

         worker.onerror = function(error) {
            console.log('Worker error: ' + error.message + '\n');
         };

         window.addEventListener('beforeunload', function(ev) {
            worker.port.postMessage({ action: 'close', connectionID: tabID, username: window.currentUser });
            console.log('Closing tab: ' + tabID);
            worker.port.close();
         });

      });
   </script>
   @stack('js')
   @yield('js')
@stop