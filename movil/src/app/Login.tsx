import { FontAwesome5 } from "@expo/vector-icons";
import { LinearGradient } from "expo-linear-gradient";
import { useRouter } from "expo-router";
import { useState } from "react";
import { ActivityIndicator, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Entrada } from "@/components/entrada";
import { Aviso } from "@/components/panel";
import { Color, Degradado, NOMBRE_EMPRESA } from "@/constants/estilos";
import { api, Empleado, guardarSesion } from "@/lib/api";

export default function Login() {
  const router = useRouter();
  const [Telefono, setTelefono] = useState("");
  const [Contrasena, setContrasena] = useState("");
  const [Cargando, setCargando] = useState(false);
  const [error, setError] = useState("");
  const [ok, setOk] = useState("");

  const iniciarSesion = async () => {
    setError("");
    setOk("");
    if (!Telefono || !Contrasena) {
      setError("Escribe tu teléfono y tu contraseña.");
      return;
    }

    try {
      setCargando(true);
      const data = await api<{ token: string; empleado: Empleado }>("/login", "POST", {
        telefono: Telefono,
        contrasena: Contrasena,
      });
      await guardarSesion(data.token, data.empleado);
      setContrasena("");
      router.replace("/dashboard");
    } catch (e) {
      setError((e as Error).message);
    } finally {
      setCargando(false);
    }
  };

  // RF001.4: igual que en el panel, se avisa a los administradores para que la restablezcan
  const recuperar = async () => {
    setError("");
    setOk("");
    if (!Telefono) {
      setError("Escribe tu número de teléfono y vuelve a tocar «¿Olvidó su contraseña?».");
      return;
    }
    try {
      const r = await api("/recuperar", "POST", { telefono: Telefono });
      setOk(r.message);
    } catch (e) {
      setError((e as Error).message);
    }
  };

  return (
    <KeyboardAvoidingView style={styles.body} behavior={Platform.OS === "ios" ? "padding" : undefined}>
      {/* Círculos decorativos del fondo */}
      <View style={[styles.circulo, styles.circuloAzul]} />
      <View style={[styles.circulo, styles.circuloVerde]} />

      <ScrollView contentContainerStyle={styles.centro} keyboardShouldPersistTaps="handled">
        <View style={styles.box}>
          <View style={styles.logoArea}>
            <LinearGradient colors={Degradado.logo} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.logoIcon}>
              <FontAwesome5 name="shield-alt" size={19} color="#fff" />
            </LinearGradient>
            <Text style={styles.logoText}>
              {NOMBRE_EMPRESA} <Text style={{ color: Color.primario }}>Admin</Text>
            </Text>
          </View>

          <Text style={styles.h1}>Bienvenido</Text>
          <Text style={styles.sub}>Acceso al panel de administración</Text>

          {error ? <Aviso tipo="err" texto={error} /> : null}
          {ok ? <Aviso tipo="ok" texto={ok} /> : null}

          <Text style={styles.label}>Número de teléfono</Text>
          <Entrada
            icono="user"
            placeholder="Teléfono o 'admin'"
            autoCapitalize="none"
            value={Telefono}
            onChangeText={setTelefono}
            style={styles.input}
          />

          <Text style={styles.label}>Contraseña</Text>
          <Entrada
            icono="lock"
            placeholder="••••••••"
            secureTextEntry
            autoCapitalize="none"
            value={Contrasena}
            onChangeText={setContrasena}
            onSubmitEditing={iniciarSesion}
            style={styles.input}
          />

          <TouchableOpacity onPress={recuperar} style={{ alignSelf: "flex-end", marginBottom: 14 }}>
            <Text style={styles.link}>¿Olvidó su contraseña?</Text>
          </TouchableOpacity>

          <TouchableOpacity onPress={iniciarSesion} disabled={Cargando} activeOpacity={0.85}>
            <LinearGradient colors={Degradado.azul} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.btnLogin}>
              {Cargando ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <>
                  <FontAwesome5 name="sign-in-alt" size={15} color="#fff" />
                  <Text style={styles.btnTexto}>Iniciar sesión</Text>
                </>
              )}
            </LinearGradient>
          </TouchableOpacity>

          <Text style={styles.footer}>{NOMBRE_EMPRESA} · Panel administrativo</Text>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  body: {
    flex: 1,
    backgroundColor: Color.fondo,
    overflow: "hidden",
  },
  circulo: {
    position: "absolute",
    borderRadius: 999,
  },
  circuloAzul: {
    width: 420,
    height: 420,
    top: -120,
    right: -140,
    backgroundColor: "rgba(79,142,247,0.10)",
  },
  circuloVerde: {
    width: 300,
    height: 300,
    bottom: -90,
    left: -110,
    backgroundColor: "rgba(34,211,165,0.07)",
  },
  centro: {
    flexGrow: 1,
    justifyContent: "center",
    padding: 16,
  },
  box: {
    backgroundColor: Color.tarjeta,
    borderColor: Color.borde,
    borderWidth: 1,
    borderRadius: 20,
    paddingVertical: 36,
    paddingHorizontal: 24,
    width: "100%",
    maxWidth: 420,
    alignSelf: "center",
    shadowColor: "#000",
    shadowOpacity: 0.5,
    shadowRadius: 30,
    shadowOffset: { width: 0, height: 20 },
    elevation: 12,
  },
  logoArea: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    marginBottom: 30,
  },
  logoIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: "center",
    alignItems: "center",
  },
  logoText: {
    flex: 1,
    fontWeight: "800",
    fontSize: 17,
    color: Color.texto,
  },
  h1: {
    fontSize: 26,
    fontWeight: "800",
    color: Color.texto,
    marginBottom: 6,
  },
  sub: {
    color: Color.textoSuave,
    fontSize: 14,
    marginBottom: 26,
  },
  label: {
    fontSize: 11,
    fontWeight: "600",
    color: Color.textoSuave,
    textTransform: "uppercase",
    letterSpacing: 1,
    marginBottom: 8,
  },
  input: {
    backgroundColor: "rgba(255,255,255,0.04)",
    marginBottom: 18,
  },
  link: {
    color: Color.primario,
    fontSize: 13,
  },
  btnLogin: {
    height: 50,
    borderRadius: 10,
    flexDirection: "row",
    justifyContent: "center",
    alignItems: "center",
    gap: 8,
  },
  btnTexto: {
    color: "#fff",
    fontSize: 16,
    fontWeight: "700",
    letterSpacing: 0.5,
  },
  footer: {
    textAlign: "center",
    marginTop: 26,
    fontSize: 12,
    color: Color.textoSuave,
  },
});
