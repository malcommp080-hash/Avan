import Constants from "expo-constants";
import { Platform } from "react-native";

// En la PC, dentro de C:\laragon\www\AvanLara, corre:  php artisan serve --host=0.0.0.0 --port=8000
const PUERTO_API = 8000;

/**
 * La API corre en la misma PC que `npx expo start`, así que se usa la IP con la que el celular
 * llegó a Expo (cambia según la red Wi-Fi). Si eso falla, se usa IP_RESPALDO: pon ahí la de tu PC
 * (en PowerShell: ipconfig → "Dirección IPv4").
 */
const IP_RESPALDO = "192.168.1.159";

function ipDelServidor() {
  if (Platform.OS === "web" && typeof window !== "undefined") return window.location.hostname;
  const hostUri = Constants.expoConfig?.hostUri; // p. ej. "10.1.4.95:8081"
  return hostUri ? hostUri.split(":")[0] : IP_RESPALDO;
}

export const SERVER_URL = `http://${ipDelServidor()}:${PUERTO_API}`;

export const API_URL = `${SERVER_URL}/api/admin`;

/** Convierte una ruta relativa del servidor (p. ej. "img/comprobantes/x.jpg") en URL completa. */
export const urlServidor = (ruta: string) => `${SERVER_URL}/${ruta}`;
