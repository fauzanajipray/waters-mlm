{{-- This file is used to store sidebar items, inside the Backpack admin panel --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<!-- Product -->
@if (backpack_user()->hasAnyPermission(['Read Product', 'Read Branch Product']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-box"></i> Product</a>
  <ul class="nav-dropdown-items">
    @can('Read Product')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('product') }}"><i class="nav-icon la la-box"></i> Products</a></li>
    @endcan
    @can('Read Branch Product')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('branch-product') }}"><i class="nav-icon la la-box"></i> Branch Products</a></li>
    @endcan
  </ul>
</li>
@endif

<!-- Member -->
@if (backpack_user()->hasAnyPermission(['Read Member', 'Read Activation Payment']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-id-card"></i> Members</a>
  <ul class="nav-dropdown-items">
    @can('Read Member')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('member') }}"><i class="nav-icon la la-list"></i> List</a></li>
    @endcan
    @can('Create Member')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('member/create') }}"><i class="nav-icon la la-plus"></i> Add</a></li>
    @endcan
    @can('Read Activation Payment')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('activation-payments') }}"><i class="nav-icon la la-money-bill"></i> Payments</a></li>
    @endcan
  </ul>
</li>
@endif

<!-- Stock -->
@if (backpack_user()->hasAnyPermission(['Read Stock', 'Read Stock Card']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-archive"></i> Stock</a>
  <ul class="nav-dropdown-items">
    @can('Read Stock')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('stock') }}"><i class="nav-icon la la-list"></i> List</a></li>
    @endcan
    @can('Read Stock Card')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('stock-card') }}"><i class="nav-icon la la-sim-card"></i> Stock Card</a> </li>
    @endcan
  </ul>
</li>
@endif

<!-- Transactions -->
@if (backpack_user()->hasAnyPermission(['Read Normal Transaction', 'Read Transaction Display', 'Read Transaction Demokit', 'Read Transaction Bebas Putus', 'Read Transaction Sparepart', 'Read Transaction Stock', 'Read Transaction Payment']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-money-bill"></i> Transactions</a>
  <ul class="nav-dropdown-items">
    {{-- Transaction Normal --}}
    @can('Read Normal Transaction')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction') }}"><i class="nav-icon la la-dollar"></i> <span>Normal</span></a></li>
    @endcan
    {{-- Tranasction Display --}}
    @can('Read Display Transaction')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-display') }}"><i class="nav-icon la la-dollar"></i> <span>Display</span></a></li>
    @endcan
    {{-- Transaction Demokit --}}
    @can('Read Demokit Transaction')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-demokit') }}"><i class="nav-icon la la-dollar"></i> <span>Demokit</span></a></li>
    @endcan
    {{-- Transaction Bebas Putus --}}
    @can('Read Bebas Putus Transaction')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-bebas-putus') }}"><i class="nav-icon la la-dollar"></i> <span>Bebas Putus</span></a></li>
    @endcan
    {{-- Transaction Sparepart --}}
    @can('Read Sparepart Transaction')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-sparepart') }}"><i class="nav-icon la la-dollar"></i> <span>Sparepart</span></a></li>
    @endcan
    {{-- Transaction Stock --}}
    @can('Read Stock Transaction')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction-stock') }}"><i class="nav-icon la la-dollar"></i> <span>Stock</span> </a></li>
    @endcan
    {{-- Transaction Payment --}}
    @can('Read Payment Transaction')
    <li class= "nav-item"><a class="nav-link" href="{{ backpack_url('transaction-payment') }}"><i class="nav-icon la la-money-bill"></i> <span>Payments</span></a></li>
    @endcan
  </ul>
</li>
@endif

@can('Read Payment Method')
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('payment-method') }}"><i class="nav-icon la la-money-bill"></i> Payment Methods</a></li>
@endcan

<!-- Histories -->
@if (backpack_user()->hasAnyPermission(['Read Bonus History', 'Read Level History']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-history"></i> Histories</a>
  <ul class="nav-dropdown-items">
    @can('Read Bonus History')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('bonus-history') }}"><i class="nav-icon la la-hand-holding-usd"></i> Bonus Histories</a></li>
    @endcan
    @can('Read Level History')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('level-up-histories') }}"><i class="nav-icon la la-level-up-alt"></i> Level Up Histories</a></li>
    @endcan
  </ul>
</li>
@endif

@can('Read Branch')
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('branch') }}"><i class="nav-icon la la-city"></i> Branches</a></li>
@endcan
@can('Read Customer')
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('customer') }}"><i class="nav-icon la la-users"></i> Customers</a></li>
@endcan

<!-- Configuration -->
@if (backpack_user()->hasAnyPermission(['Read Config Level Member', 'Read Config Level NSI', 'Read Config']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-cog"></i> Configuration</a>
  <ul class="nav-dropdown-items">
    @can('Read Config Level Member')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('level') }}"><i class="nav-icon la la-chart-line"></i> Levels</a></li>
    @endcan
    @can('Read Config Level NSI')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('level-nsi') }}"><i class="nav-icon la la-chart-line"></i> Level NSI</a></li>
    @endcan
    @can('Read Config')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('configuration') }}"><i class="nav-icon la la-tools"></i> Others</a></li>
    @endcan
  </ul>
</li>
@endif

<!-- Users, Roles, Permissions -->
@if (backpack_user()->hasAnyPermission(['Read User', 'Read Role', 'Read Permission']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>
  <ul class="nav-dropdown-items">
    @can('Read User')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
    @endcan
    @can('Read Role')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
    @endcan
    @can('Read Permission')
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
    @endcan
  </ul>
</li>
@endif

<!--  -->
@if (backpack_user()->hasAnyPermission(['Read User', 'Read Role', 'Read Permission']))
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Area</a>
  <ul class="nav-dropdown-items">
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('area') }}"> <i class="nav-icon la la-stop"></i> Areas</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('area/lsi') }}"> <i class="nav-icon la la-user-lock"></i> LSI</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('area/pm') }}"> <i class="nav-icon la la-user-astronaut"></i> PM</a></li>
  </ul>
@endif