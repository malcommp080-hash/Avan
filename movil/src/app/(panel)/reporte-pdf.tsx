import { useState } from "react";
import { ScrollView, Text, View } from "react-native";

import { Aviso, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { base } from "@/constants/estilos";
import { descargar } from "@/lib/api";

/** Reporte PDF de clientes (el mismo que genera el panel con FPDF). */
export default function ReportePdfScreen() {
  const [cargando, setCargando] = useState(false);
  const [error, setError] = useState("");

  const generar = async () => {
    setError("");
    setCargando(true);
    try {
      await descargar("/reporte-clientes", `reporte_clientes_${new Date().toISOString().slice(0, 10)}.pdf`, "application/pdf");
    } catch (e) {
      setError((e as Error).message);
    } finally {
      setCargando(false);
    }
  };

  return (
    <ScrollView contentContainerStyle={base.container}>
      <Encabezado titulo="Reporte PDF" sub="Reportes en PDF para imprimir o compartir" />
      {error ? <Aviso tipo="err" texto={error} /> : null}

      <View style={base.formCard}>
        <TituloTarjeta icono="file-pdf" texto="Reporte de clientes" color="#ef4444" />
        <Text style={[base.cardText, { marginBottom: 10 }]}>
          Lista de todos los clientes registrados con su teléfono, dirección y código postal.
        </Text>
        <Boton texto={cargando ? "Generando…" : "Generar PDF"} icono="file-pdf" tipo="danger" onPress={generar} disabled={cargando} />
      </View>
    </ScrollView>
  );
}
