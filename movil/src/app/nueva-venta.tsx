import { FontAwesome5 } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import { useState } from "react";
import { ActivityIndicator, ScrollView, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Chips } from "@/components/chips";
import { Entrada } from "@/components/entrada";
import { Aviso, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { Selector } from "@/components/selector";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero } from "@/lib/api";

type Producto = {
  id: number; nombre: string; precio: number; precio_mayoreo: number | null; min_mayoreo: number | null;
  tallas: { talla: string; stock: number }[];
};
type Catalogo = {
  clientes: { id: number; nombre: string; telefono: string }[];
  productos: Producto[];
  formas: Record<string, string>;
};
type Renglon = { id_producto: number | null; talla: string; cantidad: string };

const renglonVacio: Renglon = { id_producto: null, talla: "", cantidad: "1" };

/** RF002: venta en mostrador. El precio y el stock los vuelve a calcular el servidor al guardar. */
export default function NuevaVenta() {
  const router = useRouter();
  const { datos, cargando } = useApi<Catalogo>("/ventas/catalogo");

  const [idCliente, setIdCliente] = useState<number | null>(null);
  const [formaPago, setFormaPago] = useState("EFECTIVO");
  const [renglones, setRenglones] = useState<Renglon[]>([{ ...renglonVacio }]);
  const [guardando, setGuardando] = useState(false);
  const [error, setError] = useState("");

  const producto = (id: number | null) => datos?.productos.find((p) => p.id === id);

  const actualizarRenglon = (index: number, cambios: Partial<Renglon>) => {
    const copia = [...renglones];
    copia[index] = { ...copia[index], ...cambios };
    setRenglones(copia);
  };

  // Mismo cálculo que el servidor: mayoreo según el total de piezas del producto (todas sus tallas)
  const piezasDe = (id: number | null) =>
    renglones.filter((r) => r.id_producto === id).reduce((s, r) => s + (parseInt(r.cantidad) || 0), 0);
  const precioDe = (p: Producto) =>
    p.precio_mayoreo && p.min_mayoreo && piezasDe(p.id) >= p.min_mayoreo ? p.precio_mayoreo : p.precio;
  const total = renglones.reduce((s, r) => {
    const p = producto(r.id_producto);
    return p ? s + precioDe(p) * (parseInt(r.cantidad) || 0) : s;
  }, 0);

  const handleGuardar = async () => {
    setError("");
    try {
      setGuardando(true);
      const r = await api("/ventas", "POST", {
        id_cliente: idCliente,
        forma_pago: formaPago,
        items: renglones.map((x) => ({ id_producto: x.id_producto, talla: x.talla, cantidad: parseInt(x.cantidad) || 0 })),
      });
      router.replace(`/venta/${r.id}`);
    } catch (e) {
      setError((e as Error).message);
    } finally {
      setGuardando(false);
    }
  };

  if (cargando && !datos) return <ActivityIndicator style={{ flex: 1, backgroundColor: Color.fondo }} color={Color.primario} />;
  if (!datos) return <Text style={[base.vacio, { flex: 1, backgroundColor: Color.fondo, paddingTop: 30 }]}>No se pudo cargar el catálogo.</Text>;

  return (
    <ScrollView contentContainerStyle={base.container} keyboardShouldPersistTaps="handled">
      <Encabezado titulo="Registrar venta" sub="Venta en mostrador; el stock se descuenta al guardar" />

      {error ? <Aviso tipo="err" texto={error} /> : null}

      <View style={base.formCard}>
        <TituloTarjeta icono="user" texto="Cliente y pago" />
        <Text style={base.label}>Cliente</Text>
        <Selector
          placeholder="Selecciona un cliente"
          opciones={datos.clientes.map((c) => ({ id: c.id, nombre: c.nombre, detalle: c.telefono }))}
          valor={idCliente}
          onChange={setIdCliente}
        />
        <Text style={base.label}>Forma de pago</Text>
        <Chips opciones={datos.formas} valor={formaPago} onChange={setFormaPago} />
      </View>

      {renglones.map((r, index) => {
        const p = producto(r.id_producto);
        const talla = p?.tallas.find((t) => t.talla === r.talla);
        return (
          <View key={index} style={base.formCard}>
            <View style={[base.row, { justifyContent: "space-between" }]}>
              <TituloTarjeta icono="box-open" texto={`Producto ${index + 1}`} color={Color.turquesa} />
              {renglones.length > 1 ? (
                <TouchableOpacity onPress={() => setRenglones(renglones.filter((_, i) => i !== index))} style={{ marginTop: -10 }}>
                  <FontAwesome5 name="trash" size={14} color={Color.peligro} />
                </TouchableOpacity>
              ) : null}
            </View>
            <Selector
              placeholder="Selecciona un producto"
              opciones={datos.productos.map((x) => ({ id: x.id, nombre: x.nombre, detalle: dinero(x.precio) }))}
              valor={r.id_producto}
              onChange={(id) => actualizarRenglon(index, { id_producto: id, talla: "" })}
            />

            {p ? (
              <>
                <Text style={base.label}>Talla (piezas disponibles)</Text>
                <Chips
                  opciones={Object.fromEntries(p.tallas.map((t) => [t.talla, `${t.talla} (${t.stock})`]))}
                  valor={r.talla}
                  onChange={(t) => actualizarRenglon(index, { talla: t })}
                />
                <View style={[base.row, { alignItems: "flex-start" }]}>
                  <View style={{ flex: 1 }}>
                    <Text style={base.label}>Cantidad</Text>
                    <Entrada
                      keyboardType="numeric"
                      value={r.cantidad}
                      onChangeText={(v) => actualizarRenglon(index, { cantidad: v.replace(/\D/g, "") })}
                    />
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={base.label}>Subtotal</Text>
                    <Text style={styles.subtotal}>{dinero(precioDe(p) * (parseInt(r.cantidad) || 0))}</Text>
                  </View>
                </View>
                {talla && (parseInt(r.cantidad) || 0) > talla.stock ? (
                  <Aviso tipo="warn" texto={`Sólo hay ${talla.stock} pieza(s) en talla ${talla.talla}.`} />
                ) : null}
                {p.precio_mayoreo && p.min_mayoreo ? (
                  <Text style={base.cardText}>
                    Mayoreo: {dinero(p.precio_mayoreo)} desde {p.min_mayoreo} piezas
                  </Text>
                ) : null}
              </>
            ) : null}
          </View>
        );
      })}

      <Boton texto="Agregar producto" icono="plus" tipo="outline" onPress={() => setRenglones([...renglones, { ...renglonVacio }])} style={{ marginBottom: 16 }} />

      <View style={styles.total}>
        <Text style={styles.totalTexto}>Total estimado</Text>
        <Text style={styles.totalTexto}>{dinero(total)}</Text>
      </View>

      <Boton texto={guardando ? "Guardando…" : "Guardar venta"} icono="save" tipo="success" onPress={handleGuardar} disabled={guardando} />
      <Boton texto="Cancelar" tipo="outline" onPress={() => router.back()} style={{ marginTop: 10, marginBottom: 30 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  subtotal: {
    height: 46,
    lineHeight: 46,
    fontSize: 16,
    fontWeight: "700",
    color: Color.exito,
  },
  total: {
    flexDirection: "row",
    justifyContent: "space-between",
    backgroundColor: "rgba(79,142,247,.15)",
    borderColor: Color.primario,
    borderWidth: 1,
    borderRadius: 12,
    padding: 14,
    marginBottom: 8,
  },
  totalTexto: {
    color: Color.texto,
    fontWeight: "700",
    fontSize: 16,
  },
});
