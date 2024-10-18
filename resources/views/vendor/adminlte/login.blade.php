@extends('adminlte::master')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/iCheck/square/blue.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/auth.css') }}">
    @yield('css')
    <style>
            .border{
                border-color: black;
            }
               .imagencentro{
                   
                margin-left: auto;
                margin-right: auto;
                display: block;
                max-width:100%;
                max-height:100%;
                margin-top: 50px;
               }    
               .center{
                   text-align: center;
               }
            
               .total{
                font-weight: bold;
                
            }
            
            .titulo{
                margin-top: 20px;
                text-align: center;
                margin-bottom: -60px;
            }

            body {
                
                background-color: #a1b3d1;
            }
            
            </style>
@stop



@section('body')
    <div class="row">
        <div class="col-md-4">
                <img src="{{asset('img/CASTELLANO-Y-GURANI-min-de-la-vivienda.png')}}" class="imagencentro" width="230" height="70">
        </div>
        <div class="col-md-4">
                <img src="{{asset('img/gobierno-nacional.png')}}" class="imagencentro"  width="250" height="60">
        </div>
        <div class="col-md-4">
                <img src="{{asset('img/slogan.png')}}" class="imagencentro" width="220" height="70">
        </div>
    </div>
    <div class="row titulo">
        <h2><strong> {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}</strong></h2>
    </div>
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg">{{ trans('adminlte::adminlte.login_message') }}</p>
            <form action="{{ url(config('adminlte.login_url', 'login')) }}" method="post">
                {!! csrf_field() !!}

                <div class="form-group has-feedback">
                    
                        <input id="username" type="username" class="form-control" name="username" value="{{ old('username') }}" required 
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
                    <div class="col-xs-4">

                    </div >
                    <!-- /.col -->
                    <div class="col-xs-4">
                        <button type="submit"
                                class="btn btn-primary btn-block btn-flat">{{ trans('adminlte::adminlte.sign_in') }}</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->
@stop

