import { useLocalSearchParams, useRouter } from "expo-router";
import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from "react-native";

import { Badge, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { Tabla } from "@/components/tabla";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero } from "@/lib/api";
import { avisar, confirmar } from "@/lib/dialogos";

type Detalle = { producto: string; talla: string; cantidad: number; precio: number; subtotal: number };
type Venta = {
  id: number; fecha: string; total: number; piezas: number; origen: string; forma_pago: string;
  cliente: string; telefono: string | null; empleado: string | null; direccion_entrega: string | null;
  pago: { folio: string; estado: string; estado_texto: string } | null;
  detalles: Detalle[];
};

/** RF012: resumen detallado de la venta. */
export default function VentaResumen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();
  const { datos: v, cargando } = useApi<Venta>(`/ventas/${id}`);

  const handleEliminar = async () => {
    if (!(await confirmar("Eliminar venta", "Las piezas regresarán al inventario. ¿Continuar?", "Eliminar", true))) return;
    try {
      const r = await api(`/ventas/${id}`, "DELETE");
      avisar("Listo", r.message);
      router.back();
    } catch (e) {
      avisar("No se pudo eliminar", (e as Error).message);
    }
  };

  if (cargando && !v) return <ActivityIndicator style={{ flex: 1, backgroundColor: Color.fondo }} color={Color.primario} />;
  if (!v) return <Text style={[base.vacio, { flex: 1, backgroundColor: Color.fondo, paddingTop: 30 }]}>No se encontró la venta.</Text>;

  return (
    <ScrollView contentContainerStyle={base.container}>
      <Encabezado titulo={`Venta #${v.id}`} sub={v.fecha} />

      <View style={base.card}>
        <TituloTarjeta icono="receipt" texto="Datos de la venta" />
        <Dato etiqueta="Cliente" valor={v.cliente + (v.telefono ? ` · ${v.telefono}` : "")} />
        <View style={styles.dato}>
          <Text style={styles.etiqueta}>Origen</Text>
          <Badge texto={v.origen === "EN_LINEA" ? "Compra en línea" : "Mostrador"} color={v.origen === "EN_LINEA" ? "blue" : "green"} />
        </View>
        <Dato etiqueta="Forma de pago" valor={v.forma_pago} />
        {v.empleado ? <Dato etiqueta="Atendió" valor={v.empleado} /> : null}
        {v.direccion_entrega ? <Dato etiqueta="Entrega" valor={v.direccion_entrega} /> : null}
        {v.pago ? <Dato etiqueta="Pago" valor={`${v.pago.folio} · ${v.pago.estado_texto}`} /> : null}
      </View>

      <Tabla
        icono="box-open"
        colorIcono={Color.turquesa}
        titulo="Productos"
        columnas={["Producto", "Cant.", "Subtotal"]}
        filas={v.detalles.map((d) => [`${d.producto} · ${d.talla}`, `${d.cantidad} × ${dinero(d.precio)}`, dinero(d.subtotal)])}
        vacio="Sin productos."
      />

      <View style={styles.total}>
        <Text style={styles.totalTexto}>{v.piezas} pieza(s)</Text>
        <Text style={styles.totalTexto}>Total {dinero(v.total)}</Text>
      </View>

      <Boton texto="Eliminar venta" icono="trash" tipo="danger" onPress={handleEliminar} />
    </ScrollView>
  );
}

function Dato({ etiqueta, valor }: { etiqueta: string; valor: string }) {
  return (
    <View style={styles.dato}>
      <Text style={styles.etiqueta}>{etiqueta}</Text>
      <Text style={styles.valor}>{valor}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  dato: {
    marginBottom: 10,
  },
  etiqueta: {
    fontSize: 11,
    color: Color.textoSuave,
    textTransform: "uppercase",
    letterSpacing: 0.5,
    marginBottom: 2,
  },
  valor: {
    fontSize: 14,
    color: Color.texto,
  },
  total: {
    flexDirection: "row",
    justifyContent: "space-between",
    backgroundColor: "rgba(79,142,247,.15)",
    borderColor: Color.primario,
    borderWidth: 1,
    borderRadius: 12,
    padding: 14,
    marginBottom: 8,
  },
  totalTexto: {
    color: Color.texto,
    fontWeight: "700",
    fontSize: 15,
  },
});
