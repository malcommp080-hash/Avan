import { FontAwesome5 } from "@expo/vector-icons";
import { Image } from "expo-image";
import { useState } from "react";
import { ActivityIndicator, FlatList, RefreshControl, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Entrada } from "@/components/entrada";
import { Aviso, Badge, Boton, Encabezado, Estadistica } from "@/components/panel";
import { urlServidor } from "@/constants/api";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero } from "@/lib/api";

type Talla = { id: number; talla: string; stock: number };
type Producto = {
  id: number; nombre: string; categoria: string | null; segmento: string; precio: number;
  stock: number; activo: boolean; imagen: string | null; tallas: Talla[];
};
type Inventario = { totalProductos: number; totalStock: number; valor: number; stockBajo: number; productos: Producto[] };

export default function InventarioScreen() {
  const { datos, cargando, recargar } = useApi<Inventario>("/inventario");
  const [abierto, setAbierto] = useState<number | null>(null);
  const [stock, setStock] = useState<Record<number, string>>({});
  const [buscar, setBuscar] = useState("");
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const abrir = (p: Producto) => {
    setAbierto(abierto === p.id ? null : p.id);
    setStock(Object.fromEntries(p.tallas.map((t) => [t.id, String(t.stock)])));
  };

  const guardar = async (p: Producto) => {
    try {
      const tallas = Object.fromEntries(Object.entries(stock).map(([id, v]) => [id, parseInt(v) || 0]));
      const r = await api(`/inventario/${p.id}`, "PUT", { tallas });
      setMensaje({ tipo: "ok", texto: r.message });
      setAbierto(null);
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
  };

  const productos = (datos?.productos ?? []).filter((p) => p.nombre.toLowerCase().includes(buscar.toLowerCase()));

  return (
    <FlatList
      style={{ backgroundColor: Color.fondo }}
      contentContainerStyle={base.container}
      data={productos}
      keyExtractor={(p) => String(p.id)}
      keyboardShouldPersistTaps="handled"
      refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}
      ListHeaderComponent={
        <>
          <Encabezado titulo="Inventario" sub="Stock por talla; toca un producto para ajustarlo" />
          {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}
          {datos ? (
            <View style={styles.grid}>
              <Estadistica color="teal" valor={String(datos.totalProductos)} etiqueta="Productos" icono="box-open" />
              <Estadistica color="green" valor={String(datos.totalStock)} etiqueta="Piezas en stock" icono="cubes" />
              <Estadistica color="amber" valor={dinero(datos.valor)} etiqueta="Valor del inventario" icono="dollar-sign" />
              <Estadistica color="red" valor={String(datos.stockBajo)} etiqueta="Con stock bajo (< 5)" icono="exclamation-triangle" />
            </View>
          ) : null}
          <Entrada icono="search" placeholder="Buscar producto" value={buscar} onChangeText={setBuscar} />
          {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
        </>
      }
      renderItem={({ item: p }) => {
        const bajo = p.tallas.some((t) => t.stock < 5);
        return (
          <View style={[base.card, abierto === p.id && { borderColor: Color.primario }]}>
            <TouchableOpacity style={base.row} onPress={() => abrir(p)}>
              {p.imagen ? (
                <Image source={{ uri: urlServidor(p.imagen) }} style={styles.foto} contentFit="cover" />
              ) : (
                <View style={[styles.foto, styles.sinFoto]}>
                  <FontAwesome5 name="tshirt" size={18} color={Color.textoSuave} />
                </View>
              )}
              <View style={{ flex: 1 }}>
                <Text style={base.cardTitle}>{p.nombre}</Text>
                <Text style={base.cardText}>
                  {p.categoria ?? "Sin categoría"} · {p.segmento} · {dinero(p.precio)}
                </Text>
                <View style={styles.tallasFila}>
                  {p.tallas.map((t) => (
                    <Badge key={t.id} texto={`${t.talla}: ${t.stock}`} color={t.stock < 5 ? "red" : "blue"} />
                  ))}
                  {!p.activo ? <Badge texto="Oculto" color="orange" /> : null}
                </View>
              </View>
              <Text style={[styles.total, bajo && { color: Color.peligro }]}>{p.stock}</Text>
            </TouchableOpacity>

            {abierto === p.id ? (
              <View style={styles.editor}>
                <Text style={base.label}>Ajustar piezas por talla</Text>
                <View style={styles.tallas}>
                  {p.tallas.map((t) => (
                    <View key={t.id} style={styles.talla}>
                      <Text style={styles.tallaNombre}>{t.talla}</Text>
                      <Entrada
                        style={{ textAlign: "center", marginBottom: 0 }}
                        keyboardType="numeric"
                        value={stock[t.id] ?? ""}
                        onChangeText={(v) => setStock({ ...stock, [t.id]: v.replace(/\D/g, "") })}
                      />
                    </View>
                  ))}
                </View>
                <Boton texto="Guardar stock" icono="save" onPress={() => guardar(p)} />
              </View>
            ) : null}
          </View>
        );
      }}
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
  foto: {
    width: 56,
    height: 56,
    borderRadius: 8,
    backgroundColor: Color.input,
  },
  sinFoto: {
    justifyContent: "center",
    alignItems: "center",
  },
  tallasFila: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 4,
    marginTop: 6,
  },
  total: {
    fontSize: 20,
    fontWeight: "800",
    color: Color.primario,
  },
  editor: {
    marginTop: 12,
    borderTopWidth: 1,
    borderTopColor: Color.borde,
    paddingTop: 12,
  },
  tallas: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 8,
    marginBottom: 8,
  },
  talla: {
    width: 72,
  },
  tallaNombre: {
    textAlign: "center",
    fontWeight: "700",
    color: Color.texto,
    marginBottom: 4,
  },
});
