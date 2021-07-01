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
         var currentUser = "{{ Auth::user()->name }}";

         // The worker variable is declared as a property
         // of the window object in order to make it global.
         window.worker = new SharedWorker("{{ asset('/js/worker.js') }}");
         window.worker.port.start();
         window.worker.port.postMessage({ action: 'connect', username: currentUser, tab: window.location.href });

         window.worker.port.onmessage = function(message) {
            console.log(message.data);
            $('#notifications a.nav-link span').text(message.data.msg);
         };

         window.addEventListener('beforeunload', function(ev) {
            window.worker.port.postMessage({ action: 'close', username: currentUser, tab: window.location.href });
            window.worker.port.close();
         });

      });
   </script>
   @stack('js')
   @yield('js')
@stop