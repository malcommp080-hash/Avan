@php
  $sinLeer = \App\Models\Notificacion::para((int) session('admin_id'))->where('Leida', false)->count();
@endphp
        <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-sm btn-outline" title="Notificaciones" style="position:relative;margin-right:6px">
          <i class="fas fa-bell"></i>
          @if($sinLeer)<span style="position:absolute;top:-6px;right:-6px;background:#e74c3c;color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700">{{ $sinLeer > 99 ? '99+' : $sinLeer }}</span>@endif
        </a>
        <form action="{{ route('admin.logout') }}" method="post" style="display:inline">@csrf<button type="submit" class="btn btn-sm btn-outline" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i> Salir</button></form>
