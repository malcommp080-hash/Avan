import { FontAwesome5 } from "@expo/vector-icons";
import { useState } from "react";
import { ActivityIndicator, RefreshControl, ScrollView, Text, TouchableOpacity, View } from "react-native";

import { Chips } from "@/components/chips";
import { Entrada } from "@/components/entrada";
import { Aviso, Badge, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { Selector } from "@/components/selector";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero } from "@/lib/api";
import { confirmar } from "@/lib/dialogos";

type Pedido = { id: number; fecha: string; total: number; piezas: number; estado: string; proveedor: string };
type Datos = { pedidos: Pedido[]; proveedores: { id: number; nombre: string }[]; estados: string[] };

const COLOR_ESTADO: Record<string, "orange" | "blue" | "green" | "red"> = {
  PENDIENTE: "orange", ENVIADO: "blue", RECIBIDO: "green", CANCELADO: "red",
};

const hoy = () => new Date().toISOString().slice(0, 10);

export default function PedidosProveedoresScreen() {
  const { datos, cargando, recargar } = useApi<Datos>("/pedidos-proveedores");
  const [idProveedor, setIdProveedor] = useState<number | null>(null);
  const [fecha, setFecha] = useState(hoy());
  const [total, setTotal] = useState("");
  const [piezas, setPiezas] = useState("");
  const [estado, setEstado] = useState("PENDIENTE");
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const estados = Object.fromEntries((datos?.estados ?? ["PENDIENTE"]).map((e) => [e, e.charAt(0) + e.slice(1).toLowerCase()]));

  const handleGuardar = async () => {
    if (!idProveedor || !fecha || !total || !piezas) {
      setMensaje({ tipo: "err", texto: "Complete todos los campos antes de guardar." });
      return;
    }
    try {
      const r = await api("/pedidos-proveedores", "POST", { id_proovedor: idProveedor, fecha, total, total_piezas: piezas, estado });
      setMensaje({ tipo: "ok", texto: r.message });
      setIdProveedor(null);
      setTotal("");
      setPiezas("");
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
  };

  const handleEliminar = async (p: Pedido) => {
    if (!(await confirmar("Eliminar pedido", `¿Eliminar el pedido #${p.id} a ${p.proveedor}?`, "Eliminar", true))) return;
    try {
      const r = await api(`/pedidos-proveedores/${p.id}`, "DELETE");
      setMensaje({ tipo: "ok", texto: r.message });
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
  };

  return (
    <ScrollView
      contentContainerStyle={base.container}
      keyboardShouldPersistTaps="handled"
      refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}>
      <Encabezado titulo="Pedidos a proveedores" sub="Compras de mercancía y su estado" />
      {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}

      <View style={base.formCard}>
        <TituloTarjeta icono="plus-circle" texto="Nuevo pedido a proveedor" />
        <Text style={base.label}>Proveedor</Text>
        <Selector placeholder="Selecciona un proveedor" opciones={datos?.proveedores ?? []} valor={idProveedor} onChange={setIdProveedor} />
        <Text style={base.label}>Fecha</Text>
        <Entrada placeholder="AAAA-MM-DD" value={fecha} onChangeText={setFecha} />
        <View style={base.row}>
          <View style={{ flex: 1 }}>
            <Text style={base.label}>Total</Text>
            <Entrada placeholder="0.00" keyboardType="decimal-pad" value={total} onChangeText={setTotal} />
          </View>
          <View style={{ flex: 1 }}>
            <Text style={base.label}>Piezas</Text>
            <Entrada placeholder="0" keyboardType="numeric" value={piezas} onChangeText={setPiezas} />
          </View>
        </View>
        <Text style={base.label}>Estado</Text>
        <Chips opciones={estados} valor={estado} onChange={setEstado} />
        <Boton texto="Guardar pedido" icono="save" onPress={handleGuardar} />
      </View>

      {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
      {datos && datos.pedidos.length === 0 ? <Text style={base.vacio}>No hay pedidos a proveedores.</Text> : null}
      {datos?.pedidos.map((p) => (
        <View key={p.id} style={base.card}>
          <View style={[base.row, { justifyContent: "space-between" }]}>
            <Text style={base.cardTitle}>
              <Text style={{ color: Color.textoSuave }}>#{p.id} </Text>
              {p.proveedor}
            </Text>
            <Badge texto={p.estado} color={COLOR_ESTADO[p.estado] ?? "blue"} />
          </View>
          <View style={[base.row, { justifyContent: "space-between", marginTop: 4 }]}>
            <Text style={base.cardText}>
              {p.fecha} · {p.piezas} pza(s) · <Text style={{ color: Color.exito, fontWeight: "700" }}>{dinero(p.total)}</Text>
            </Text>
            <TouchableOpacity onPress={() => handleEliminar(p)} style={{ padding: 6 }}>
              <FontAwesome5 name="trash" size={14} color={Color.peligro} />
            </TouchableOpacity>
          </View>
        </View>
      ))}
    </ScrollView>
  );
}
