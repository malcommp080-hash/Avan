import { FontAwesome5 } from "@expo/vector-icons";
import { ReactNode } from "react";
import { StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Color } from "@/constants/estilos";

type Props = {
  titulo: string;
  icono: React.ComponentProps<typeof FontAwesome5>["name"];
  colorIcono?: string;
  boton?: ReactNode;
  columnas: string[];
  filas: (string | ReactNode)[][];
  onFila?: (index: number) => void;
  vacio: string;
};

/** Tarjeta con tabla (.tcard): encabezado con icono, cabecera gris y renglones con borde. */
export function Tabla({ titulo, icono, colorIcono = Color.primario, boton, columnas, filas, onFila, vacio }: Props) {
  return (
    <View style={styles.tcard}>
      <View style={styles.hdr}>
        <View style={styles.ttl}>
          <FontAwesome5 name={icono} size={13} color={colorIcono} />
          <Text style={styles.ttlTexto}>{titulo}</Text>
        </View>
        {boton}
      </View>
      <View style={styles.fila}>
        {columnas.map((c, i) => (
          <Text key={c} style={[styles.th, i === 0 && styles.primera]}>
            {c}
          </Text>
        ))}
      </View>
      {filas.length === 0 ? <Text style={styles.vacio}>{vacio}</Text> : null}
      {filas.map((f, i) => (
        <TouchableOpacity
          key={i}
          disabled={!onFila}
          onPress={() => onFila?.(i)}
          style={[styles.fila, styles.tr, i === filas.length - 1 && { borderBottomWidth: 0 }]}>
          {f.map((celda, j) =>
            typeof celda === "string" ? (
              <Text key={j} style={[styles.td, j === 0 && styles.primera]} numberOfLines={2}>
                {celda}
              </Text>
            ) : (
              <View key={j} style={[styles.tdVista, j === 0 && styles.primera]}>
                {celda}
              </View>
            )
          )}
        </TouchableOpacity>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  tcard: {
    backgroundColor: Color.tarjeta,
    borderWidth: 1,
    borderColor: Color.borde,
    borderRadius: 12,
    overflow: "hidden",
    marginBottom: 18,
  },
  hdr: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: Color.borde,
  },
  ttl: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
  },
  ttlTexto: {
    fontSize: 13.5,
    fontWeight: "600",
    color: Color.texto,
  },
  fila: {
    flexDirection: "row",
    backgroundColor: "#11141e",
  },
  tr: {
    backgroundColor: "transparent",
    borderBottomWidth: 1,
    borderBottomColor: Color.borde,
  },
  th: {
    flex: 1,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 11,
    fontWeight: "600",
    color: Color.textoSuave,
    textTransform: "uppercase",
    letterSpacing: 0.5,
  },
  td: {
    flex: 1,
    paddingHorizontal: 12,
    paddingVertical: 11,
    fontSize: 13,
    color: Color.texto,
  },
  tdVista: {
    flex: 1,
    paddingHorizontal: 12,
    paddingVertical: 8,
    justifyContent: "center",
  },
  primera: {
    flex: 0.6,
  },
  vacio: {
    textAlign: "center",
    color: Color.textoSuave,
    padding: 16,
    fontSize: 13,
  },
});
