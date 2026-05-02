-- Dimensiones de pantalla para la tarjeta de dispositivos del Escudo Radar
ALTER TABLE user_sessions
ADD COLUMN screen_w SMALLINT UNSIGNED NULL AFTER user_agent,
ADD COLUMN screen_h SMALLINT UNSIGNED NULL AFTER screen_w;
