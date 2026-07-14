{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="la la-users nav-icon"></i> Usuarios y Permisos</a>
    <ul class="nav-dropdown-items">
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="la la-user nav-icon"></i> Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="la la-id-badge nav-icon"></i> Roles</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="la la-key nav-icon"></i> Permisos</a></li>
    </ul>
</li>
