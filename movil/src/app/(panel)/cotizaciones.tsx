import { useRouter } from "expo-router";
import { useState } from "react";
import { ActivityIndicator, FlatList, RefreshControl, Text, TouchableOpacity, View } from "react-native";

import { Chips } from "@/components/chips";
import { Badge, Encabezado } from "@/components/panel";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { dinero } from "@/lib/api";

type Estado = "PENDIENTE" | "ATENDIDA";
type Cotizacion = {
  id: number; total: number; estado: Estado; cliente: string; fecha: string | null;
  atendio: string | null; atendida_en: string | null; renglones: number;
};

const ESTADOS: Record<Estado, string> = { PENDIENTE: "Pendientes", ATENDIDA: "Atendidas" };

/** RF009: cotizaciones que los clientes generan desde su carrito. */
export default function CotizacionesScreen() {
  const router = useRouter();
  const [estado, setEstado] = useState<Estado>("PENDIENTE");
  const { datos, cargando, recargar } = useApi<{ cotizaciones: Cotizacion[] }>(`/cotizaciones?estado=${estado}`);

  return (
    <FlatList
      style={{ backgroundColor: Color.fondo }}
      contentContainerStyle={base.container}
      data={datos?.cotizaciones ?? []}
      keyExtractor={(c) => String(c.id)}
      refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}
      ListHeaderComponent={
        <>
          <Encabezado titulo="Cotizaciones" sub="Cotizaciones que los clientes generan desde su carrito" />
          <Chips opciones={ESTADOS} valor={estado} onChange={setEstado} />
          {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
        </>
      }
      ListEmptyComponent={datos ? <Text style={base.vacio}>No hay cotizaciones {ESTADOS[estado].toLowerCase()}.</Text> : null}
      renderItem={({ item: c }) => (
        <TouchableOpacity style={base.card} onPress={() => router.push(`/cotizacion/${c.id}`)}>
          <View style={[base.row, { justifyContent: "space-between" }]}>
            <Text style={base.cardTitle}>
              <Text style={{ color: Color.textoSuave }}>#{c.id} </Text>
              {c.cliente}
            </Text>
            <Text style={{ fontWeight: "700", color: Color.exito }}>{dinero(c.total)}</Text>
          </View>
          <Text style={base.cardText}>
            {c.fecha} · {c.renglones} producto(s)
          </Text>
          <View style={{ marginTop: 6 }}>
            <Badge
              texto={c.estado === "ATENDIDA" ? `Atendida por ${c.atendio} · ${c.atendida_en}` : "Pendiente"}
              color={c.estado === "ATENDIDA" ? "green" : "orange"}
            />
          </View>
        </TouchableOpacity>
      )}
    />
  );
}
