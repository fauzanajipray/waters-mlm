{{-- This file is used to store sidebar items, inside the Backpack admin panel --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('product') }}"><i class="nav-icon la la-box"></i> Products</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('member') }}"><i class="nav-icon la la-id-card"></i> Members</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('level') }}"><i class="nav-icon la la-chart-line"></i> Levels</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('transaction') }}"><i class="nav-icon la la-dollar"></i> Transactions</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('bonus-history') }}"><i class="nav-icon la la-hand-holding-usd"></i> Bonus Histories</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('level-up-histories') }}"><i class="nav-icon la la-level-up-alt"></i> Level Up Histories</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('activation-payments') }}"><i class="nav-icon la la-money-bill"></i> Payments</a></li>
<!-- Users, Roles, Permissions -->
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>
  <ul class="nav-dropdown-items">
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
  </ul>
</li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('customer') }}"><i class="nav-icon la la-users"></i> Customers</a></li>

<li class="nav-item"><a class="nav-link" href="{{ backpack_url('configuration') }}"><i class="nav-icon la la-question"></i> Configurations</a></li>