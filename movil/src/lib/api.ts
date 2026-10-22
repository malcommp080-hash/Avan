import { File, Paths } from "expo-file-system";
import * as SecureStore from "expo-secure-store";
import * as Sharing from "expo-sharing";
import { router } from "expo-router";
import { Platform } from "react-native";

import { API_URL } from "@/constants/api";

const CLAVE_TOKEN = "dd_token";
const CLAVE_EMPLEADO = "dd_empleado";

export type Empleado = { id: number; nombre: string; telefono: string; rol: "admin" | "vendedor" | "almacen" | "contador" };

// En web no hay SecureStore: se usa localStorage
const guardar = async (clave: string, valor: string | null) => {
  if (Platform.OS === "web") {
    if (valor === null) localStorage.removeItem(clave);
    else localStorage.setItem(clave, valor);
    return;
  }
  if (valor === null) await SecureStore.deleteItemAsync(clave);
  else await SecureStore.setItemAsync(clave, valor);
};

const leer = async (clave: string) =>
  Platform.OS === "web" ? localStorage.getItem(clave) : SecureStore.getItemAsync(clave);

let token: string | null = null;
let empleado: Empleado | null = null;

export async function cargarSesion() {
  token = await leer(CLAVE_TOKEN);
  const e = await leer(CLAVE_EMPLEADO);
  empleado = e ? JSON.parse(e) : null;
  return token !== null;
}

export async function guardarSesion(nuevoToken: string, nuevoEmpleado: Empleado) {
  token = nuevoToken;
  empleado = nuevoEmpleado;
  await guardar(CLAVE_TOKEN, nuevoToken);
  await guardar(CLAVE_EMPLEADO, JSON.stringify(nuevoEmpleado));
}

export async function borrarSesion() {
  token = null;
  empleado = null;
  await guardar(CLAVE_TOKEN, null);
  await guardar(CLAVE_EMPLEADO, null);
}

export const empleadoActual = () => empleado;

const encabezados = () => ({
  Accept: "application/json",
  ...(token ? { Authorization: `Bearer ${token}` } : {}),
});

const MSG_SIN_CONEXION =
  "No se pudo conectar con el servidor. Revisa que esté corriendo `php artisan serve --host=0.0.0.0 --port=8000` y que estés en la misma red.";

/** Lee la respuesta; si la sesión expiró (401) regresa al login. Lanza Error con el mensaje del servidor. */
async function respuesta<T>(response: Response, ruta: string): Promise<T> {
  const data = await response.json().catch(() => ({}));

  if (response.status === 401 && ruta !== "/login") {
    await borrarSesion();
    router.replace("/Login");
  }
  if (!response.ok) {
    // Errores de validación de Laravel: { message, errors: { campo: [..] } }
    const primero = data.errors ? Object.values(data.errors as Record<string, string[]>)[0]?.[0] : null;
    throw new Error(primero || data.message || `Error ${response.status}`);
  }
  return data as T;
}

/** Llama a la API del panel con JSON. */
export async function api<T = any>(ruta: string, metodo: "GET" | "POST" | "PUT" | "DELETE" = "GET", datos?: object): Promise<T> {
  let response: Response;
  try {
    response = await fetch(`${API_URL}${ruta}`, {
      method: metodo,
      headers: { ...encabezados(), "Content-Type": "application/json" },
      body: datos ? JSON.stringify(datos) : undefined,
    });
  } catch {
    throw new Error(MSG_SIN_CONEXION);
  }
  return respuesta<T>(response, ruta);
}

/** Envía un formulario multipart (imágenes, archivos .sql). */
export async function subir<T = any>(ruta: string, datos: FormData): Promise<T> {
  let response: Response;
  try {
    response = await fetch(`${API_URL}${ruta}`, { method: "POST", headers: encabezados(), body: datos });
  } catch {
    throw new Error(MSG_SIN_CONEXION);
  }
  return respuesta<T>(response, ruta);
}

/** Descarga un archivo de la API (respaldo .sql, PDF) y lo abre para guardarlo o compartirlo. */
export async function descargar(ruta: string, nombre: string, tipo: string) {
  if (Platform.OS === "web") {
    const response = await fetch(`${API_URL}${ruta}`, { headers: encabezados() }).catch(() => {
      throw new Error(MSG_SIN_CONEXION);
    });
    if (!response.ok) await respuesta(response, ruta);
    const url = URL.createObjectURL(await response.blob());
    const a = document.createElement("a");
    a.href = url;
    a.download = nombre;
    a.click();
    URL.revokeObjectURL(url);
    return;
  }

  const destino = new File(Paths.cache, nombre);
  try {
    await File.downloadFileAsync(`${API_URL}${ruta}`, destino, { headers: encabezados(), idempotent: true });
  } catch (e) {
    throw new Error((e as Error).message || MSG_SIN_CONEXION);
  }
  if (await Sharing.isAvailableAsync()) {
    await Sharing.shareAsync(destino.uri, { mimeType: tipo, dialogTitle: nombre });
  }
}

export const dinero = (n: number) =>
  "$" + Number(n).toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

/** Iniciales para el avatar: "Juan Pérez" → "JP". */
export const iniciales = (nombre: string) =>
  nombre.split(" ").filter(Boolean).slice(0, 2).map((p) => p[0].toUpperCase()).join("");
