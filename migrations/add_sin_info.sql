-- Tercera manita: 📱 "Sin info" — el asesor intentó contactar y el cliente no
-- respondió: no hay juicio honesto posible (👍👎 sería inventado). Cuenta como
-- evaluación para la cobertura de la mesa (manita + postura = calificada),
-- es NEUTRAL en la calidad del termómetro (no hay juicio que validar) y NO
-- descarta la cotización (sigue viva; envejece a Frías por el flujo normal).
-- Candado: solo se puede marcar con contacto vigente = 'no_contesta'.
-- Correr ANTES de desplegar el código que la usa.
ALTER TABLE radar_feedback
    MODIFY COLUMN tipo ENUM('con_interes','sin_interes','sin_info') NOT NULL;
