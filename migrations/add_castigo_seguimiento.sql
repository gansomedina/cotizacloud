-- Ciclo de seguimiento Fase C (docs/mesa_seguimiento_diseno.md):
-- castigo DIRECTO sobre el score final, espejo de los boosters
-- (bonus_ticket/bonus_cierre). Métrica: días-vencidos acumulados del asesor
-- en la ventana rolling (mesa_vencidos) — tocar detiene la acumulación, no
-- la borra. Niveles: 3+ → -2 (silencioso) · 7+ → -5 (frase) · 14+ → -8 tope.
-- Gate: empresas.mesa_activa = 2 (el mismo del blend s_mesa).
-- Correr ANTES de desplegar la Fase C.
ALTER TABLE usuario_score
    ADD COLUMN mesa_dias_vencidos  INT UNSIGNED NOT NULL DEFAULT 0 AFTER s_mesa,
    ADD COLUMN castigo_seguimiento INT UNSIGNED NOT NULL DEFAULT 0 AFTER mesa_dias_vencidos;
