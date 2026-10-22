import { FontAwesome5 } from "@expo/vector-icons";
import { useState } from "react";
import { StyleSheet, TextInput, TextInputProps, View } from "react-native";

import { base, Color } from "@/constants/estilos";

type Props = TextInputProps & {
  // Icono de Font Awesome a la izquierda, como en el login del panel
  icono?: React.ComponentProps<typeof FontAwesome5>["name"];
};

/** Campo de texto con el estilo oscuro del panel (.fc); el borde se pinta de azul al enfocar. */
export function Entrada({ icono, style, onFocus, onBlur, ...rest }: Props) {
  const [foco, setFoco] = useState(false);

  const input = (
    <TextInput
      placeholderTextColor={Color.placeholder}
      style={[base.input, icono && styles.conIcono, foco && styles.foco, style]}
      onFocus={(e) => {
        setFoco(true);
        onFocus?.(e);
      }}
      onBlur={(e) => {
        setFoco(false);
        onBlur?.(e);
      }}
      {...rest}
    />
  );

  if (!icono) return input;
  return (
    <View>
      <FontAwesome5 name={icono} size={14} color={Color.textoSuave} style={styles.icono} />
      {input}
    </View>
  );
}

const styles = StyleSheet.create({
  conIcono: {
    paddingLeft: 40,
    height: 50,
    borderRadius: 10,
    fontSize: 15,
  },
  foco: {
    borderColor: Color.primario,
  },
  icono: {
    position: "absolute",
    left: 14,
    top: 17,
    zIndex: 1,
  },
});
