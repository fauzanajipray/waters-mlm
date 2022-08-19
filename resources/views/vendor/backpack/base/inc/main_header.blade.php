<header class="{{ config('backpack.base.header_class') }}">
    {{-- Logo --}}
    <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto ml-3" type="button" data-toggle="sidebar-show" aria-label="{{ trans('backpack::base.toggle_navigation')}}">
      <span class="navbar-toggler-icon"></span>
    </button>
    <button class="navbar-toggler sidebar-toggler d-md-down-none ml-2 mr-lg-1" type="button" data-toggle="sidebar-lg-show" aria-label="{{ trans('backpack::base.toggle_navigation')}}">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="{{ url(config('backpack.base.home_link')) }}" title="{{ config('backpack.base.project_name') }}">
      <img style="width:100px;height:auto" class="" src="{{asset('images/bg-waters.png')}}">
    </a>
    @include(backpack_view('inc.menu'))
  </header>
  