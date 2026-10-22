import { Campo, CatalogoCrud } from "@/components/catalogo-crud";

const campos: Campo[] = [
  { key: "nombre", label: "Nombre", placeholder: "Nombre" },
  { key: "ap_paterno", label: "Apellido paterno", placeholder: "Apellido paterno" },
  { key: "telefono", label: "Teléfono", placeholder: "10 dígitos", keyboardType: "phone-pad" },
  { key: "direccion", label: "Dirección", placeholder: "Dirección" },
];

export default function ProveedoresScreen() {
  return (
    <CatalogoCrud
      titulo="Proveedores"
      singular="proveedor"
      icono="truck"
      ruta="/proveedores"
      claveLista="proveedores"
      campos={campos}
      renglon={(p) => ({ titulo: `${p.nombre} ${p.ap_paterno}`, detalle: `${p.telefono} · ${p.direccion}` })}
    />
  );
}
