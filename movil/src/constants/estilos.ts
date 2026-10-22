import { StyleSheet } from "react-native";

export const NOMBRE_EMPRESA = "DISTRIBUIDORA DAVID";

// Misma paleta que el panel web (public/css/style.css)
export const Color = {
  fondo: "#0f1117",       // --bg
  menu: "#1a1d27",        // --sb (barra lateral y superior)
  tarjeta: "#1e2130",     // --card
  borde: "#2a2f45",       // --border
  input: "#11141e",
  placeholder: "#444d6b",
  texto: "#e4e8f0",       // --text
  textoSuave: "#7a8099",  // --muted
  primario: "#4f8ef7",    // --accent
  turquesa: "#22d3a5",    // --accent2
  amarillo: "#f7c948",    // --accent3
  exito: "#22c55e",       // --green
  aviso: "#f97316",       // --orange
  peligro: "#ef4444",     // --red
  morado: "#a855f7",      // --purple
};

// Fondos de las tarjetas de estadísticas (.sc-teal, .sc-green, .sc-amber, .sc-red)
export const Degradado = {
  teal: ["#0d9488", "#14b8a6"],
  green: ["#16a34a", "#22c55e"],
  amber: ["#d97706", "#f59e0b"],
  red: ["#dc2626", "#ef4444"],
  azul: ["#4f8ef7", "#2563eb"],
  logo: ["#4f8ef7", "#22d3a5"],
  avatar: ["#a855f7", "#4f8ef7"],
} as const;

/** Badges del panel (.bg-green, .bg-orange, .bg-blue, .bg-red). */
export const Badge = {
  green: { backgroundColor: "rgba(34,197,94,.15)", color: "#22c55e" },
  orange: { backgroundColor: "rgba(249,115,22,.15)", color: "#f97316" },
  blue: { backgroundColor: "rgba(79,142,247,.15)", color: "#4f8ef7" },
  red: { backgroundColor: "rgba(239,68,68,.15)", color: "#ef4444" },
};

/** Estilos que comparten todas las pantallas. */
export const base = StyleSheet.create({
  container: {
    flexGrow: 1,
    padding: 16,
    backgroundColor: Color.fondo,
  },
  title: {
    fontSize: 21,
    fontWeight: "bold",
    marginBottom: 4,
    color: Color.texto,
  },
  subtitle: {
    fontSize: 12.5,
    color: Color.textoSuave,
    marginBottom: 18,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: "600",
    color: Color.texto,
    marginTop: 8,
    marginBottom: 10,
  },
  label: {
    fontSize: 12,
    fontWeight: "500",
    color: Color.textoSuave,
    marginBottom: 5,
  },
  input: {
    width: "100%",
    height: 46,
    borderColor: Color.borde,
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 12,
    marginBottom: 12,
    backgroundColor: Color.input,
    color: Color.texto,
    fontSize: 14,
  },
  // .fcard: tarjeta que agrupa un formulario
  formCard: {
    backgroundColor: Color.tarjeta,
    borderColor: Color.borde,
    borderWidth: 1,
    borderRadius: 12,
    padding: 16,
    marginBottom: 18,
  },
  card: {
    backgroundColor: Color.tarjeta,
    borderRadius: 12,
    borderColor: Color.borde,
    borderWidth: 1,
    padding: 14,
    marginBottom: 10,
  },
  cardTitle: {
    fontSize: 14,
    fontWeight: "600",
    color: Color.texto,
  },
  cardText: {
    fontSize: 13,
    color: Color.textoSuave,
    marginTop: 2,
  },
  row: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
  },
  button: {
    height: 44,
    backgroundColor: Color.primario,
    borderRadius: 8,
    justifyContent: "center",
    alignItems: "center",
    flexDirection: "row",
    gap: 7,
    marginTop: 6,
    paddingHorizontal: 16,
  },
  cancelButton: {
    height: 44,
    backgroundColor: Color.peligro,
    borderRadius: 8,
    justifyContent: "center",
    alignItems: "center",
    flexDirection: "row",
    gap: 7,
    marginTop: 10,
    paddingHorizontal: 16,
  },
  buttonText: {
    color: "#fff",
    fontSize: 14,
    fontWeight: "600",
  },
  // .btn-outline
  outlineButton: {
    height: 40,
    borderColor: Color.borde,
    borderWidth: 1,
    borderRadius: 8,
    justifyContent: "center",
    alignItems: "center",
    flexDirection: "row",
    gap: 6,
    paddingHorizontal: 12,
  },
  outlineButtonText: {
    color: Color.texto,
    fontSize: 13,
    fontWeight: "600",
  },
  badge: {
    alignSelf: "flex-start",
    paddingHorizontal: 9,
    paddingVertical: 3,
    borderRadius: 999,
    fontSize: 11,
    fontWeight: "600",
    overflow: "hidden",
  },
  vacio: {
    textAlign: "center",
    color: Color.textoSuave,
    marginTop: 30,
  },
});
