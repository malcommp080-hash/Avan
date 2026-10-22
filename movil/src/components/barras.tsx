import { StyleSheet, Text, View } from "react-native";

import { Color } from "@/constants/estilos";

type Props = {
  datos: { etiqueta: string; valor: number }[];
  formato: (n: number) => string;
  // Línea: sólo se dibuja la punta de cada barra (vista "Línea" del dashboard web)
  linea?: boolean;
  alto?: number;
};

/** Gráfica de barras verticales sencilla, con los colores del panel. */
export function Barras({ datos, formato, linea, alto = 160 }: Props) {
  const max = Math.max(1, ...datos.map((d) => d.valor));

  return (
    <View>
      <View style={[styles.area, { height: alto }]}>
        {datos.map((d) => {
          const h = Math.max(2, (d.valor / max) * (alto - 22));
          return (
            <View key={d.etiqueta} style={styles.columna}>
              <Text style={styles.valor} numberOfLines={1}>
                {d.valor > 0 ? formato(d.valor).replace(".00", "") : ""}
              </Text>
              {linea ? (
                <View style={{ height: h, justifyContent: "flex-start", alignItems: "center", width: "100%" }}>
                  <View style={styles.punto} />
                  <View style={styles.guia} />
                </View>
              ) : (
                <View style={[styles.barra, { height: h }]} />
              )}
            </View>
          );
        })}
      </View>
      <View style={styles.ejes}>
        {datos.map((d) => (
          <Text key={d.etiqueta} style={styles.etiqueta} numberOfLines={1}>
            {d.etiqueta}
          </Text>
        ))}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  area: {
    flexDirection: "row",
    alignItems: "flex-end",
    gap: 6,
    borderBottomWidth: 1,
    borderBottomColor: "rgba(255,255,255,.08)",
  },
  columna: {
    flex: 1,
    alignItems: "center",
    justifyContent: "flex-end",
  },
  barra: {
    width: "70%",
    backgroundColor: "rgba(79,142,247,.75)",
    borderTopLeftRadius: 4,
    borderTopRightRadius: 4,
  },
  punto: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: Color.primario,
  },
  guia: {
    flex: 1,
    width: 2,
    backgroundColor: "rgba(79,142,247,.25)",
  },
  valor: {
    fontSize: 9,
    color: Color.textoSuave,
    marginBottom: 3,
  },
  ejes: {
    flexDirection: "row",
    gap: 6,
    marginTop: 6,
  },
  etiqueta: {
    flex: 1,
    fontSize: 10,
    color: Color.textoSuave,
    textAlign: "center",
  },
});
