-- Migration: Add puede_capturar_pagos column to usuarios table
-- Run this on the live database

ALTER TABLE `usuarios`
  ADD COLUMN `puede_capturar_pagos` tinyint(1) NOT NULL DEFAULT 0
  AFTER `puede_cancelar_recibos`;
