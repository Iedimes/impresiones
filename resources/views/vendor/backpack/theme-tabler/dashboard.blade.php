@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid">
        <h2>{{ trans('backpack::base.dashboard') }}</h2>
    </section>
@endsection

@section('content')
    <div class="row g-4">
        <!-- Users Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="la la-users" style="font-size: 64px; color: #667eea;"></i>
                    </div>
                    <h4 class="card-title fw-bold text-dark">Usuarios</h4>
                    <p class="card-text text-muted">Gestionar usuarios del sistema</p>
                    <a href="{{ backpack_url('user') }}" class="btn btn-primary mt-2">
                        <i class="la la-arrow-right me-1"></i> Ver Usuarios
                    </a>
                </div>
            </div>
        </div>

        <!-- Roles Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="la la-id-badge" style="font-size: 64px; color: #11998e;"></i>
                    </div>
                    <h4 class="card-title fw-bold text-dark">Roles</h4>
                    <p class="card-text text-muted">Gestionar roles de usuarios</p>
                    <a href="{{ backpack_url('role') }}" class="btn btn-success mt-2">
                        <i class="la la-arrow-right me-1"></i> Ver Roles
                    </a>
                </div>
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="la la-key" style="font-size: 64px; color: #f5576c;"></i>
                    </div>
                    <h4 class="card-title fw-bold text-dark">Permisos</h4>
                    <p class="card-text text-muted">Gestionar permisos del sistema</p>
                    <a href="{{ backpack_url('permission') }}" class="btn btn-danger mt-2">
                        <i class="la la-arrow-right me-1"></i> Ver Permisos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="la la-check-circle text-success" style="font-size: 32px;"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ backpack_auth()->user()->name }}</h5>
                            <p class="text-muted mb-0">{{ trans('backpack::base.logged_in') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
