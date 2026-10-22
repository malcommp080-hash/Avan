import { useLocalSearchParams, useRouter } from "expo-router";
import { ActivityIndicator, Linking, ScrollView, Text, View } from "react-native";

import { Badge, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { Tabla } from "@/components/tabla";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero } from "@/lib/api";
import { avisar } from "@/lib/dialogos";

type Cotizacion = {
  id: number; total: number; estado: "PENDIENTE" | "ATENDIDA"; cliente: string; telefono: string | null;
  fecha: string | null; atendio: string | null; atendida_en: string | null;
  detalles: { producto: string; talla: string; cantidad: number; precio: number; subtotal: number }[];
};

export default function CotizacionDetalle() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();
  const { datos: c, cargando } = useApi<Cotizacion>(`/cotizaciones/${id}`);

  const atender = async () => {
    try {
      const r = await api(`/cotizaciones/${id}/atender`, "PUT");
      avisar("Listo", r.message);
      router.back();
    } catch (e) {
      avisar("No se pudo actualizar", (e as Error).message);
    }
  };

  if (cargando && !c) return <ActivityIndicator style={{ flex: 1, backgroundColor: Color.fondo }} color={Color.primario} />;
  if (!c) return <Text style={[base.vacio, { flex: 1, backgroundColor: Color.fondo, paddingTop: 30 }]}>No se encontró la cotización.</Text>;

  return (
    <ScrollView contentContainerStyle={base.container}>
      <Encabezado titulo={`Cotización #${c.id}`} sub={`Solicitada el ${c.fecha}`} />

      <View style={base.card}>
        <TituloTarjeta icono="user" texto={c.cliente} />
        <Badge
          texto={c.estado === "ATENDIDA" ? `Atendida por ${c.atendio} el ${c.atendida_en}` : "Pendiente de atender"}
          color={c.estado === "ATENDIDA" ? "green" : "orange"}
        />
        {c.telefono ? (
          <Boton texto={`Llamar a ${c.telefono}`} icono="phone" tipo="outline" onPress={() => Linking.openURL(`tel:${c.telefono}`)} style={{ marginTop: 12 }} />
        ) : null}
      </View>

      <Tabla
        icono="file-invoice-dollar"
        colorIcono={Color.turquesa}
        titulo="Productos"
        columnas={["Producto", "Cant.", "Subtotal"]}
        filas={c.detalles.map((d) => [`${d.producto} · ${d.talla}`, `${d.cantidad} × ${dinero(d.precio)}`, dinero(d.subtotal)])}
        vacio="Sin productos."
      />

      <View style={[base.card, { backgroundColor: "rgba(79,142,247,.15)", borderColor: Color.primario }]}>
        <Text style={{ color: Color.texto, fontWeight: "700", fontSize: 16 }}>Total {dinero(c.total)}</Text>
      </View>

      {c.estado === "PENDIENTE" ? <Boton texto="Marcar como atendida" icono="check" tipo="success" onPress={atender} /> : null}
    </ScrollView>
  );
}
