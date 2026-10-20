@php
  // [ruta, patrón para marcar activo, icono, texto, solo admin]
  $secciones = [
    'Principal' => [
      ['admin.dashboard', 'admin.dashboard', 'fa-tachometer-alt', 'Dashboard', false],
      ['admin.notificaciones.index', 'admin.notificaciones.*', 'fa-bell', 'Notificaciones', false],
    ],
    'Operatividad' => [
      ['admin.ventas.index', 'admin.ventas.*', 'fa-dollar-sign', 'Ventas', false],
      ['admin.pedidos-clientes.index', 'admin.pedidos-clientes.*', 'fa-box', 'Pedidos de clientes', false],
      ['admin.pedidos-proveedores.index', 'admin.pedidos-proveedores.*', 'fa-truck-loading', 'Pedidos a proveedores', false],
      ['admin.proveedores.index', 'admin.proveedores.*', 'fa-truck', 'Proveedores', false],
      ['admin.validacion-pago.index', 'admin.validacion-pago.*', 'fa-money-check-dollar', 'Validación de pago', false],
      ['admin.cotizaciones.index', 'admin.cotizaciones.*', 'fa-file-invoice-dollar', 'Cotizaciones', false],
    ],
    'Catálogos' => [
      ['admin.clientes.index', 'admin.clientes.*', 'fa-users', 'Clientes', false],
      ['admin.prendas.index', 'admin.prendas.*', 'fa-shirt', 'Prendas', false],
      ['admin.productos.index', 'admin.productos.*', 'fa-box-open', 'Productos', false],
      ['admin.inventario.index', 'admin.inventario.*', 'fa-warehouse', 'Inventario', false],
      ['admin.usuarios.index', 'admin.usuarios.*', 'fa-user-gear', 'Usuarios', true],
    ],
    'Sistema' => [
      ['admin.respaldo.index', 'admin.respaldo.*', 'fa-download', 'Respaldar BD', false],
      ['admin.restaurar.index', 'admin.restaurar.*', 'fa-upload', 'Restaurar BD', true],
      ['admin.reporte.index', 'admin.reporte.*', 'fa-file-pdf', 'Reporte PDF', false],
      ['admin.bitacora.index', 'admin.bitacora.*', 'fa-clipboard-list', 'Bitácora de accesos', true],
    ],
  ];
  $esAdmin = session('admin_rol') === 'admin';
@endphp
  <!-- SIDEBAR -->
  <nav id="sb">
    <div class="sb-head">
      <div class="logo-c"><img src="{{ asset('img/logo.jpeg') }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;" onerror="this.style.display='none';this.parentElement.textContent='⚙'"></div>
      <div class="logo-t">SistemaCRUD</div>
    </div>
    <div class="sb-user">
      <div class="av">{{ collect(explode(' ', session('admin_nombre', 'Admin')))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('') }}</div>
      <div>
        <div class="un">{{ session('admin_nombre', 'Administrador') }}</div>
        <div class="ur">{{ ucfirst(session('admin_rol', 'admin')) }}</div>
      </div>
    </div>

    @foreach($secciones as $titulo => $items)
    <div class="sb-lbl">{{ $titulo }}</div>
      @foreach($items as [$ruta, $patron, $icono, $texto, $soloAdmin])
        @continue($soloAdmin && !$esAdmin)
    <a href="{{ route($ruta) }}" class="ni {{ request()->routeIs($patron) ? 'act' : '' }}">
      <i class="fas {{ $icono }}"></i><span>{{ $texto }}</span>
    </a>
      @endforeach
    @endforeach
  </nav>
@include('partials.inactividad')
