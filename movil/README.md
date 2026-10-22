# DISTRIBUIDORA DAVID — Panel de administración (app móvil)

El panel de administración en el celular, hecho con Expo (misma estructura que `Formularios`) y con la misma
paleta y secciones que el panel web. Consume la API JSON de este mismo proyecto Laravel
(`routes/api.php`, prefijo `/api/admin`).

## Pantallas

- `src/app/Login.tsx`: acceso con teléfono y contraseña, con el mismo bloqueo por intentos que la web.
- `src/app/(panel)/`: todo lo que está en el menú lateral, que también es el del panel web:
  - **Principal:** Dashboard, Notificaciones
  - **Operatividad:** Ventas, Pedidos de clientes, Pedidos a proveedores, Proveedores, Validación de pago, Cotizaciones
  - **Catálogos:** Clientes, Prendas, Productos, Inventario, Usuarios (sólo admin)
  - **Sistema:** Respaldar BD, Restaurar BD (sólo admin), Reportes de ventas, Reporte PDF, Bitácora (sólo admin)
- `src/app/nueva-venta.tsx`, `venta/[id].tsx`, `cotizacion/[id].tsx`: pantallas que se abren encima del panel.

Los colores están en `src/constants/estilos.ts`, copiados de `public/css/style.css`.

## Cómo correrla

1. En la raíz del proyecto (`C:\laragon\www\AvanLara`), con Laragon encendido (MySQL):
   ```bash
   php artisan migrate
   php artisan serve --host=0.0.0.0 --port=8000
   ```
2. En otra terminal, dentro de `movil/`:
   ```bash
   npm install
   npx expo start
   ```
   Escanea el QR con Expo Go (con el celular en la misma red Wi-Fi), o presiona `w` para abrirla en el navegador.

La app toma sola la IP de la PC donde corre Expo. Si no conecta, revisa `IP_RESPALDO` en
`src/constants/api.ts` y permite el puerto 8000 en el Firewall de Windows.
