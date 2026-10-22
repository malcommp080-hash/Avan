import { FontAwesome5 } from "@expo/vector-icons";
import { Image } from "expo-image";
import * as ImagePicker from "expo-image-picker";
import { useState } from "react";
import { ActivityIndicator, Platform, RefreshControl, ScrollView, StyleSheet, Switch, Text, TouchableOpacity, View } from "react-native";

import { Chips } from "@/components/chips";
import { Entrada } from "@/components/entrada";
import { Aviso, Badge, Boton, Encabezado, TituloTarjeta } from "@/components/panel";
import { Selector } from "@/components/selector";
import { urlServidor } from "@/constants/api";
import { base, Color } from "@/constants/estilos";
import { useApi } from "@/hooks/use-api";
import { api, dinero, subir } from "@/lib/api";
import { confirmar } from "@/lib/dialogos";

type Talla = { talla: string; stock: string };
type Producto = {
  id: number; nombre: string; id_categoria: number; categoria: string | null; segmento: string; segmento_texto: string;
  color: string | null; descripcion: string | null; preciopro: number; precioneto: number;
  precio_mayoreo: number | null; min_mayoreo: number | null; activo: boolean; stock: number; imagen: string | null;
  tallas: { talla: string; stock: number }[];
};
type Datos = {
  totalProductos: number; totalStock: number; stockBajo: number;
  categorias: { id: number; nombre: string }[]; segmentos: Record<string, string>; sugeridas: string[];
  productos: Producto[];
};
type Foto = { uri: string; name: string; type: string; file?: File };

const formVacio = {
  nombre: "", id_categoria: null as number | null, segmento: "UNISEX", color: "", descripcion: "",
  preciopro: "", precioneto: "", precio_mayoreo: "", min_mayoreo: "5", activo: true,
};

/** Modelos del catálogo: datos, imagen, precio de mayoreo y stock por talla. */
export default function ProductosScreen() {
  const { datos, cargando, recargar } = useApi<Datos>("/productos");
  const [form, setForm] = useState(formVacio);
  const [tallas, setTallas] = useState<Talla[]>([{ talla: "M", stock: "0" }]);
  const [foto, setFoto] = useState<Foto | null>(null);
  const [fotoActual, setFotoActual] = useState<string | null>(null);
  const [quitarFoto, setQuitarFoto] = useState(false);
  const [editando, setEditando] = useState<number | null>(null);
  const [mostrarForm, setMostrarForm] = useState(false);
  const [guardando, setGuardando] = useState(false);
  const [buscar, setBuscar] = useState("");
  const [mensaje, setMensaje] = useState<{ tipo: "ok" | "err"; texto: string } | null>(null);

  const cambiar = (cambios: Partial<typeof formVacio>) => setForm({ ...form, ...cambios });

  const limpiar = () => {
    setForm(formVacio);
    setTallas([{ talla: "M", stock: "0" }]);
    setFoto(null);
    setFotoActual(null);
    setQuitarFoto(false);
    setEditando(null);
    setMostrarForm(false);
  };

  const editar = (p: Producto) => {
    setForm({
      nombre: p.nombre, id_categoria: p.id_categoria, segmento: p.segmento, color: p.color ?? "", descripcion: p.descripcion ?? "",
      preciopro: String(p.preciopro), precioneto: String(p.precioneto),
      precio_mayoreo: p.precio_mayoreo ? String(p.precio_mayoreo) : "", min_mayoreo: String(p.min_mayoreo ?? 5), activo: p.activo,
    });
    setTallas(p.tallas.map((t) => ({ talla: t.talla, stock: String(t.stock) })));
    setFoto(null);
    setFotoActual(p.imagen);
    setQuitarFoto(false);
    setEditando(p.id);
    setMostrarForm(true);
    setMensaje(null);
  };

  const elegirFoto = async () => {
    const r = await ImagePicker.launchImageLibraryAsync({ mediaTypes: ["images"], quality: 0.8, allowsEditing: true, aspect: [1, 1] });
    if (r.canceled) return;
    const a = r.assets[0];
    setFoto({ uri: a.uri, name: a.fileName ?? "producto.jpg", type: a.mimeType ?? "image/jpeg", file: a.file });
    setQuitarFoto(false);
  };

  const guardar = async () => {
    const d = new FormData();
    d.append("nombre", form.nombre);
    d.append("id_categoria", form.id_categoria ? String(form.id_categoria) : "");
    d.append("segmento", form.segmento);
    d.append("color", form.color);
    d.append("descripcion", form.descripcion);
    d.append("preciopro", form.preciopro);
    d.append("precioneto", form.precioneto);
    d.append("precio_mayoreo", form.precio_mayoreo);
    d.append("min_mayoreo", form.min_mayoreo);
    d.append("activo", form.activo ? "1" : "0");
    tallas.forEach((t, i) => {
      d.append(`tallas[${i}][talla]`, t.talla);
      d.append(`tallas[${i}][stock]`, t.stock);
    });
    if (foto) {
      // En web el selector entrega un File; en el celular, la uri de la imagen
      if (Platform.OS === "web" && foto.file) d.append("imagen", foto.file);
      else d.append("imagen", { uri: foto.uri, name: foto.name, type: foto.type } as any);
    }
    if (quitarFoto) d.append("quitar_imagen", "1");

    setGuardando(true);
    try {
      const r = await subir(editando ? `/productos/${editando}` : "/productos", d);
      setMensaje({ tipo: "ok", texto: r.message });
      limpiar();
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    } finally {
      setGuardando(false);
    }
  };

  const eliminar = async (p: Producto) => {
    if (!(await confirmar("Eliminar modelo", `¿Eliminar «${p.nombre}» del catálogo?`, "Eliminar", true))) return;
    try {
      const r = await api(`/productos/${p.id}`, "DELETE");
      setMensaje({ tipo: "ok", texto: r.message });
      if (editando === p.id) limpiar();
      recargar();
    } catch (e) {
      setMensaje({ tipo: "err", texto: (e as Error).message });
    }
  };

  const productos = (datos?.productos ?? []).filter((p) => p.nombre.toLowerCase().includes(buscar.toLowerCase()));
  const vistaFoto = foto?.uri ?? (fotoActual && !quitarFoto ? urlServidor(fotoActual) : null);

  return (
    <ScrollView
      contentContainerStyle={base.container}
      keyboardShouldPersistTaps="handled"
      refreshControl={<RefreshControl refreshing={cargando && datos !== null} onRefresh={recargar} tintColor={Color.primario} />}>
      <Encabezado
        titulo="Productos"
        sub={datos ? `${datos.totalProductos} modelos · ${datos.totalStock} piezas · ${datos.stockBajo} con stock bajo` : "Modelos del catálogo"}
      />
      {mensaje ? <Aviso tipo={mensaje.tipo} texto={mensaje.texto} /> : null}

      {!mostrarForm ? (
        <Boton texto="Nuevo modelo" icono="plus" onPress={() => setMostrarForm(true)} style={{ marginBottom: 14 }} />
      ) : datos ? (
        <View style={base.formCard}>
          <TituloTarjeta
            icono={editando ? "edit" : "plus-circle"}
            texto={editando ? "Editar modelo" : "Nuevo modelo"}
            color={editando ? Color.amarillo : Color.primario}
          />

          <View style={[base.row, { marginBottom: 12, alignItems: "flex-start" }]}>
            <TouchableOpacity style={styles.foto} onPress={elegirFoto}>
              {vistaFoto ? (
                <Image source={{ uri: vistaFoto }} style={styles.fotoImg} contentFit="cover" />
              ) : (
                <FontAwesome5 name="camera" size={22} color={Color.textoSuave} />
              )}
            </TouchableOpacity>
            <View style={{ flex: 1, gap: 6 }}>
              <Boton texto="Elegir imagen" icono="image" tipo="outline" onPress={elegirFoto} style={{ height: 36 }} />
              {vistaFoto && editando ? (
                <Boton
                  texto="Quitar imagen"
                  icono="times"
                  tipo="outline"
                  onPress={() => {
                    setFoto(null);
                    setQuitarFoto(true);
                  }}
                  style={{ height: 36 }}
                />
              ) : null}
              <Text style={[base.cardText, { fontSize: 11 }]}>JPG, PNG o WEBP, máx. 4 MB</Text>
            </View>
          </View>

          <Text style={base.label}>Nombre del modelo</Text>
          <Entrada placeholder="Ej. Playera cuello V" value={form.nombre} onChangeText={(v) => cambiar({ nombre: v })} />
          <Text style={base.label}>Categoría</Text>
          <Selector placeholder="Selecciona una categoría" opciones={datos.categorias} valor={form.id_categoria} onChange={(id) => cambiar({ id_categoria: id })} />
          <Text style={base.label}>Segmento</Text>
          <Chips opciones={datos.segmentos} valor={form.segmento} onChange={(v) => cambiar({ segmento: v })} />
          <View style={base.row}>
            <View style={{ flex: 1 }}>
              <Text style={base.label}>Color</Text>
              <Entrada placeholder="Opcional" value={form.color} onChangeText={(v) => cambiar({ color: v })} />
            </View>
            <View style={{ alignItems: "center" }}>
              <Text style={base.label}>Visible en tienda</Text>
              <Switch
                value={form.activo}
                onValueChange={(v) => cambiar({ activo: v })}
                trackColor={{ true: Color.primario, false: Color.borde }}
                thumbColor="#fff"
                style={{ marginBottom: 12 }}
              />
            </View>
          </View>
          <Text style={base.label}>Descripción</Text>
          <Entrada placeholder="Opcional" multiline style={{ height: 70, paddingTop: 10, textAlignVertical: "top" }} value={form.descripcion} onChangeText={(v) => cambiar({ descripcion: v })} />

          <View style={base.row}>
            <View style={{ flex: 1 }}>
              <Text style={base.label}>Precio proveedor</Text>
              <Entrada placeholder="0.00" keyboardType="decimal-pad" value={form.preciopro} onChangeText={(v) => cambiar({ preciopro: v })} />
            </View>
            <View style={{ flex: 1 }}>
              <Text style={base.label}>Precio de venta</Text>
              <Entrada placeholder="0.00" keyboardType="decimal-pad" value={form.precioneto} onChangeText={(v) => cambiar({ precioneto: v })} />
            </View>
          </View>
          <View style={base.row}>
            <View style={{ flex: 1 }}>
              <Text style={base.label}>Precio mayoreo</Text>
              <Entrada placeholder="Opcional" keyboardType="decimal-pad" value={form.precio_mayoreo} onChangeText={(v) => cambiar({ precio_mayoreo: v })} />
            </View>
            <View style={{ flex: 1 }}>
              <Text style={base.label}>Desde (piezas)</Text>
              <Entrada placeholder="5" keyboardType="numeric" value={form.min_mayoreo} onChangeText={(v) => cambiar({ min_mayoreo: v })} />
            </View>
          </View>

          <Text style={base.label}>Tallas y stock</Text>
          {tallas.map((t, i) => (
            <View key={i} style={[base.row, { alignItems: "flex-start" }]}>
              <Entrada
                style={{ flex: 1 }}
                placeholder="Talla"
                autoCapitalize="characters"
                value={t.talla}
                onChangeText={(v) => setTallas(tallas.map((x, j) => (j === i ? { ...x, talla: v } : x)))}
              />
              <Entrada
                style={{ flex: 1 }}
                placeholder="Stock"
                keyboardType="numeric"
                value={t.stock}
                onChangeText={(v) => setTallas(tallas.map((x, j) => (j === i ? { ...x, stock: v.replace(/\D/g, "") } : x)))}
              />
              <TouchableOpacity
                disabled={tallas.length === 1}
                onPress={() => setTallas(tallas.filter((_, j) => j !== i))}
                style={{ padding: 14, opacity: tallas.length === 1 ? 0.3 : 1 }}>
                <FontAwesome5 name="trash" size={14} color={Color.peligro} />
              </TouchableOpacity>
            </View>
          ))}
          <Chips<string>
            opciones={Object.fromEntries(datos.sugeridas.filter((s) => !tallas.some((t) => t.talla.toUpperCase() === s)).map((s) => [s, `+ ${s}`]))}
            valor=""
            onChange={(s) => setTallas([...tallas.filter((t) => t.talla.trim()), { talla: s, stock: "0" }])}
          />

          <View style={base.row}>
            <Boton
              texto={guardando ? "Guardando…" : editando ? "Actualizar" : "Guardar"}
              icono="save"
              tipo={editando ? "orange" : "primary"}
              onPress={guardar}
              disabled={guardando}
              style={{ flex: 1 }}
            />
            <Boton texto="Cancelar" tipo="outline" onPress={limpiar} style={{ marginTop: 6, height: 44 }} />
          </View>
        </View>
      ) : null}

      <Entrada icono="search" placeholder="Buscar modelo" value={buscar} onChangeText={setBuscar} />
      {cargando && !datos ? <ActivityIndicator color={Color.primario} /> : null}
      {datos && productos.length === 0 ? <Text style={base.vacio}>No hay modelos.</Text> : null}

      {productos.map((p) => (
        <View key={p.id} style={[base.card, editando === p.id && { borderColor: Color.amarillo }]}>
          <View style={base.row}>
            {p.imagen ? (
              <Image source={{ uri: urlServidor(p.imagen) }} style={styles.mini} contentFit="cover" />
            ) : (
              <View style={[styles.mini, styles.sinFoto]}>
                <FontAwesome5 name="tshirt" size={18} color={Color.textoSuave} />
              </View>
            )}
            <View style={{ flex: 1 }}>
              <Text style={base.cardTitle}>{p.nombre}</Text>
              <Text style={base.cardText}>
                {p.categoria ?? "Sin categoría"} · {p.segmento_texto}
              </Text>
              <Text style={[base.cardText, { color: Color.exito, fontWeight: "700" }]}>
                {dinero(p.precioneto)}
                {p.precio_mayoreo ? <Text style={{ color: Color.textoSuave, fontWeight: "400" }}>  · mayoreo {dinero(p.precio_mayoreo)} desde {p.min_mayoreo}</Text> : null}
              </Text>
            </View>
            <TouchableOpacity style={{ padding: 8 }} onPress={() => editar(p)}>
              <FontAwesome5 name="edit" size={15} color={Color.amarillo} />
            </TouchableOpacity>
            <TouchableOpacity style={{ padding: 8 }} onPress={() => eliminar(p)}>
              <FontAwesome5 name="trash" size={15} color={Color.peligro} />
            </TouchableOpacity>
          </View>
          <View style={styles.tallas}>
            {p.tallas.map((t) => (
              <Badge key={t.talla} texto={`${t.talla}: ${t.stock}`} color={t.stock < 5 ? "red" : "blue"} />
            ))}
            {!p.activo ? <Badge texto="Oculto en tienda" color="orange" /> : null}
          </View>
        </View>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  foto: {
    width: 96,
    height: 96,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: Color.borde,
    borderStyle: "dashed",
    backgroundColor: Color.input,
    justifyContent: "center",
    alignItems: "center",
    overflow: "hidden",
  },
  fotoImg: {
    width: "100%",
    height: "100%",
  },
  mini: {
    width: 52,
    height: 52,
    borderRadius: 8,
    backgroundColor: Color.input,
  },
  sinFoto: {
    justifyContent: "center",
    alignItems: "center",
  },
  tallas: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 4,
    marginTop: 8,
  },
});
