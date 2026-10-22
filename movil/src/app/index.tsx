import { Redirect } from "expo-router";

import { empleadoActual } from "@/lib/api";

// La sesión guardada ya se leyó en el layout raíz: si existe va directo al dashboard
export default function Index() {
  return <Redirect href={empleadoActual() ? "/dashboard" : "/Login"} />;
}
