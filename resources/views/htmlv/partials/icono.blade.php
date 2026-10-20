{{-- Íconos de línea de los formularios de Figma (teléfono, contraseña, persona, casa, ubicación) --}}
@switch($i)
  @case('tel')<svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h3.5l1.8 4.5-2.3 1.4a11 11 0 0 0 7.1 7.1l1.4-2.3L21 15.5V19a2 2 0 0 1-2 2A17 17 0 0 1 3 5a2 2 0 0 1 2-2Z"/></svg>@break
  @case('clave')<svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M3 10h4M5 8v4M3.6 8.6l2.8 2.8M6.4 8.6l-2.8 2.8M10 10h4M12 8v4M10.6 8.6l2.8 2.8M13.4 8.6l-2.8 2.8M17 10h4M19 8v4M17.6 8.6l2.8 2.8M20.4 8.6l-2.8 2.8M3 16h18"/></svg>@break
  @case('persona')<svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="8" r="4"/><path d="M3 21a7 7 0 0 1 11-5.7M15 20l5-5 1.5 1.5-5 5H15z"/></svg>@break
  @case('apellido')<svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.6" aria-hidden="true"><rect x="4" y="5" width="12" height="14"/><path d="M19 4v16"/></svg>@break
  @case('casa')<svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/></svg>@break
  @case('ubicacion')<svg viewBox="0 0 24 24" fill="#111" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>@break
@endswitch
