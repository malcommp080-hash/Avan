import { Stack } from "expo-router";
import { StatusBar } from "expo-status-bar";
import { useEffect, useState } from "react";
import { ActivityIndicator, View } from "react-native";
import { GestureHandlerRootView } from "react-native-gesture-handler";

import { Color } from "@/constants/estilos";
import { cargarSesion } from "@/lib/api";

export default function RootLayout() {
  // Se lee el token guardado antes de mostrar cualquier pantalla (también al recargar en web)
  const [listo, setListo] = useState(false);
  useEffect(() => {
    cargarSesion().finally(() => setListo(true));
  }, []);

  if (!listo) {
    return (
      <View style={{ flex: 1, justifyContent: "center", backgroundColor: Color.fondo }}>
        <ActivityIndicator size="large" color={Color.primario} />
      </View>
    );
  }

  return (
    <GestureHandlerRootView style={{ flex: 1, backgroundColor: Color.fondo }}>
      <StatusBar style="light" />
      <Stack
        screenOptions={{
          headerStyle: { backgroundColor: Color.menu },
          headerTintColor: Color.texto,
          headerTitleStyle: { fontSize: 15, fontWeight: "600" },
          headerBackTitle: "Atrás",
          contentStyle: { backgroundColor: Color.fondo },
        }}>
        <Stack.Screen name="index" options={{ headerShown: false }} />
        <Stack.Screen name="Login" options={{ headerShown: false }} />
        <Stack.Screen name="(panel)" options={{ headerShown: false }} />
        <Stack.Screen name="nueva-venta" options={{ title: "Registrar venta" }} />
        <Stack.Screen name="venta/[id]" options={{ title: "Resumen de venta" }} />
        <Stack.Screen name="cotizacion/[id]" options={{ title: "Cotización" }} />
      </Stack>
    </GestureHandlerRootView>
  );
}
