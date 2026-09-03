-- ============================================================
--  Migración: las 5 dimensiones en el historial del score
--
--  Por qué: score_diario y score_historial se crearon cuando el termómetro
--  tenía 3 dimensiones (Activación, Seguimiento, Conversión). El termómetro
--  pasó a 5 —se agregaron Engagement y Radar Health— y nadie amplió las tablas.
--
--  Consecuencia real: si un asesor cae 15 puntos porque se le murieron los
--  clientes calientes, el historial NO puede decirlo. Solo muestra que el score
--  bajó, sin la dimensión responsable. Y Radar Health es justo la que más se
--  mueve de las cinco.
--
--  Aditiva y con default 0. Las filas viejas quedan en 0 en las dos columnas
--  nuevas — que es honesto: en esos días el dato no se guardó, no es que valiera
--  cero. El reporte solo compara dimensiones desde que existan datos (ver
--  RitmoReporte::_historial(), que exige s_engagement > 0 para considerarlas).
-- ============================================================

ALTER TABLE `score_diario`
    ADD COLUMN IF NOT EXISTS `s_engagement`   DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `s_activacion`,
    ADD COLUMN IF NOT EXISTS `s_radar_health` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `s_seguimiento`;

ALTER TABLE `score_historial`
    ADD COLUMN IF NOT EXISTS `s_engagement`   DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `s_activacion`,
    ADD COLUMN IF NOT EXISTS `s_radar_health` DECIMAL(5,3) NOT NULL DEFAULT 0 AFTER `s_seguimiento`;
