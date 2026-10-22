import { FontAwesome5 } from "@expo/vector-icons";
import { FlatList, RefreshControl, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Boton, Encabezado } from "@/components/panel";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api } from "@/lib/api";
import { avisar } from "@/lib/dialogos";

type Notificacion = { id: number; tipo: string; mensaje: string; leida: boolean; fecha: string | null };

export default function NotificacionesScreen() {
  const { datos, cargando, recargar } = useApi<{ notificaciones: Notificacion[] }>("/notificaciones");

  const leer = async (n: Notificacion) => {
    if (n.leida) return;
    await api(`/notificaciones/${n.id}`, "PUT").catch((e) => avisar("Error", e.message));
    recargar();
  };

  const leerTodas = async () => {
    await api("/notificaciones/leer-todas", "PUT").catch((e) => avisar("Error", e.message));
    recargar();
  };

  const lista = datos?.notificaciones ?? [];

  return (
    <FlatList
      style={{ backgroundColor: Color.fondo }}
      contentContainerStyle={base.container}
      data={lista}
      keyExtractor={(n) => String(n.id)}
      refreshControl={<RefreshControl refreshing={cargando} onRefresh={recargar} tintColor={Color.primario} />}
      ListHeaderComponent={
        <>
          <Encabezado titulo="Notificaciones" sub="Avisos del sistema para ti y para todos los empleados" />
          {lista.some((n) => !n.leida) ? (
            <Boton texto="Marcar todas como leídas" icono="check-double" tipo="outline" onPress={leerTodas} style={{ marginBottom: 14 }} />
          ) : null}
        </>
      }
      ListEmptyComponent={datos ? <Text style={base.vacio}>No tienes notificaciones.</Text> : null}
      renderItem={({ item: n }) => (
        <TouchableOpacity style={[base.card, styles.fila, !n.leida && styles.nueva]} onPress={() => leer(n)}>
          <View style={[styles.icono, { backgroundColor: n.leida ? Color.input : "rgba(79,142,247,.15)" }]}>
            <FontAwesome5 name="bell" size={13} color={n.leida ? Color.textoSuave : Color.primario} />
          </View>
          <View style={{ flex: 1 }}>
            <Text style={styles.tipo}>{n.tipo.replace(/_/g, " ")}</Text>
            <Text style={[base.cardTitle, { fontWeight: n.leida ? "400" : "700" }]}>{n.mensaje}</Text>
            <Text style={base.cardText}>{n.fecha}</Text>
          </View>
        </TouchableOpacity>
      )}
    />
  );
}

const styles = StyleSheet.create({
  fila: {
    flexDirection: "row",
    gap: 12,
  },
  nueva: {
    borderLeftWidth: 3,
    borderLeftColor: Color.primario,
  },
  icono: {
    width: 34,
    height: 34,
    borderRadius: 17,
    justifyContent: "center",
    alignItems: "center",
  },
  tipo: {
    fontSize: 10,
    color: Color.textoSuave,
    textTransform: "uppercase",
    letterSpacing: 0.6,
    marginBottom: 2,
  },
});
