-- ============================================================
--  Backfill: alinear `nivel` al estándar de 70 (decisión CEO, 3 sep 2026)
--
--  Los umbrales del ENUM eran 86 / 61 / 31, heredados de cuando el score se
--  diseñó asumiendo que ~50 era el centro de la distribución. El estándar del
--  negocio es otro: 85 excelencia, 70 estándar, 60 el piso.
--
--  Con los viejos, el dashboard le decía "Activo" a un 62 —que en la escala del
--  negocio está DEBAJO del estándar— y "Regular" a un 35, que es crítico. El
--  mismo asesor recibía dos veredictos distintos en dos pantallas.
--
--  Este backfill reescribe el `nivel` de las filas YA GUARDADAS a partir del
--  score que cada una tiene. No toca los scores: solo la etiqueta.
--
--  · 'nuevo' se preserva SIEMPRE. Es el período de gracia (score 0 por early
--    return de ActividadScore), no una calificación — recalcularlo lo
--    convertiría en 'bajo' y marcaría como fracaso a quien apenas entró.
--  · Es idempotente: correrlo dos veces da el mismo resultado.
--
--  Correr DESPUÉS de desplegar el código (ActividadScore ya escribe con los
--  umbrales nuevos). Si se corre antes, el siguiente recálculo de cada asesor
--  lo dejaría igual de todas formas — pero el histórico no se recalcula solo,
--  y por eso este script existe.
-- ============================================================

UPDATE `usuario_score`
   SET `nivel` = CASE
        WHEN `nivel` = 'nuevo' THEN 'nuevo'
        WHEN `score` >= 85     THEN 'top'
        WHEN `score` >= 70     THEN 'activo'
        WHEN `score` >= 60     THEN 'regular'
        ELSE 'bajo'
   END;

UPDATE `score_diario`
   SET `nivel` = CASE
        WHEN `nivel` = 'nuevo' THEN 'nuevo'
        WHEN `score` >= 85     THEN 'top'
        WHEN `score` >= 70     THEN 'activo'
        WHEN `score` >= 60     THEN 'regular'
        ELSE 'bajo'
   END;

UPDATE `score_historial`
   SET `nivel` = CASE
        WHEN `nivel` = 'nuevo' THEN 'nuevo'
        WHEN `score` >= 85     THEN 'top'
        WHEN `score` >= 70     THEN 'activo'
        WHEN `score` >= 60     THEN 'regular'
        ELSE 'bajo'
   END;
