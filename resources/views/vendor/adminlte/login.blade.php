@extends('adminlte::master')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/iCheck/square/blue.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/auth.css') }}">
    @yield('css')
    <style>
        body {
            background-color: #a1b3d1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .auth-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
            padding: 30px 25px;
        }

        .auth-logos {
            margin-bottom: 25px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            align-items: center;
        }

        .auth-logos img {
            max-width: 400px;
            height: auto;
        }

        .auth-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            font-weight: bold;
        }

        .login-box-msg {
            margin-bottom: 25px;
            font-size: 15px;
            color: #555;
        }

        .form-control {
            height: 45px;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .btn-primary {
            background: #3c8dbc;
            border: none;
            font-size: 16px;
            padding: 10px;
            border-radius: 6px;
            transition: 0.3s ease;
        }

        .btn-primary:hover {
            background: #367fa9;
        }
    </style>
@stop

@section('body')
    <div class="auth-container">
        <div class="auth-logos">

            <img src="{{asset('img/logofull.png')}}" alt="Gobierno Nacional">

        </div>

        <h2 class="auth-title">{!! config('adminlte.logo', '<b>Admin</b>LTE') !!}</h2>

        <p class="login-box-msg">{{ trans('adminlte::adminlte.login_message') }}</p>

        <form action="{{ url(config('adminlte.login_url', 'login')) }}" method="post">
            {!! csrf_field() !!}

            <div class="form-group has-feedback">
                <input id="username" type="username" class="form-control" name="username"
                       value="{{ old('username') }}" required
                       placeholder="{{ trans('adminlte::adminlte.username') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                @if ($errors->has('username'))
                    <span class="help-block">
                        <strong>{{ $errors->first('username') }}</strong>
                    </span>
                @endif
            </div>

            <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
                <input type="password" name="password" class="form-control"
                       placeholder="{{ trans('adminlte::adminlte.password') }}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>

            <div class="row">
                <div class="col-xs-4">
                    <a href="{{ url('/admin/login') }}">
                        <button class="btn btn-block btn-link">Acceso Admin</button>
                    </a>
                </div>
                <div class="col-xs-4"></div>
                <div class="col-xs-4">
                    <button type="submit"
                            class="btn btn-primary btn-block btn-flat">{{ trans('adminlte::adminlte.sign_in') }}</button>
                </div>
            </div>
        </form>
    </div>
@stop
