import { useState } from "react";
import { ScrollView, Text, View } from "react-native";

import { Aviso, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { base, Color } from "@/constants/estilos";
import { descargar } from "@/lib/api";

/** Respaldar BD: descarga un .sql con la estructura y los datos de todas las tablas. */
export default function RespaldoScreen() {
  const [cargando, setCargando] = useState(false);
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const respaldar = async () => {
    setMensaje(null);
    setCargando(true);
    const fecha = new Date().toISOString().slice(0, 19).replace(/[T:]/g, "-");
    try {
      await descargar("/respaldo", `respaldo_${fecha}.sql`, "application/sql");
      setMensaje({ tipo: "ok", texto: "Respaldo generado. Guárdalo en un lugar seguro." });
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    } finally {
      setCargando(false);
    }
  };

  return (
    <ScrollView contentContainerStyle={base.container}>
      <Encabezado titulo="Respaldar BD" sub="Genera una copia completa de la base de datos" />
      {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}

      <View style={base.formCard}>
        <TituloTarjeta icono="database" texto="Respaldo de la base de datos" />
        <Text style={[base.cardText, { marginBottom: 6 }]}>
          Se descarga un archivo .sql con todas las tablas (estructura y datos). Con él puedes restaurar la información desde
          «Restaurar BD».
        </Text>
        <Text style={[base.cardText, { color: Color.amarillo, marginBottom: 10 }]}>
          En el celular se abrirá el menú para compartir: elige dónde guardarlo (Drive, correo, archivos…).
        </Text>
        <Boton texto={cargando ? "Generando…" : "Descargar respaldo"} icono="download" tipo="success" onPress={respaldar} disabled={cargando} />
      </View>
    </ScrollView>
  );
}
