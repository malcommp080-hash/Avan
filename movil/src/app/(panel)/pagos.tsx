import { Image } from "expo-image";
import { useState } from "react";
import { ActivityIndicator, FlatList, Modal, RefreshControl, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Chips } from "@/components/chips";
import { Entrada } from "@/components/entrada";
import { Aviso, Badge, Boton, Encabezado } from "@/components/panel";
import { urlServidor } from "@/constants/api";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero } from "@/lib/api";
import { confirmar } from "@/lib/dialogos";

type Estado = "PENDIENTE" | "APROBADO" | "RECHAZADO";
type Pago = {
  id: number; folio: string; monto: number; estado: Estado; estado_texto: string; referencia: string | null;
  comprobante: string | null; comprobante_en: string | null; validado_por: string | null; validado_en: string | null;
  motivo_rechazo: string | null; id_venta: number; cliente: string; telefono: string | null;
  detalles: { producto: string; talla: string; cantidad: number }[];
};

const ESTADOS: Record<Estado, string> = { PENDIENTE: "Pendientes", APROBADO: "Aprobados", RECHAZADO: "Rechazados" };
const COLOR_ESTADO = { PENDIENTE: "orange", APROBADO: "green", RECHAZADO: "red" } as const;

/** RF004: revisar el ticket de las compras en línea y aprobar o rechazar el pago. */
export default function PagosScreen() {
  const [estado, setEstado] = useState<Estado>("PENDIENTE");
  const { datos, cargando, recargar } = useApi<{ pagos: Pago[]; puedeValidar: boolean }>(`/pagos?estado=${estado}`);
  const [ticket, setTicket] = useState<string | null>(null);
  const [rechazando, setRechazando] = useState<Pago | null>(null);
  const [motivo, setMotivo] = useState("");
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const aprobar = async (p: Pago) => {
    if (!(await confirmar("Aprobar pago", `¿Aprobar ${p.folio} por ${dinero(p.monto)}?`, "Aprobar"))) return;
    try {
      const r = await api(`/pagos/${p.id}/aprobar`, "PUT");
      setMensaje({ tipo: "ok", texto: r.message });
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
    recargar();
  };

  const confirmarRechazo = async () => {
    if (!rechazando) return;
    if (!motivo.trim()) {
      setMensaje({ tipo: "err", texto: "Escribe el motivo del rechazo para que el cliente lo vea." });
      return;
    }
    try {
      const r = await api(`/pagos/${rechazando.id}/rechazar`, "PUT", { motivo });
      setMensaje({ tipo: "ok", texto: r.message });
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
    setRechazando(null);
    setMotivo("");
    recargar();
  };

  return (
    <View style={{ flex: 1, backgroundColor: Color.fondo }}>
      <FlatList
        contentContainerStyle={base.container}
        data={datos?.pagos ?? []}
        keyExtractor={(p) => String(p.id)}
        refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}
        ListHeaderComponent={
          <>
            <Encabezado titulo="Validación de pago" sub="Revisa el ticket de las compras en línea y aprueba o rechaza el pago" />
            {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}
            <Chips opciones={ESTADOS} valor={estado} onChange={setEstado} />
            {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
          </>
        }
        ListEmptyComponent={datos ? <Text style={base.vacio}>No hay pagos {ESTADOS[estado].toLowerCase()}.</Text> : null}
        renderItem={({ item: p }) => (
          <View style={base.card}>
            <View style={[base.row, { justifyContent: "space-between" }]}>
              <Text style={[base.cardTitle, { fontFamily: "monospace" }]}>{p.folio}</Text>
              <Text style={styles.monto}>{dinero(p.monto)}</Text>
            </View>
            <Text style={base.cardText}>
              {p.cliente}
              {p.telefono ? ` · ${p.telefono}` : ""}
            </Text>
            <View style={{ marginVertical: 6 }}>
              <Badge texto={p.estado_texto} color={COLOR_ESTADO[p.estado]} />
            </View>
            {p.referencia ? <Text style={base.cardText}>Referencia: {p.referencia}</Text> : null}
            {p.detalles.map((d, i) => (
              <Text key={i} style={base.cardText}>
                • {d.cantidad} × {d.producto} (talla {d.talla})
              </Text>
            ))}
            {p.validado_por ? (
              <Text style={base.cardText}>
                Validó {p.validado_por} el {p.validado_en}
              </Text>
            ) : null}
            {p.motivo_rechazo ? <Text style={{ color: "#fca5a5", marginTop: 4 }}>Motivo: {p.motivo_rechazo}</Text> : null}

            {p.comprobante ? (
              <TouchableOpacity onPress={() => setTicket(urlServidor(p.comprobante!))}>
                <Image source={{ uri: urlServidor(p.comprobante) }} style={styles.miniatura} contentFit="cover" />
                <Text style={styles.verTicket}>Ver ticket ({p.comprobante_en})</Text>
              </TouchableOpacity>
            ) : null}

            {p.estado === "PENDIENTE" && datos?.puedeValidar ? (
              <View style={[base.row, { marginTop: 8 }]}>
                <Boton texto="Aprobar" icono="check" tipo="success" disabled={!p.comprobante} onPress={() => aprobar(p)} style={{ flex: 1 }} />
                <Boton texto="Rechazar" icono="times" tipo="danger" onPress={() => setRechazando(p)} style={{ flex: 1 }} />
              </View>
            ) : null}
          </View>
        )}
      />

      {/* Ticket en pantalla completa */}
      <Modal visible={ticket !== null} transparent animationType="fade" onRequestClose={() => setTicket(null)}>
        <TouchableOpacity style={styles.fondoModal} activeOpacity={1} onPress={() => setTicket(null)}>
          {ticket ? <Image source={{ uri: ticket }} style={styles.ticket} contentFit="contain" /> : null}
          <Text style={styles.cerrar}>Toca para cerrar</Text>
        </TouchableOpacity>
      </Modal>

      {/* Motivo del rechazo */}
      <Modal visible={rechazando !== null} transparent animationType="slide" onRequestClose={() => setRechazando(null)}>
        <View style={styles.fondoModal}>
          <View style={styles.dialogo}>
            <Text style={base.sectionTitle}>Rechazar {rechazando?.folio}</Text>
            <Text style={[base.cardText, { marginBottom: 10 }]}>Las piezas apartadas regresarán al inventario.</Text>
            <Entrada
              style={styles.textArea}
              placeholder="Motivo del rechazo (lo verá el cliente)"
              multiline
              value={motivo}
              onChangeText={setMotivo}
            />
            <Boton texto="Rechazar pago" icono="times" tipo="danger" onPress={confirmarRechazo} />
            <Boton texto="Cancelar" tipo="outline" onPress={() => setRechazando(null)} style={{ marginTop: 10 }} />
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  monto: {
    fontWeight: "700",
    color: Color.exito,
    fontSize: 15,
  },
  miniatura: {
    width: "100%",
    height: 160,
    borderRadius: 8,
    marginTop: 10,
    backgroundColor: Color.input,
  },
  verTicket: {
    color: Color.primario,
    fontWeight: "600",
    marginTop: 4,
    fontSize: 13,
  },
  fondoModal: {
    flex: 1,
    backgroundColor: "rgba(0,0,0,0.85)",
    justifyContent: "center",
    padding: 16,
  },
  ticket: {
    width: "100%",
    height: "80%",
  },
  cerrar: {
    color: "#fff",
    textAlign: "center",
    marginTop: 10,
  },
  dialogo: {
    backgroundColor: Color.tarjeta,
    borderColor: Color.borde,
    borderWidth: 1,
    borderRadius: 12,
    padding: 16,
  },
  textArea: {
    height: 90,
    paddingTop: 12,
    textAlignVertical: "top",
  },
});
