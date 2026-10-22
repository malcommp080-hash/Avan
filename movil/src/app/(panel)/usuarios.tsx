import { Campo, CatalogoCrud } from "@/components/catalogo-crud";

const ROLES = { admin: "Administrador", vendedor: "Vendedor", almacen: "Almacén", contador: "Contador" };

// Sólo los administradores ven esta pantalla (la API también lo valida)
const campos: Campo[] = [
  { key: "nombre", label: "Nombre", placeholder: "Nombre" },
  { key: "ap_paterno", label: "Apellido paterno", placeholder: "Apellido paterno" },
  { key: "telefono", label: "Teléfono (usuario para entrar)", placeholder: "10 dígitos", keyboardType: "phone-pad" },
  { key: "direccion", label: "Dirección", placeholder: "Dirección" },
  { key: "rol", label: "Rol", placeholder: "", opciones: ROLES },
  { key: "contrasena", label: "Contraseña", placeholder: "Mínimo 6 caracteres", secure: true, soloAlCrear: true },
];

export default function UsuariosScreen() {
  return (
    <CatalogoCrud
      titulo="Usuarios"
      singular="usuario"
      icono="user-cog"
      ruta="/usuarios"
      claveLista="usuarios"
      campos={campos}
      renglon={(u) => ({
        titulo: `${u.nombre} ${u.ap_paterno}`,
        detalle: `${ROLES[u.rol as keyof typeof ROLES] ?? u.rol} · ${u.telefono}`,
      })}
    />
  );
}
