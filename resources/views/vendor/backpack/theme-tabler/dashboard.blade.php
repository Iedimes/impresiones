@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid">
        <h2>
            {{ trans('backpack::base.dashboard') }}
            <small>{{ trans('backpack::base.first_page_you_see') }}</small>
        </h2>
    </section>
@endsection

@section('content')
    <div class="row">
        <!-- Users Card -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <i class="la la-users" style="font-size: 48px; color: #3c8dbc;"></i>
                    <h3 class="card-title mt-2">Usuarios</h3>
                    <p class="card-text text-muted">Gestionar usuarios del sistema</p>
                    <a href="{{ backpack_url('user') }}" class="btn btn-primary">Ver Usuarios</a>
                </div>
            </div>
        </div>

        <!-- Roles Card -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <i class="la la-id-badge" style="font-size: 48px; color: #00a65a;"></i>
                    <h3 class="card-title mt-2">Roles</h3>
                    <p class="card-text text-muted">Gestionar roles de usuarios</p>
                    <a href="{{ backpack_url('role') }}" class="btn btn-success">Ver Roles</a>
                </div>
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <i class="la la-key" style="font-size: 48px; color: #f39c12;"></i>
                    <h3 class="card-title mt-2">Permisos</h3>
                    <p class="card-text text-muted">Gestionar permisos del sistema</p>
                    <a href="{{ backpack_url('permission') }}" class="btn btn-warning">Ver Permisos</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('backpack::base.login_status') }}</h3>
                </div>
                <div class="card-body">
                    {{ trans('backpack::base.logged_in') }}
                </div>
            </div>
        </div>
    </div>
@endsection
