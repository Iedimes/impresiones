<li class="dropdown user user-menu">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
  <img src="{{ Gravatar::get($user->email) }}" class="user-image" alt="User Image">
  <span class="hidden-xs">{{$user->name}}</span>
</a>
<ul class="dropdown-menu">
  <!-- User image -->
  <li class="user-header">
    <img src="{{ Gravatar::get($user->email) }}" class="img-circle" alt="User Image">

    <p>
        {{$user->name}}
      <small></small>
    </p>
  </li>
  <!-- Menu Body -->
  
  <!-- Menu Footer-->
  <li class="user-footer">
    <div class="pull-left">
      <a href="#" class="btn btn-default btn-flat">Perfil</a>
    </div>
    <div class="pull-right">
      
      <a href="#" class="btn btn-default btn-flat"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
    >
        <i class="fa fa-fw fa-power-off"></i> Salir
    </a>
    <form id="logout-form" action="{{ url(config('adminlte.logout_url', 'auth/logout')) }}" method="POST" style="display: none;">
        @if(config('adminlte.logout_method'))
            {{ method_field(config('adminlte.logout_method')) }}
        @endif
        {{ csrf_field() }}
    </form>
    </div>
  </li>
</ul>
</li>