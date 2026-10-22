import { useFocusEffect } from "expo-router";
import { useCallback, useState } from "react";

import { api } from "@/lib/api";
import { avisar } from "@/lib/dialogos";

/** Carga una ruta GET de la API cada vez que la pantalla se muestra; `recargar` sirve para pull-to-refresh. */
export function useApi<T>(ruta: string) {
  const [datos, setDatos] = useState<T | null>(null);
  const [cargando, setCargando] = useState(true);

  const recargar = useCallback(async () => {
    setCargando(true);
    try {
      setDatos(await api<T>(ruta));
    } catch (e) {
      avisar("Error", (e as Error).message);
    } finally {
      setCargando(false);
    }
  }, [ruta]);

  useFocusEffect(
    useCallback(() => {
      recargar();
    }, [recargar])
  );

  return { datos, cargando, recargar };
}
