import { FontAwesome5 } from "@expo/vector-icons";
import { useState } from "react";
import { FlatList, Modal, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Entrada } from "@/components/entrada";
import { Boton } from "@/components/panel";
import { base, Color } from "@/constants/estilos";

export type Opcion = { id: number; nombre: string; detalle?: string };

type Props = {
  placeholder: string;
  opciones: Opcion[];
  valor: number | null;
  onChange: (id: number) => void;
};

/** Campo que abre una lista con buscador para elegir cliente, producto, etc. (equivale al <select> del panel). */
export function Selector({ placeholder, opciones, valor, onChange }: Props) {
  const [abierto, setAbierto] = useState(false);
  const [buscar, setBuscar] = useState("");

  const elegido = opciones.find((o) => o.id === valor);
  const filtradas = opciones.filter((o) => o.nombre.toLowerCase().includes(buscar.toLowerCase()));

  return (
    <>
      <TouchableOpacity style={[base.input, styles.campo]} onPress={() => setAbierto(true)}>
        <Text style={elegido ? styles.valor : styles.placeholder} numberOfLines={1}>
          {elegido ? elegido.nombre : placeholder}
        </Text>
        <FontAwesome5 name="chevron-down" size={11} color={Color.textoSuave} />
      </TouchableOpacity>

      <Modal visible={abierto} animationType="slide" onRequestClose={() => setAbierto(false)}>
        <View style={styles.modal}>
          <Text style={[base.title, { marginBottom: 14 }]}>{placeholder}</Text>
          <Entrada icono="search" placeholder="Buscar…" value={buscar} onChangeText={setBuscar} autoFocus />
          <FlatList
            data={filtradas}
            keyExtractor={(o) => String(o.id)}
            keyboardShouldPersistTaps="handled"
            ListEmptyComponent={<Text style={base.vacio}>Sin resultados</Text>}
            renderItem={({ item }) => (
              <TouchableOpacity
                style={[base.card, item.id === valor && styles.elegida]}
                onPress={() => {
                  onChange(item.id);
                  setAbierto(false);
                  setBuscar("");
                }}>
                <Text style={base.cardTitle}>{item.nombre}</Text>
                {item.detalle ? <Text style={base.cardText}>{item.detalle}</Text> : null}
              </TouchableOpacity>
            )}
          />
          <Boton texto="Cerrar" tipo="outline" onPress={() => setAbierto(false)} />
        </View>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  campo: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
  },
  valor: {
    flex: 1,
    color: Color.texto,
    fontSize: 14,
  },
  placeholder: {
    flex: 1,
    color: Color.placeholder,
    fontSize: 14,
  },
  modal: {
    flex: 1,
    padding: 16,
    paddingTop: 50,
    paddingBottom: 30,
    backgroundColor: Color.fondo,
  },
  elegida: {
    borderColor: Color.primario,
    backgroundColor: "rgba(79,142,247,.12)",
  },
});
