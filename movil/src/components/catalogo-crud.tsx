import { FontAwesome5 } from "@expo/vector-icons";
import React, { useState } from "react";
import { ActivityIndicator, KeyboardTypeOptions, RefreshControl, ScrollView, StyleSheet, Text, TouchableOpacity, View } from "react-native";

import { Chips } from "@/components/chips";
import { Entrada } from "@/components/entrada";
import { Aviso, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api } from "@/lib/api";
import { confirmar } from "@/lib/dialogos";

export type Campo = {
  key: string;
  label: string;
  placeholder: string;
  keyboardType?: KeyboardTypeOptions;
  secure?: boolean;
  // Si tiene opciones se muestra como botones en lugar de texto
  opciones?: Record<string, string>;
  // Campo que sólo es obligatorio al crear (p. ej. contraseña)
  soloAlCrear?: boolean;
};

type Registro = { id: number } & Record<string, any>;

type Props = {
  titulo: string;        // "Clientes"
  singular: string;      // "cliente"
  icono: React.ComponentProps<typeof FontAwesome5>["name"];
  ruta: string;          // "/clientes"
  claveLista: string;    // nombre del arreglo en la respuesta: "clientes"
  campos: Campo[];
  renglon: (r: Registro) => { titulo: string; detalle: string };
};

/** Pantalla de catálogo: formulario arriba (alta / edición) y la lista abajo, como en el panel web. */
export function CatalogoCrud({ titulo, singular, icono, ruta, claveLista, campos, renglon }: Props) {
  const { datos, cargando, recargar } = useApi<Record<string, Registro[]>>(ruta);
  const [form, setForm] = useState<Record<string, string>>({});
  const [editando, setEditando] = useState<number | null>(null);
  const [guardando, setGuardando] = useState(false);
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const lista = datos?.[claveLista] ?? [];

  const actualizar = (key: string, valor: string) => setForm({ ...form, [key]: valor });

  const limpiar = () => {
    setForm({});
    setEditando(null);
  };

  const editar = (r: Registro) => {
    const valores: Record<string, string> = {};
    campos.forEach((c) => (valores[c.key] = c.soloAlCrear ? "" : String(r[c.key] ?? "")));
    setForm(valores);
    setEditando(r.id);
    setMensaje(null);
  };

  const handleGuardar = async () => {
    const faltan = campos.some((c) => !(c.soloAlCrear && editando) && !form[c.key]?.trim());
    if (faltan) {
      setMensaje({ tipo: "err", texto: "Complete todos los campos antes de guardar." });
      return;
    }
    try {
      setGuardando(true);
      const r = editando ? await api(`${ruta}/${editando}`, "PUT", form) : await api(ruta, "POST", form);
      setMensaje({ tipo: "ok", texto: r.message });
      limpiar();
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    } finally {
      setGuardando(false);
    }
  };

  const handleEliminar = async (r: Registro) => {
    if (!(await confirmar("Eliminar", `¿Eliminar a ${renglon(r).titulo}?`, "Eliminar", true))) return;
    try {
      const res = await api(`${ruta}/${r.id}`, "DELETE");
      setMensaje({ tipo: "ok", texto: res.message });
      if (editando === r.id) limpiar();
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
  };

  return (
    <ScrollView
      contentContainerStyle={base.container}
      keyboardShouldPersistTaps="handled"
      refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}>
      <Encabezado titulo={titulo} sub={`Registro y administración de ${titulo.toLowerCase()}`} />

      {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}

      <View style={base.formCard}>
        <TituloTarjeta
          icono={editando ? "edit" : "plus-circle"}
          texto={editando ? `Editar ${singular}` : `Nuevo ${singular}`}
          color={editando ? Color.amarillo : Color.primario}
        />
        {campos.map((campo) => (
          <React.Fragment key={campo.key}>
            <Text style={base.label}>
              {campo.label}
              {campo.soloAlCrear && editando ? " (déjala vacía para no cambiarla)" : ""}
            </Text>
            {campo.opciones ? (
              <Chips opciones={campo.opciones} valor={form[campo.key] ?? ""} onChange={(v) => actualizar(campo.key, v)} />
            ) : (
              <Entrada
                placeholder={campo.placeholder}
                keyboardType={campo.keyboardType}
                secureTextEntry={campo.secure}
                autoCapitalize={campo.secure ? "none" : "sentences"}
                value={form[campo.key] ?? ""}
                onChangeText={(v) => actualizar(campo.key, v)}
              />
            )}
          </React.Fragment>
        ))}

        <View style={base.row}>
          <Boton
            texto={guardando ? "Guardando…" : editando ? "Actualizar" : "Guardar"}
            icono="save"
            tipo={editando ? "orange" : "primary"}
            onPress={handleGuardar}
            disabled={guardando}
            style={{ flex: 1 }}
          />
          {editando ? <Boton texto="Cancelar" tipo="outline" onPress={limpiar} style={{ marginTop: 6, height: 44 }} /> : null}
        </View>
      </View>

      <View style={styles.tcard}>
        <View style={styles.hdr}>
          <TituloTarjeta icono={icono} texto={`${titulo} registrados (${lista.length})`} />
        </View>
        {cargando && !datos ? <ActivityIndicator color={Color.primario} style={{ margin: 16 }} /> : null}
        {datos && lista.length === 0 ? <Text style={styles.vacio}>No hay registros todavía.</Text> : null}

        {lista.map((r, i) => {
          const { titulo: t, detalle } = renglon(r);
          return (
            <View key={r.id} style={[styles.tr, i === lista.length - 1 && { borderBottomWidth: 0 }, editando === r.id && styles.editando]}>
              <View style={{ flex: 1 }}>
                <Text style={base.cardTitle}>
                  <Text style={{ color: Color.textoSuave }}>#{r.id} </Text>
                  {t}
                </Text>
                <Text style={base.cardText}>{detalle}</Text>
              </View>
              <TouchableOpacity style={styles.ib} onPress={() => editar(r)}>
                <FontAwesome5 name="edit" size={15} color={Color.amarillo} />
              </TouchableOpacity>
              <TouchableOpacity style={styles.ib} onPress={() => handleEliminar(r)}>
                <FontAwesome5 name="trash" size={15} color={Color.peligro} />
              </TouchableOpacity>
            </View>
          );
        })}
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  tcard: {
    backgroundColor: Color.tarjeta,
    borderWidth: 1,
    borderColor: Color.borde,
    borderRadius: 12,
    overflow: "hidden",
    marginBottom: 20,
  },
  hdr: {
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 2,
    borderBottomWidth: 1,
    borderBottomColor: Color.borde,
  },
  tr: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: Color.borde,
    gap: 4,
  },
  editando: {
    backgroundColor: "rgba(79,142,247,.08)",
  },
  ib: {
    padding: 8,
    borderRadius: 5,
  },
  vacio: {
    textAlign: "center",
    color: Color.textoSuave,
    padding: 16,
  },
});
