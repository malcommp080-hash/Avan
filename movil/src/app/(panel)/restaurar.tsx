import * as DocumentPicker from "expo-document-picker";
import { useState } from "react";
import { Platform, ScrollView, Text, View } from "react-native";

import { Aviso, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { base, Color } from "@/constants/estilos";
import { subir } from "@/lib/api";
import { confirmar } from "@/lib/dialogos";

type Archivo = { uri: string; name: string; mimeType?: string; file?: File };

/** Restaurar BD desde un .sql generado con «Respaldar BD» (sólo administradores). */
export default function RestaurarScreen() {
  const [archivo, setArchivo] = useState<Archivo | null>(null);
  const [cargando, setCargando] = useState(false);
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const elegir = async () => {
    const r = await DocumentPicker.getDocumentAsync({ copyToCacheDirectory: true });
    if (r.canceled) return;
    const a = r.assets[0];
    if (!a.name.toLowerCase().endsWith(".sql")) {
      setMensaje({ tipo: "err", texto: "El archivo debe tener extensión .sql" });
      return;
    }
    setMensaje(null);
    setArchivo(a);
  };

  const restaurar = async () => {
    if (!archivo) return;
    const ok = await confirmar(
      "Restaurar base de datos",
      "Se reemplazarán las tablas con el contenido del respaldo. Esta acción no se puede deshacer. ¿Continuar?",
      "Restaurar",
      true
    );
    if (!ok) return;

    const datos = new FormData();
    // En web el selector entrega un File; en el celular, la uri del archivo
    if (Platform.OS === "web" && archivo.file) datos.append("archivo", archivo.file);
    else datos.append("archivo", { uri: archivo.uri, name: archivo.name, type: archivo.mimeType ?? "application/sql" } as any);

    setCargando(true);
    try {
      const r = await subir("/restaurar", datos);
      setMensaje({ tipo: "ok", texto: r.message });
      setArchivo(null);
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    } finally {
      setCargando(false);
    }
  };

  return (
    <ScrollView contentContainerStyle={base.container}>
      <Encabezado titulo="Restaurar BD" sub="Carga un respaldo .sql para recuperar la información" />
      {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}
      <Aviso tipo="warn" texto="Restaurar reemplaza los datos actuales. Haz un respaldo antes por si necesitas regresar." />

      <View style={base.formCard}>
        <TituloTarjeta icono="upload" texto="Archivo de respaldo" color={Color.aviso} />
        <Boton texto={archivo ? "Elegir otro archivo" : "Elegir archivo .sql"} icono="file-upload" tipo="outline" onPress={elegir} />
        {archivo ? <Text style={[base.cardText, { marginTop: 10 }]}>Seleccionado: {archivo.name}</Text> : null}
        <Boton
          texto={cargando ? "Restaurando…" : "Restaurar base de datos"}
          icono="upload"
          tipo="orange"
          onPress={restaurar}
          disabled={!archivo || cargando}
          style={{ marginTop: 14 }}
        />
      </View>
    </ScrollView>
  );
}
