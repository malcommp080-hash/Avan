import { FontAwesome5 } from "@expo/vector-icons";
import { useState } from "react";
import { ActivityIndicator, RefreshControl, ScrollView, Text, TouchableOpacity, View } from "react-native";

import { Entrada } from "@/components/entrada";
import { Aviso, Badge, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { Selector } from "@/components/selector";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero } from "@/lib/api";
import { confirmar } from "@/lib/dialogos";

type Pedido = { id: number; cliente: string; fecha: string; total: number; piezas: number; dias: number; prioridad: "URGENTE" | "ALTA" | "NORMAL" };

const COLOR_PRIORIDAD = { URGENTE: "red", ALTA: "orange", NORMAL: "green" } as const;

const hoy = () => new Date().toISOString().slice(0, 10);

export default function PedidosScreen() {
  const { datos, cargando, recargar } = useApi<{ pedidos: Pedido[]; clientes: { id: number; nombre: string }[] }>("/pedidos-clientes");
  const [idCliente, setIdCliente] = useState<number | null>(null);
  const [fecha, setFecha] = useState(hoy());
  const [total, setTotal] = useState("");
  const [piezas, setPiezas] = useState("");
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const handleGuardar = async () => {
    if (!idCliente || !fecha || !total || !piezas) {
      setMensaje({ tipo: "err", texto: "Complete todos los campos antes de guardar." });
      return;
    }
    try {
      const r = await api("/pedidos-clientes", "POST", { id_cliente: idCliente, fecha, total, total_piezas: piezas });
      setMensaje({ tipo: "ok", texto: r.message });
      setIdCliente(null);
      setTotal("");
      setPiezas("");
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
  };

  const handleEliminar = async (p: Pedido) => {
    if (!(await confirmar("Eliminar pedido", `¿Eliminar el pedido #${p.id} de ${p.cliente}?`, "Eliminar", true))) return;
    try {
      const r = await api(`/pedidos-clientes/${p.id}`, "DELETE");
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
      <Encabezado titulo="Pedidos de clientes" sub="Los más antiguos aparecen primero (más urgentes)" />
      {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}

      <View style={base.formCard}>
        <TituloTarjeta icono="plus-circle" texto="Nuevo pedido" />
        <Text style={base.label}>Cliente</Text>
        <Selector placeholder="Selecciona un cliente" opciones={datos?.clientes ?? []} valor={idCliente} onChange={setIdCliente} />
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
        <Boton texto="Guardar pedido" icono="save" onPress={handleGuardar} />
      </View>

      {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
      {datos && datos.pedidos.length === 0 ? <Text style={base.vacio}>No hay pedidos.</Text> : null}
      {datos?.pedidos.map((p) => (
        <View key={p.id} style={base.card}>
          <View style={[base.row, { justifyContent: "space-between" }]}>
            <Text style={base.cardTitle}>
              <Text style={{ color: Color.textoSuave }}>#{p.id} </Text>
              {p.cliente}
            </Text>
            <Badge texto={p.prioridad} color={COLOR_PRIORIDAD[p.prioridad]} />
          </View>
          <Text style={base.cardText}>
            {p.fecha} · hace {p.dias} día(s)
          </Text>
          <View style={[base.row, { justifyContent: "space-between", marginTop: 4 }]}>
            <Text style={base.cardText}>
              {p.piezas} pieza(s) · <Text style={{ color: Color.exito, fontWeight: "700" }}>{dinero(p.total)}</Text>
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
