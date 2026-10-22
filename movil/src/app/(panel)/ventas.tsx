import { FontAwesome5 } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import { useState } from "react";
import { ActivityIndicator, FlatList, RefreshControl, Text, TouchableOpacity, View } from "react-native";

import { Entrada } from "@/components/entrada";
import { Badge, Boton, Encabezado } from "@/components/panel";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { dinero } from "@/lib/api";

type Venta = {
  id: number; fecha: string; total: number; cliente: string; origen: string;
  forma_pago: string; piezas: number; renglones: number;
};

export default function VentasScreen() {
  const router = useRouter();
  const { datos, cargando, recargar } = useApi<{ ventas: Venta[] }>("/ventas");
  const [buscar, setBuscar] = useState("");

  const ventas = (datos?.ventas ?? []).filter(
    (v) => !buscar || v.cliente.toLowerCase().includes(buscar.toLowerCase()) || String(v.id) === buscar.replace("#", "")
  );

  return (
    <FlatList
      style={{ backgroundColor: Color.fondo }}
      contentContainerStyle={base.container}
      data={ventas}
      keyExtractor={(v) => String(v.id)}
      keyboardShouldPersistTaps="handled"
      refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}
      ListHeaderComponent={
        <>
          <Encabezado titulo="Ventas" sub="Ventas en mostrador y compras en línea con pago aprobado" />
          <Boton texto="Registrar venta" icono="plus" tipo="success" onPress={() => router.push("/nueva-venta")} style={{ marginBottom: 14 }} />
          <Entrada icono="search" placeholder="Buscar por cliente o #folio" value={buscar} onChangeText={setBuscar} />
          {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
        </>
      }
      ListEmptyComponent={datos ? <Text style={base.vacio}>No hay ventas registradas.</Text> : null}
      renderItem={({ item: v }) => (
        <TouchableOpacity style={base.card} onPress={() => router.push(`/venta/${v.id}`)}>
          <View style={[base.row, { justifyContent: "space-between" }]}>
            <Text style={base.cardTitle}>
              <Text style={{ color: Color.textoSuave }}>#{v.id} </Text>
              {v.cliente}
            </Text>
            <Text style={{ fontWeight: "700", color: Color.exito }}>{dinero(v.total)}</Text>
          </View>
          <View style={[base.row, { marginTop: 6 }]}>
            <Badge texto={v.origen === "EN_LINEA" ? "En línea" : "Mostrador"} color={v.origen === "EN_LINEA" ? "blue" : "green"} />
            <Text style={base.cardText}>{v.forma_pago}</Text>
          </View>
          <View style={[base.row, { marginTop: 4, justifyContent: "space-between" }]}>
            <Text style={base.cardText}>
              <FontAwesome5 name="calendar-alt" size={11} color={Color.textoSuave} /> {v.fecha}
            </Text>
            <Text style={base.cardText}>{v.piezas} pza(s)</Text>
          </View>
        </TouchableOpacity>
      )}
    />
  );
}
