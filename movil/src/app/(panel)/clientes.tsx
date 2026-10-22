import { Campo, CatalogoCrud } from "@/components/catalogo-crud";

// Para agregar un campo nuevo, solo agrega una línea aquí (y en la API)
const campos: Campo[] = [
  { key: "nombre", label: "Nombre", placeholder: "Nombre" },
  { key: "ap_paterno", label: "Apellido paterno", placeholder: "Apellido paterno" },
  { key: "telefono", label: "Teléfono", placeholder: "10 dígitos", keyboardType: "phone-pad" },
  { key: "direccion", label: "Dirección", placeholder: "Calle, número, colonia" },
  { key: "cp", label: "Código postal", placeholder: "5 dígitos", keyboardType: "numeric" },
  { key: "contrasena", label: "Contraseña de la tienda", placeholder: "Mínimo 4 caracteres", secure: true, soloAlCrear: true },
];

export default function ClientesScreen() {
  return (
    <CatalogoCrud
      titulo="Clientes"
      singular="cliente"
      icono="users"
      ruta="/clientes"
      claveLista="clientes"
      campos={campos}
      renglon={(c) => ({ titulo: `${c.nombre} ${c.ap_paterno}`, detalle: `${c.telefono} · ${c.direccion} · CP ${c.cp}` })}
    />
  );
}
