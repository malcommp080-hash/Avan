import { useState } from "react";
import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from "react-native";

import { Barras } from "@/components/barras";
import { Chips } from "@/components/chips";
import { Entrada } from "@/components/entrada";
import { Aviso, Boton, Encabezado, Estadistica, TituloTarjeta } from "@/components/panel";
import { Tabla } from "@/components/tabla";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { dinero } from "@/lib/api";

type Grupo = { etiqueta: string; total: number; ventas: number; piezas: number };
type Reporte = {
  desde: string; hasta: string; semanas: Grupo[]; meses: Grupo[];
  total: number; cuadra: boolean; numVentas: number; piezas: number; promedio: number; enLinea: number;
  top: { Nombre: string; piezas: number; importe: number }[];
};

const VISTAS = { meses: "Por mes", semanas: "Por semana" };

/** RF007: ventas agrupadas por semana y por mes. */
export default function ReportesScreen() {
  const [desde, setDesde] = useState("");
  const [hasta, setHasta] = useState("");
  const [filtro, setFiltro] = useState("");
  const [error, setError] = useState("");
  const [vista, setVista] = useState<keyof typeof VISTAS>("meses");
  const { datos: r, cargando } = useApi<Reporte>(`/reportes${filtro}`);

  const aplicar = () => {
    const formato = /^\d{4}-\d{2}-\d{2}$/;
    if ((desde && !formato.test(desde)) || (hasta && !formato.test(hasta))) {
      setError("Escribe las fechas como AAAA-MM-DD.");
      return;
    }
    setError("");
    const params = [desde && `desde=${desde}`, hasta && `hasta=${hasta}`].filter(Boolean).join("&");
    setFiltro(params ? `?${params}` : "");
  };

  const grupos = r ? r[vista] : [];

  return (
    <ScrollView contentContainerStyle={base.container} keyboardShouldPersistTaps="handled">
      <Encabezado titulo="Reportes de ventas" sub="Ventas agrupadas por semana y por mes en un rango de fechas" />
      {error ? <Aviso tipo="err" texto={error} /> : null}

      <View style={base.formCard}>
        <TituloTarjeta icono="filter" texto="Rango de fechas" />
        <View style={base.row}>
          <View style={{ flex: 1 }}>
            <Text style={base.label}>Desde</Text>
            <Entrada placeholder={r?.desde ?? "AAAA-MM-DD"} value={desde} onChangeText={setDesde} />
          </View>
          <View style={{ flex: 1 }}>
            <Text style={base.label}>Hasta</Text>
            <Entrada placeholder={r?.hasta ?? "AAAA-MM-DD"} value={hasta} onChangeText={setHasta} />
          </View>
        </View>
        <Boton texto="Consultar" icono="search" onPress={aplicar} />
      </View>

      {cargando && !r ? <ActivityIndicator color={Color.primario} /> : null}

      {r ? (
        <>
          <View style={styles.grid}>
            <Estadistica color="green" valor={dinero(r.total)} etiqueta="Total vendido" icono="dollar-sign" />
            <Estadistica color="teal" valor={String(r.numVentas)} etiqueta="Ventas" icono="receipt" />
            <Estadistica color="amber" valor={String(r.piezas)} etiqueta="Piezas" icono="tshirt" />
            <Estadistica color="red" valor={dinero(r.promedio)} etiqueta="Ticket promedio" icono="chart-line" />
          </View>
          <Aviso
            tipo={r.cuadra ? "ok" : "warn"}
            texto={
              (r.cuadra ? "Los totales coinciden con las ventas registradas." : "Los totales no coinciden; revisa las ventas.") +
              ` En línea: ${dinero(r.enLinea)}.`
            }
          />

          <View style={base.card}>
            <View style={[base.row, { justifyContent: "space-between" }]}>
              <TituloTarjeta icono="chart-bar" texto="Ventas" />
              <View style={{ marginTop: -2 }}>
                <Chips opciones={VISTAS} valor={vista} onChange={setVista} />
              </View>
            </View>
            {vista === "meses" ? (
              <Barras datos={grupos.map((g) => ({ etiqueta: g.etiqueta, valor: g.total }))} formato={dinero} />
            ) : null}
          </View>

          <Tabla
            icono="table"
            titulo={vista === "meses" ? "Detalle por mes" : "Detalle por semana"}
            columnas={[vista === "meses" ? "Mes" : "Semana", "Ventas", "Total"]}
            filas={grupos.map((g) => [g.etiqueta, `${g.ventas} · ${g.piezas} pzs`, dinero(g.total)])}
            vacio="Sin datos en el rango."
          />

          <Tabla
            icono="trophy"
            colorIcono={Color.amarillo}
            titulo="Productos más vendidos"
            columnas={["#", "Producto", "Importe"]}
            filas={r.top.map((t, i) => [String(i + 1), `${t.Nombre} (${t.piezas} pzs)`, dinero(t.importe)])}
            vacio="Sin ventas en el rango."
          />
        </>
      ) : null}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  grid: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 12,
    marginBottom: 16,
  },
});
