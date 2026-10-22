import { Campo, CatalogoCrud } from "@/components/catalogo-crud";

const campos: Campo[] = [
  { key: "prendas", label: "Prenda", placeholder: "Máx. 10 caracteres" },
  { key: "num_piezas", label: "Número de piezas", placeholder: "0", keyboardType: "numeric" },
];

export default function PrendasScreen() {
  return (
    <CatalogoCrud
      titulo="Prendas"
      singular="prenda"
      icono="tshirt"
      ruta="/prendas"
      claveLista="prendas"
      campos={campos}
      renglon={(p) => ({ titulo: p.prendas, detalle: `${p.num_piezas} pieza(s)` })}
    />
  );
}
