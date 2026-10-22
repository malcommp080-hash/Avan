import { useState } from "react";
import { ActivityIndicator, FlatList, RefreshControl, StyleSheet, Text, View } from "react-native";

import { Chips } from "@/components/chips";
import { Badge, Encabezado, Estadistica } from "@/components/panel";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";

type Registro = { id: number; tipo: "ADMIN" | "CLIENTE"; telefono: string; resultado: string; nombre: string | null; ip: string; fecha: string };
type Bitacora = { exitosHoy: number; fallidosHoy: number; bloqueosHoy: number; resultados: string[]; registros: Registro[] };

const TIPOS = { "": "Todos", ADMIN: "Panel", CLIENTE: "Tienda" };
const COLOR_RESULTADO: Record<string, "green" | "orange" | "red" | "blue"> = {
  EXITO: "green", FALLIDO: "orange", BLOQUEADO: "red", LOGOUT: "blue", INACTIVIDAD: "blue",
};

/** RF014: bitácora de intentos de acceso (sólo administradores). */
export default function BitacoraScreen() {
  const [tipo, setTipo] = useState<keyof typeof TIPOS>("");
  const [resultado, setResultado] = useState("");
  const params = [tipo && `tipo=${tipo}`, resultado && `resultado=${resultado}`].filter(Boolean).join("&");
  const { datos, cargando, recargar } = useApi<Bitacora>(`/bitacora${params ? `?${params}` : ""}`);

  const resultados = { "": "Todos", ...Object.fromEntries((datos?.resultados ?? []).map((r) => [r, r.charAt(0) + r.slice(1).toLowerCase()])) };

  return (
    <FlatList
      style={{ backgroundColor: Color.fondo }}
      contentContainerStyle={base.container}
      data={datos?.registros ?? []}
      keyExtractor={(r) => String(r.id)}
      refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}
      ListHeaderComponent={
        <>
          <Encabezado titulo="Bitácora de accesos" sub="Últimos 100 intentos de inicio de sesión" />
          {datos ? (
            <View style={styles.grid}>
              <Estadistica color="green" valor={String(datos.exitosHoy)} etiqueta="Accesos hoy" icono="check-circle" />
              <Estadistica color="amber" valor={String(datos.fallidosHoy)} etiqueta="Fallidos hoy" icono="times-circle" />
              <Estadistica color="red" valor={String(datos.bloqueosHoy)} etiqueta="Bloqueos hoy" icono="lock" />
            </View>
          ) : null}
          <Chips opciones={TIPOS} valor={tipo} onChange={setTipo} />
          <Chips opciones={resultados} valor={resultado} onChange={setResultado} />
          {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
        </>
      }
      ListEmptyComponent={datos ? <Text style={base.vacio}>Sin registros con esos filtros.</Text> : null}
      renderItem={({ item: r }) => (
        <View style={base.card}>
          <View style={[base.row, { justifyContent: "space-between" }]}>
            <Text style={base.cardTitle}>{r.nombre ?? (r.telefono || "—")}</Text>
            <Badge texto={r.resultado} color={COLOR_RESULTADO[r.resultado] ?? "blue"} />
          </View>
          <Text style={base.cardText}>
            {r.tipo === "ADMIN" ? "Panel" : "Tienda"}
            {r.telefono ? ` · tel. ${r.telefono}` : ""} · IP {r.ip}
          </Text>
          <Text style={base.cardText}>{r.fecha}</Text>
        </View>
      )}
    />
  );
}

const styles = StyleSheet.create({
  grid: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 12,
    marginBottom: 16,
  },
});
