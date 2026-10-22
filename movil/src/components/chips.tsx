import { ScrollView, StyleSheet, Text, TouchableOpacity } from "react-native";

import { Color } from "@/constants/estilos";

type Props<T extends string> = {
  opciones: Record<T, string>;
  valor: T;
  onChange: (v: T) => void;
};

/** Botones para elegir una opción (estado, forma de pago, rol…), como los .ch-btn del panel. */
export function Chips<T extends string>({ opciones, valor, onChange }: Props<T>) {
  return (
    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.fila}>
      {(Object.keys(opciones) as T[]).map((clave) => (
        <TouchableOpacity
          key={clave}
          style={[styles.chip, valor === clave && styles.activo]}
          onPress={() => onChange(clave)}>
          <Text style={[styles.texto, valor === clave && styles.textoActivo]}>{opciones[clave]}</Text>
        </TouchableOpacity>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  fila: {
    gap: 6,
    paddingBottom: 12,
  },
  chip: {
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: Color.borde,
    backgroundColor: "transparent",
  },
  activo: {
    backgroundColor: Color.primario,
    borderColor: Color.primario,
  },
  texto: {
    color: Color.textoSuave,
    fontSize: 12.5,
    fontWeight: "500",
  },
  textoActivo: {
    color: "#fff",
  },
});
