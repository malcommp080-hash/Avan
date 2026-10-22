import { Alert, Platform } from "react-native";

// Alert.alert no hace nada en web; ahí se usan alert() y confirm() del navegador

export function avisar(titulo: string, mensaje?: string) {
  if (Platform.OS === "web") {
    window.alert(mensaje ? `${titulo}\n\n${mensaje}` : titulo);
    return;
  }
  Alert.alert(titulo, mensaje);
}

/** Pregunta sí/no; resuelve true si el usuario acepta. */
export function confirmar(titulo: string, mensaje: string, textoSi = "Aceptar", peligro = false): Promise<boolean> {
  if (Platform.OS === "web") {
    return Promise.resolve(window.confirm(`${titulo}\n\n${mensaje}`));
  }
  return new Promise((resolve) =>
    Alert.alert(titulo, mensaje, [
      { text: "Cancelar", style: "cancel", onPress: () => resolve(false) },
      { text: textoSi, style: peligro ? "destructive" : "default", onPress: () => resolve(true) },
    ], { cancelable: true, onDismiss: () => resolve(false) })
  );
}
