import { FontAwesome5 } from "@expo/vector-icons";
import { DrawerContentComponentProps, DrawerContentScrollView } from "expo-router/drawer";
import { LinearGradient } from "expo-linear-gradient";
import { Image, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Color, Degradado, NOMBRE_EMPRESA } from "@/constants/estilos";
import { empleadoActual, iniciales } from "@/lib/api";

type Icono = React.ComponentProps<typeof FontAwesome5>["name"];

// [ruta, icono, texto, solo admin] — las mismas secciones del sidebar del panel web
export const SECCIONES: Record<string, [string, Icono, string, boolean][]> = {
  Principal: [
    ["dashboard", "tachometer-alt", "Dashboard", false],
    ["notificaciones", "bell", "Notificaciones", false],
  ],
  Operatividad: [
    ["ventas", "dollar-sign", "Ventas", false],
    ["pedidos", "box", "Pedidos de clientes", false],
    ["pedidos-proveedores", "truck-loading", "Pedidos a proveedores", false],
    ["proveedores", "truck", "Proveedores", false],
    ["pagos", "money-check-alt", "Validación de pago", false],
    ["cotizaciones", "file-invoice-dollar", "Cotizaciones", false],
  ],
  Catálogos: [
    ["clientes", "users", "Clientes", false],
    ["prendas", "tshirt", "Prendas", false],
    ["productos", "box-open", "Productos", false],
    ["inventario", "warehouse", "Inventario", false],
    ["usuarios", "user-cog", "Usuarios", true],
  ],
  Sistema: [
    ["respaldo", "download", "Respaldar BD", false],
    ["restaurar", "upload", "Restaurar BD", true],
    ["reportes", "chart-bar", "Reportes de ventas", false],
    ["reporte-pdf", "file-pdf", "Reporte PDF", false],
    ["bitacora", "clipboard-list", "Bitácora de accesos", true],
  ],
};

export function BarraLateral({ state, navigation }: DrawerContentComponentProps) {
  const empleado = empleadoActual();
  const activa = state.routes[state.index]?.name;
  const esAdmin = empleado?.rol === "admin";

  return (
    <View style={styles.sb}>
      <View style={styles.head}>
        <LinearGradient colors={Degradado.logo} style={styles.logo}>
          <Image source={require("@/assets/images/logo.jpeg")} style={styles.logoImg} />
        </LinearGradient>
        <Text style={styles.logoT} numberOfLines={1}>
          {NOMBRE_EMPRESA}
        </Text>
      </View>

      <View style={styles.user}>
        <LinearGradient colors={Degradado.avatar} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.av}>
          <Text style={styles.avTexto}>{iniciales(empleado?.nombre ?? "Admin")}</Text>
        </LinearGradient>
        <View>
          <Text style={styles.un}>{empleado?.nombre ?? "Administrador"}</Text>
          <Text style={styles.ur}>{(empleado?.rol ?? "admin").replace(/^./, (c) => c.toUpperCase())}</Text>
        </View>
      </View>

      <DrawerContentScrollView contentContainerStyle={{ paddingTop: 0 }}>
        {Object.entries(SECCIONES).map(([titulo, items]) => (
          <View key={titulo}>
            <Text style={styles.lbl}>{titulo}</Text>
            {items
              .filter(([, , , soloAdmin]) => !soloAdmin || esAdmin)
              .map(([ruta, icono, texto]) => {
                const act = activa === ruta;
                return (
                  <TouchableOpacity key={ruta} style={[styles.ni, act && styles.niAct]} onPress={() => navigation.navigate(ruta)}>
                    <FontAwesome5 name={icono} size={13} color={act ? Color.primario : Color.textoSuave} style={styles.niIcono} />
                    <Text style={[styles.niTexto, act && { color: Color.primario }]}>{texto}</Text>
                  </TouchableOpacity>
                );
              })}
          </View>
        ))}
      </DrawerContentScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  sb: {
    flex: 1,
    backgroundColor: Color.menu,
  },
  head: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    paddingHorizontal: 16,
    paddingTop: 50,
    paddingBottom: 14,
    borderBottomWidth: 1,
    borderBottomColor: Color.borde,
  },
  logo: {
    width: 34,
    height: 34,
    borderRadius: 17,
    justifyContent: "center",
    alignItems: "center",
  },
  logoImg: {
    width: 34,
    height: 34,
    borderRadius: 17,
  },
  logoT: {
    flex: 1,
    fontWeight: "700",
    fontSize: 14,
    color: Color.texto,
  },
  user: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: Color.borde,
  },
  av: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: "center",
    alignItems: "center",
  },
  avTexto: {
    color: "#fff",
    fontSize: 12,
    fontWeight: "700",
  },
  un: {
    fontSize: 13,
    fontWeight: "600",
    color: Color.texto,
  },
  ur: {
    fontSize: 11,
    color: Color.textoSuave,
  },
  lbl: {
    fontSize: 10,
    fontWeight: "700",
    letterSpacing: 0.8,
    color: Color.textoSuave,
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 5,
    textTransform: "uppercase",
  },
  ni: {
    flexDirection: "row",
    alignItems: "center",
    paddingVertical: 11,
    paddingHorizontal: 16,
    borderRightWidth: 3,
    borderRightColor: "transparent",
  },
  niAct: {
    backgroundColor: "rgba(79,142,247,.15)",
    borderRightColor: Color.primario,
  },
  niIcono: {
    width: 17,
    textAlign: "center",
    marginRight: 11,
  },
  niTexto: {
    color: Color.textoSuave,
    fontSize: 13.5,
    fontWeight: "500",
  },
});
