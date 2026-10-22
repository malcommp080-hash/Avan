import { FontAwesome5 } from "@expo/vector-icons";
import { Redirect, useRouter } from "expo-router";
import { Drawer } from "expo-router/drawer";
import { Alert, Platform, Text, TouchableOpacity, View } from "react-native";

import { BarraLateral, SECCIONES } from "@/components/barra-lateral";
import { Color } from "@/constants/estilos";
import { api, borrarSesion, empleadoActual } from "@/lib/api";

// Título de cada pantalla, tomado del menú lateral
const TITULOS = Object.fromEntries(Object.values(SECCIONES).flat().map(([ruta, , texto]) => [ruta, texto]));

/** Barra superior del panel: notificaciones y salir (.tb-right). */
function AccionesBarra() {
  const router = useRouter();

  const salir = async () => {
    await api("/logout", "POST").catch(() => {});
    await borrarSesion();
    router.replace("/Login");
  };

  const confirmarSalida = () => {
    // Alert con botones no existe en web
    if (Platform.OS === "web") {
      if (window.confirm("¿Seguro que quieres cerrar sesión?")) salir();
      return;
    }
    Alert.alert("Cerrar sesión", "¿Seguro que quieres salir?", [
      { text: "Cancelar", style: "cancel" },
      { text: "Salir", style: "destructive", onPress: salir },
    ]);
  };

  return (
    <View style={{ flexDirection: "row", gap: 6, marginRight: 12 }}>
      <TouchableOpacity
        onPress={() => router.navigate("/notificaciones")}
        style={{ borderWidth: 1, borderColor: Color.borde, borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6 }}>
        <FontAwesome5 name="bell" size={13} color={Color.texto} />
      </TouchableOpacity>
      <TouchableOpacity
        onPress={confirmarSalida}
        style={{ flexDirection: "row", alignItems: "center", gap: 5, borderWidth: 1, borderColor: Color.borde, borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6 }}>
        <FontAwesome5 name="sign-out-alt" size={13} color={Color.texto} />
        <Text style={{ color: Color.texto, fontSize: 12, fontWeight: "600" }}>Salir</Text>
      </TouchableOpacity>
    </View>
  );
}

export default function PanelLayout() {
  // La sesión guardada ya se leyó en el layout raíz
  if (!empleadoActual()) return <Redirect href="/Login" />;

  return (
    <Drawer
      drawerContent={(props) => <BarraLateral {...props} />}
      screenOptions={({ route }) => ({
        title: TITULOS[route.name] ?? route.name,
        headerStyle: { backgroundColor: Color.menu, borderBottomWidth: 1, borderBottomColor: Color.borde },
        headerTintColor: Color.texto,
        headerTitleStyle: { fontSize: 15, fontWeight: "600" },
        headerRight: () => <AccionesBarra />,
        drawerStyle: { backgroundColor: Color.menu, width: 265, borderRightWidth: 1, borderRightColor: Color.borde },
        sceneStyle: { backgroundColor: Color.fondo },
      })}
    />
  );
}
