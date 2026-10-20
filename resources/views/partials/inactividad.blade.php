{{-- RF014: cierre automático por inactividad. El servidor es quien realmente expira la sesión
     (middleware); este script sólo avisa un minuto antes y recarga al vencer para que se vea. --}}
<script>
(function () {
  var limite = {{ (int) config('dav.inactividad_minutos') }} * 60 * 1000;
  var aviso, fin, banner;
  function reiniciar() {
    clearTimeout(aviso); clearTimeout(fin);
    if (banner) { banner.remove(); banner = null; }
    aviso = setTimeout(function () {
      banner = document.createElement('div');
      banner.textContent = 'Tu sesión se cerrará en 1 minuto por inactividad. Mueve el mouse o presiona una tecla para continuar.';
      banner.style.cssText = 'position:fixed;bottom:16px;left:50%;transform:translateX(-50%);z-index:9999;background:#f39c12;color:#fff;padding:10px 16px;border-radius:8px;font:500 13px sans-serif;box-shadow:0 4px 12px rgba(0,0,0,.2);max-width:calc(100% - 32px)';
      document.body.appendChild(banner);
    }, Math.max(limite - 60000, 0));
    fin = setTimeout(function () { location.reload(); }, limite + 2000);
  }
  // Sólo la actividad real en la página reinicia el reloj del navegador; la del servidor
  // se renueva con cada petición, así que al recargar tras moverse la sesión sigue viva.
  var ultimoPing = Date.now();
  ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function (ev) {
    document.addEventListener(ev, function () {
      reiniciar();
      // Avisa al servidor como mucho cada 2 minutos para que tampoco expire allá
      if (Date.now() - ultimoPing > 120000) { ultimoPing = Date.now(); fetch(location.href, { method: 'HEAD', credentials: 'same-origin' }); }
    }, { passive: true });
  });
  reiniciar();
})();
</script>
