import { FontAwesome5 } from "@expo/vector-icons";
import { LinearGradient } from "expo-linear-gradient";
import { StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Badge as Colores, base, Color, Degradado } from "@/constants/estilos";

type Icono = React.ComponentProps<typeof FontAwesome5>["name"];

/** Título y subtítulo de página (.ptitle / .psub). */
export function Encabezado({ titulo, sub }: { titulo: string; sub?: string }) {
  return (
    <>
      <Text style={base.title}>{titulo}</Text>
      {sub ? <Text style={base.subtitle}>{sub}</Text> : <View style={{ height: 14 }} />}
    </>
  );
}

/** Tarjeta de estadística con degradado (.sc .sc-teal …). */
export function Estadistica({
  valor, etiqueta, icono, color, accion, onPress,
}: {
  valor: string; etiqueta: string; icono: Icono; color: keyof typeof Degradado; accion?: string; onPress?: () => void;
}) {
  return (
    <TouchableOpacity activeOpacity={onPress ? 0.85 : 1} onPress={onPress} style={styles.scCaja}>
      <LinearGradient colors={Degradado[color]} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.sc}>
        <View style={{ flex: 1 }}>
          {/* adjustsFontSizeToFit no funciona en web: la letra se achica según el largo del valor */}
          <Text style={[styles.scVal, { fontSize: valor.length > 10 ? 18 : valor.length > 7 ? 21 : 26 }]} numberOfLines={1} adjustsFontSizeToFit>
            {valor}
          </Text>
          <Text style={styles.scLbl}>{etiqueta}</Text>
          {accion ? (
            <Text style={styles.scMore}>
              {accion} <FontAwesome5 name="arrow-right" size={9} color="rgba(255,255,255,.85)" />
            </Text>
          ) : null}
        </View>
        <FontAwesome5 name={icono} size={34} color="rgba(255,255,255,.22)" />
        <View style={styles.scCirculo} />
      </LinearGradient>
    </TouchableOpacity>
  );
}

/** Cabecera de tarjeta con icono de color (.ch-ttl / .tcard-ttl). */
export function TituloTarjeta({ icono, texto, color = Color.primario }: { icono: Icono; texto: string; color?: string }) {
  return (
    <View style={[base.row, { marginBottom: 12 }]}>
      <FontAwesome5 name={icono} size={13} color={color} />
      <Text style={styles.tTitulo}>{texto}</Text>
    </View>
  );
}

/** Etiqueta de estado (.badge .bg-green …). */
export function Badge({ texto, color }: { texto: string; color: keyof typeof Colores }) {
  return <Text style={[base.badge, Colores[color]]}>{texto}</Text>;
}

/** Botón del panel (.btn .btn-primary / .btn-success / .btn-danger / .btn-outline). */
export function Boton({
  texto, icono, onPress, tipo = "primary", disabled, style,
}: {
  texto: string; icono?: Icono; onPress: () => void; tipo?: "primary" | "success" | "danger" | "outline" | "orange" | "purple";
  disabled?: boolean; style?: object;
}) {
  const fondo = { primary: Color.primario, success: Color.exito, danger: Color.peligro, orange: Color.aviso, purple: Color.morado, outline: "transparent" }[tipo];
  const contorno = tipo === "outline";
  return (
    <TouchableOpacity
      disabled={disabled}
      onPress={onPress}
      style={[contorno ? base.outlineButton : base.button, !contorno && { backgroundColor: fondo }, disabled && { opacity: 0.45 }, style]}>
      {icono ? <FontAwesome5 name={icono} size={13} color={contorno ? Color.texto : "#fff"} /> : null}
      <Text style={contorno ? base.outlineButtonText : base.buttonText}>{texto}</Text>
    </TouchableOpacity>
  );
}

/** Aviso tipo .alert-ok / .alert-err / .alert-warn. */
export function Aviso({ texto, tipo }: { texto: string; tipo: "ok" | "err" | "warn" }) {
  const c = {
    ok: { bg: "rgba(34,197,94,.12)", borde: "rgba(34,197,94,.3)", texto: "#4ade80", icono: "check-circle" as Icono },
    err: { bg: "rgba(239,68,68,.12)", borde: "rgba(239,68,68,.3)", texto: "#fca5a5", icono: "exclamation-circle" as Icono },
    warn: { bg: "rgba(249,115,22,.12)", borde: "rgba(249,115,22,.3)", texto: "#fdba74", icono: "exclamation-triangle" as Icono },
  }[tipo];
  return (
    <View style={[styles.aviso, { backgroundColor: c.bg, borderColor: c.borde }]}>
      <FontAwesome5 name={c.icono} size={13} color={c.texto} />
      <Text style={{ color: c.texto, fontSize: 13, flex: 1 }}>{texto}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  scCaja: {
    flexBasis: "47%",
    flexGrow: 1,
  },
  sc: {
    borderRadius: 12,
    padding: 16,
    flexDirection: "row",
    alignItems: "flex-start",
    overflow: "hidden",
    minHeight: 110,
  },
  scVal: {
    fontSize: 26,
    fontWeight: "800",
    color: "#fff",
  },
  scLbl: {
    fontSize: 12,
    marginTop: 6,
    color: "rgba(255,255,255,.9)",
  },
  scMore: {
    fontSize: 11,
    marginTop: 10,
    color: "rgba(255,255,255,.85)",
  },
  scCirculo: {
    position: "absolute",
    right: -10,
    bottom: -10,
    width: 75,
    height: 75,
    borderRadius: 38,
    backgroundColor: "rgba(255,255,255,.08)",
  },
  tTitulo: {
    fontSize: 13.5,
    fontWeight: "600",
    color: Color.texto,
  },
  aviso: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    marginBottom: 14,
  },
});
