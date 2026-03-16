-- ============================================================
-- CotizaCloud v2: import ventas from invoice-export CSV
-- Generated: 2026-03-16 18:16:23
-- Total: 106 ventas
-- Dates and totals come directly from WordPress invoice export
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- INV-0152: Mario Ibarra M. Calle Arboretum #13, Residencial Bonaterra 5216621915913 → matched imp-v2-quo-944
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0152', 'Mario Ibarra M. Calle Arboretum #13, Residencial Bonaterra 5216621915913', 'imp-v2-vta-inv-0152', 'e9c24024577b3985c86c98ee83d7259e031b99a645d89d67126fd3a2cfee4f2e', 33200, 0, 33200, 'pendiente', '2026-03-13 12:00:00', '2026-03-13 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-944';

-- INV-0151: Jesús Parra  6421143689 → matched imp-v2-quo-899
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0151', 'Jesús Parra  6421143689', 'imp-v2-vta-inv-0151', '64fd1afb7e63d8786012fbfbc4b317b1b13594f693419c9922dea93b85d5b91a', 66000, 0, 66000, 'pendiente', '2026-03-10 12:00:00', '2026-03-10 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-899';

-- INV-0150: Noemí Valle del marqués calle Tezcatlipoca 6624485651 → matched imp-v2-quo-934
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0150', 'Noemí Valle del marqués calle Tezcatlipoca 6624485651', 'imp-v2-vta-inv-0150', '7b32cd3a0f224f80e81cf4077de41c8cf9c9b16dcfea212984664b0b8cfbd875', 25400, 0, 25400, 'pendiente', '2026-03-07 12:00:00', '2026-03-07 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-934';

-- INV-0149: Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170 → matched imp-v2-quo-894
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0149', 'Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170', 'imp-v2-vta-inv-0149', 'e2f9d939a4d8ae17ffc2d4717d93970a281ac2fd7f016dcc16867938523582d8', 21400, 0, 21400, 'pendiente', '2026-03-05 12:00:00', '2026-03-05 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-894';

-- INV-0148: Miguel Cruz Fraccionamiento Monteregio, Vernaccia 36 6621609041 → matched imp-v2-quo-924
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0148', 'Miguel Cruz Fraccionamiento Monteregio, Vernaccia 36 6621609041', 'imp-v2-vta-inv-0148', 'fcd3f36019d6a3486822fc9fb9a581014e2d85e24f1d35a7a0f8138cac318d5c', 48800, 0, 48800, 'pendiente', '2026-03-04 12:00:00', '2026-03-04 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-924';

-- INV-0147: Natalia Aranda 5216621718828 → matched imp-v2-quo-608
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0147', 'Natalia Aranda 5216621718828', 'imp-v2-vta-inv-0147', 'f606a307a65e19fff0164fd6f0ce95f4ed84ead18423c24c11f994103b2eddf1', 16800, 16800, 0, 'pagada', '2026-03-02 12:00:00', '2026-03-02 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-608';

-- INV-0146: Ana Lourdes León Campillo 108 6624223314 → matched imp-v2-quo-911
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0146', 'Ana Lourdes León Campillo 108 6624223314', 'imp-v2-vta-inv-0146', 'a2a26d48897744667f5755f5e31e379673eb7e7a6138292da3db345f502a904a', 18600, 0, 18600, 'pendiente', '2026-02-28 12:00:00', '2026-02-28 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-911';

-- INV-0145: Ana Luisa Romo Apasible 24, Nueva Galicia 6622332809 → matched imp-v2-quo-866
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0145', 'Ana Luisa Romo Apasible 24, Nueva Galicia 6622332809', 'imp-v2-vta-inv-0145', '4a3d456d89792a525e3528d0022e405e7034d863dfefcd4a74d4bec64bb3ddb0', 21000, 0, 21000, 'pendiente', '2026-02-25 12:00:00', '2026-02-25 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-866';

-- INV-0144: Beatriz Guerrero Jimenez 5216622251632 Zonata 25 , Agaves → matched imp-v2-quo-846
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0144', 'Beatriz Guerrero Jimenez 5216622251632 Zonata 25 , Agaves', 'imp-v2-vta-inv-0144', '01c5119eebd061fc81b74b2d5fe4b768bbadb9b8b8b15b05c749e11761701cfe', 16400, 16400, 0, 'pagada', '2026-02-25 12:00:00', '2026-02-25 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-846';

-- INV-0143: Ruth Isela Salomón Alvarez Horizonte dorado #76 Fracc. El Encanto 6621733456 → matched imp-v2-quo-915
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0143', 'Ruth Isela Salomón Alvarez Horizonte dorado #76 Fracc. El Encanto 6621733456', 'imp-v2-vta-inv-0143', '8d743e3a7b1bf53d65b603ed3663f26f850a64892cb1cdc0c131d6135e1c0589', 15000, 15000, 0, 'pagada', '2026-02-25 12:00:00', '2026-02-25 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-915';

-- INV-0142: Karla Muñoz Miguel Alemán #24, Colonia ISSSTE 6623425929 → matched imp-v2-quo-743
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0142', 'Karla Muñoz Miguel Alemán #24, Colonia ISSSTE 6623425929', 'imp-v2-vta-inv-0142', 'a278bc92d97199cd185783a744cf7757a0c9753a3061ef99e97620838d66ce12', 19500, 19500, 0, 'pagada', '2026-02-17 12:00:00', '2026-02-17 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-743';

-- INV-0141: Erik Fregoso Privada sahara #10 colonia las Lomas sección almendros 6451105652 → matched imp-v2-quo-893
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0141', 'Erik Fregoso Privada sahara #10 colonia las Lomas sección almendros 6451105652', 'imp-v2-vta-inv-0141', '9474eb3513daeddee2c05a80cebdb7227ca81930abb2d0c4ac868b9a9bc1e6e9', 50500, 50500, 0, 'pagada', '2026-02-14 12:00:00', '2026-02-14 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-893';

-- INV-0140: Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170 → matched imp-v2-quo-894
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0140', 'Estreberto Grijalva Arvizu Calle Petrarca Número 3, Lomas del Sur 5216622562170', 'imp-v2-vta-inv-0140', '59f9f6abfb8960d9abdf8c3d23cd784ea1c735ee21f0c01b2c37d3437a41114d', 38300, 38300, 0, 'pagada', '2026-02-14 12:00:00', '2026-02-14 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-894';

-- INV-0139: Alberto Álvarez Privada Carsoli 11, Villa Bonita 6628474329 → matched imp-v2-quo-729
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0139', 'Alberto Álvarez Privada Carsoli 11, Villa Bonita 6628474329', 'imp-v2-vta-inv-0139', '98814539b5a18719c260488f30920643e1cf2f969468b05d9ee7683e802bd314', 30000, 0, 30000, 'pendiente', '2026-02-13 12:00:00', '2026-02-13 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-729';

-- INV-0138: Samir Ochoa 6621391597 → matched imp-v2-quo-810
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0138', 'Samir Ochoa 6621391597', 'imp-v2-vta-inv-0138', '9fa64c0ac448cfab06e1da63f01a06c4cecfa84eb90d75c878008ae26247cf09', 18350, 18350, 0, 'pagada', '2026-01-16 12:00:00', '2026-01-16 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-810';

-- INV-0137: Brenda Araceli Garcia Soto 5219991009187 puerta real → matched imp-v2-quo-689
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0137', 'Brenda Araceli Garcia Soto 5219991009187 puerta real', 'imp-v2-vta-inv-0137', '1a90e9451c34c9d9ed0a1e90a8724bc1273f692689465fe852903b9101055b34', 16800, 16800, 0, 'pagada', '2026-01-13 12:00:00', '2026-01-13 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-689';

-- INV-0136: Carlos Rosas Del Obsequio 11, El Secreto 6623264839 → matched imp-v2-quo-788
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0136', 'Carlos Rosas Del Obsequio 11, El Secreto 6623264839', 'imp-v2-vta-inv-0136', 'bb70e45f9fd4a26e2de363f6a090d5dee43c0ad4656ae136fde9241bf144d0f1', 17690, 17690, 0, 'pagada', '2026-01-10 12:00:00', '2026-01-10 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-788';

-- INV-0135: Jades Aguilar Castillo Privada del Razo núm 17, Col Casa Blanca 5216622050063 → matched imp-v2-quo-809
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0135', 'Jades Aguilar Castillo Privada del Razo núm 17, Col Casa Blanca 5216622050063', 'imp-v2-vta-inv-0135', '0e338d91ed3aed88d9ebe6b1717492d3d54db0a41d732f89d50da3c5187659e7', 57350, 57350, 0, 'pagada', '2026-01-10 12:00:00', '2026-01-10 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-809';

-- INV-0122: Paloma Benitez cerrada santo domingo colonia san marcos 6621025504 → matched imp-v2-quo-571
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0122', 'Paloma Benitez cerrada santo domingo colonia san marcos 6621025504', 'imp-v2-vta-inv-0122', '8212e2eee9b1aecf4a03ce45617ed903206e38507706089b9d446eade6a45d6a', 14000, 14000, 0, 'pagada', '2026-01-08 12:00:00', '2026-01-08 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-571';

-- INV-0120: Sandra Martínez Divisaderos 109, Lomas de Lindavista 6621409816 → matched imp-v2-quo-722
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0120', 'Sandra Martínez Divisaderos 109, Lomas de Lindavista 6621409816', 'imp-v2-vta-inv-0120', 'd1ea3724ffc9c7f77b54e369c00cc50c8c118ae74a028c791370e69f6c8da543', 16000, 16000, 0, 'pagada', '2026-01-07 12:00:00', '2026-01-07 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-722';

-- INV-0118: Ibel Agramond Privada Texcoco 4 Fracc Perisur Hermosillo 6623722093 → matched imp-v2-quo-762
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0118', 'Ibel Agramond Privada Texcoco 4 Fracc Perisur Hermosillo 6623722093', 'imp-v2-vta-inv-0118', 'c7e6abba660d5007f9a06029b9513de8b86efa6b0717f60a426ab6ad73cacf80', 17900, 17900, 0, 'pagada', '2025-12-31 12:00:00', '2025-12-31 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-762';

-- INV-0117: Alberto Álvarez Privada Carsoli 11, Villa Bonita 6628474329 → matched imp-v2-quo-729
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0117', 'Alberto Álvarez Privada Carsoli 11, Villa Bonita 6628474329', 'imp-v2-vta-inv-0117', 'f566e969c23b275a3a02d80bfa5e93f3a9886642326018832d40059387c836c7', 33900, 33900, 0, 'pagada', '2025-12-29 12:00:00', '2025-12-29 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-729';

-- INV-0116: Julissa López San Francisco Valle Residencial 6621076656 → matched imp-v2-quo-718
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0116', 'Julissa López San Francisco Valle Residencial 6621076656', 'imp-v2-vta-inv-0116', 'a931503c98ec6f1f0c2f0d38cc353efeb0b7f1cabbc47c2758913f43c68cc48f', 15600, 15600, 0, 'pagada', '2025-12-04 12:00:00', '2025-12-04 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-718';

-- INV-0115: Aracely Romero Avenida 5 218 6624307579 → matched imp-v2-quo-725
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0115', 'Aracely Romero Avenida 5 218 6624307579', 'imp-v2-vta-inv-0115', '242e225f7d9c9cf86fc123ab0ca7fb9fe60e30a67c0c93aed804da01820653f4', 25800, 25800, 0, 'pagada', '2025-12-03 12:00:00', '2025-12-03 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-725';

-- INV-0110: Beatriz Galaviz Arredondo Alce 176 altares 6623567770 → matched imp-v2-quo-708
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0110', 'Beatriz Galaviz Arredondo Alce 176 altares 6623567770', 'imp-v2-vta-inv-0110', '0957c03f46957be88ad4244114938ced194b6bc56cfbe257197c4d4729ff1f5d', 17900, 17900, 0, 'pagada', '2025-11-28 12:00:00', '2025-11-28 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-708';

-- INV-0109: Jorge Galaviz Fracc Real de Sevilla 6861960663 → matched imp-v2-quo-703
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0109', 'Jorge Galaviz Fracc Real de Sevilla 6861960663', 'imp-v2-vta-inv-0109', '23aaf4def104f8aa3665ca0190e17f38b88edd908f134d6f71f17b8b067186c0', 19500, 19500, 0, 'pagada', '2025-11-27 12:00:00', '2025-11-27 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-703';

-- INV-0107: jazmin gonzalez rodriguez 476 santa isabel 6421121859 → matched imp-v2-quo-707
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0107', 'jazmin gonzalez rodriguez 476 santa isabel 6421121859', 'imp-v2-vta-inv-0107', '105183f6c8cc52077144f309b5656953b16c2374c708dfbbbe368bb68e60e460', 27200, 27200, 0, 'pagada', '2025-11-26 12:00:00', '2025-11-26 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-707';

-- INV-0101: Miguel Ángel Sánchez Cerrada Realeza 31, Paseo Real Residencial 6441118340 → matched imp-v2-quo-538
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0101', 'Miguel Ángel Sánchez Cerrada Realeza 31, Paseo Real Residencial 6441118340', 'imp-v2-vta-inv-0101', 'f518c7224f13866c4278cb036addaaa45092e32c64ea1581b0b9a1fe9158dbb1', 15500, 15500, 0, 'pagada', '2025-11-10 12:00:00', '2025-11-10 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-538';

-- INV-0099: isaac martinez espinoza Jose Gonzalez 487 col Benito Juárez 6624192743 → matched imp-v2-quo-644
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0099', 'isaac martinez espinoza Jose Gonzalez 487 col Benito Juárez 6624192743', 'imp-v2-vta-inv-0099', 'd64191bace1a7fba2d81e58ec9fb94f4628bca077780fbf84764f9ccaf34076b', 22000, 22000, 0, 'pagada', '2025-11-03 12:00:00', '2025-11-03 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-644';

-- INV-0098: Gissel Felix Ocarina Belcanto Residencial 6623488724 → matched imp-v2-quo-625
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0098', 'Gissel Felix Ocarina Belcanto Residencial 6623488724', 'imp-v2-vta-inv-0098', '4c2cd90ba3142963ba5fdb86452d81be6736d05defd0773e4055a213d70470af', 16000, 16000, 0, 'pagada', '2025-10-27 12:00:00', '2025-10-27 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-625';

-- INV-0097: Gabriel Montoya otaria 12, mar de plata 6623174171 – copy → matched imp-v2-quo-634
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0097', 'Gabriel Montoya otaria 12, mar de plata 6623174171 – copy', 'imp-v2-vta-inv-0097', '03cecd713b301b48ea620f383ac2a82a875eb47db0ec985eb8ecc3197103d053', 32000, 32000, 0, 'pagada', '2025-10-27 12:00:00', '2025-10-27 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-634';

-- INV-0094: Sonia Cerrada borgoño #1, Campo grande 6622981798 → matched imp-v2-quo-606
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0094', 'Sonia Cerrada borgoño #1, Campo grande 6622981798', 'imp-v2-vta-inv-0094', '6d4f691a939a2b7c117eb1c09c5780e819ea1a9b2b92fe90454d71d48fd33b71', 20500, 20500, 0, 'pagada', '2025-11-16 12:00:00', '2025-11-16 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-606';

-- INV-0093: Esthela Carolina pompa Encinas Santa Martha 92 6622293718 → matched imp-v2-quo-431
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0093', 'Esthela Carolina pompa Encinas Santa Martha 92 6622293718', 'imp-v2-vta-inv-0093', '0de2b8a76ce25350648fb5fb65ccd0e56e679adeeea7f33ee8ea3d0b610c231f', 35000, 35000, 0, 'pagada', '2025-10-13 12:00:00', '2025-10-13 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-431';

-- INV-0092: Natalia Aranda 6621718828 Paseo del Algodon #701, col. san javier → matched imp-v2-quo-610
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0092', 'Natalia Aranda 6621718828 Paseo del Algodon #701, col. san javier', 'imp-v2-vta-inv-0092', 'cabfd926a2e39b803246c6b1ef05e28751896e61fa00012cd8261abcd0dd3e19', 16000, 16000, 0, 'pagada', '2025-10-09 12:00:00', '2025-10-09 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-610';

-- INV-0091: Guadalupe villa FACUNDO BERNAL 1473 6621807994 → matched imp-v2-quo-579
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0091', 'Guadalupe villa FACUNDO BERNAL 1473 6621807994', 'imp-v2-vta-inv-0091', '73333800d6e388bc342963c29bfc0247110d83a0294db3ffa815c3cb80d0cf45', 16000, 16000, 0, 'pagada', '2025-09-29 12:00:00', '2025-09-29 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-579';

-- INV-0090: Alma Rodríguez 6621741957 → matched imp-v2-quo-569
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0090', 'Alma Rodríguez 6621741957', 'imp-v2-vta-inv-0090', '9f940d75e242d655b79480bd1496c674429c9c332e03659d33bd4468feae7b4a', 43900, 43900, 0, 'pagada', '2025-09-20 12:00:00', '2025-09-20 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-569';

-- INV-0089: Miguel Ángel Sánchez Cerrada Realeza 31, Paseo Real Residencial 6441118340 → matched imp-v2-quo-538
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0089', 'Miguel Ángel Sánchez Cerrada Realeza 31, Paseo Real Residencial 6441118340', 'imp-v2-vta-inv-0089', '7ba2360e8427edfd663daa198ceb4cb82bd627766126d52dfdc87492eba0da5f', 32000, 32000, 0, 'pagada', '2025-09-19 12:00:00', '2025-09-19 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-538';

-- INV-0088: Noé Oloño 6621110224 Bacanora #58 → matched imp-v2-quo-564
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0088', 'Noé Oloño 6621110224 Bacanora #58', 'imp-v2-vta-inv-0088', 'b8e22c5647e21ff5b7bb4356c82c5bd5c9e7bf8fe48b8f80110f9f8ecff17132', 20500, 20500, 0, 'pagada', '2025-09-18 12:00:00', '2025-09-18 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-564';

-- INV-0087: Alfredo Cabrera Acacia Florida 56, colonia Acacia Alicante 6626303800 → matched imp-v2-quo-563
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0087', 'Alfredo Cabrera Acacia Florida 56, colonia Acacia Alicante 6626303800', 'imp-v2-vta-inv-0087', 'eb17195ca4c7d518102537035abb40a46f0f53ea322a96506376bf13b870da3f', 15000, 15000, 0, 'pagada', '2025-09-16 12:00:00', '2025-09-16 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-563';

-- INV-0085: Aracely Romero Avenida 5 #218 6624307579 – REC1 → NO MATCH (venta sin cotización)
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) VALUES (2, NULL, 4, 2, 'INV-0085', 'Aracely Romero Avenida 5 #218 6624307579 – REC1', 'imp-v2-vta-inv-0085', 'd33e758769e159a984398355e392ffd16ece0a7d55d03ae2c65741d1167fa6f0', 21000, 21000, 0, 'pagada', '2025-09-09 12:00:00', '2025-09-09 12:00:00');

-- INV-0084: Aracely Romero Avenida 5 218 6624307579 – ppal → NO MATCH (venta sin cotización)
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) VALUES (2, NULL, 4, 2, 'INV-0084', 'Aracely Romero Avenida 5 218 6624307579 – ppal', 'imp-v2-vta-inv-0084', '0b3252729037d17f7bf8f8a1181a512356e145630b990f8ec206c4375ecd5588', 21300, 21300, 0, 'pagada', '2025-09-08 12:00:00', '2025-09-08 12:00:00');

-- INV-0083: Aracely Romero Avenida 5 #218 6624307579 – PRNCPLWLKIN → NO MATCH (venta sin cotización)
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) VALUES (2, NULL, 4, 2, 'INV-0083', 'Aracely Romero Avenida 5 #218 6624307579 – PRNCPLWLKIN', 'imp-v2-vta-inv-0083', 'd56e90959ac58c140dc6874830af3fe29bc27d1185e14605d7e9c17521bcc6c8', 27500, 27500, 0, 'pagada', '2025-09-08 12:00:00', '2025-09-08 12:00:00');

-- INV-0080: Jesús Ibarra Casarez calle Bisenzio 13 col stanza solare → matched imp-v2-quo-550
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0080', 'Jesús Ibarra Casarez calle Bisenzio 13 col stanza solare', 'imp-v2-vta-inv-0080', '00d903cc069340dca81a3d34584a9c8f9711fe12a8005e66c6b6e9e5414ee1f6', 15000, 15000, 0, 'pagada', '2025-09-06 12:00:00', '2025-09-06 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-550';

-- INV-0077: Angel luna Aguatinta 31 – 6682054332 → matched imp-v2-quo-541
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0077', 'Angel luna Aguatinta 31 – 6682054332', 'imp-v2-vta-inv-0077', 'f46fb9053abc275c9e2b394cf4894affb9f70510e417dfec8ec17f8cc2a35e27', 16000, 16000, 0, 'pagada', '2025-09-01 12:00:00', '2025-09-01 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-541';

-- INV-0076: Guadalupe villa 6621807994 → matched imp-v2-quo-515
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0076', 'Guadalupe villa 6621807994', 'imp-v2-vta-inv-0076', '0c1a846b89baf29ef46dba2ace97a1f59fec4dc5959a0221d4379ff0cb068083', 16000, 16000, 0, 'pagada', '2025-08-23 12:00:00', '2025-08-23 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-515';

-- INV-0075: Juan Carlos Ureta Sánchez  Gordelina #46 Fracc. Urbi Villas del Cedro  6731007973 → matched imp-v2-quo-525
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0075', 'Juan Carlos Ureta Sánchez  Gordelina #46 Fracc. Urbi Villas del Cedro  6731007973', 'imp-v2-vta-inv-0075', 'a5f0d965382c91028a999fe2e82fd60a7d1f4e39c7231f77f9ea9c769294b43e', 4500, 4500, 0, 'pagada', '2025-08-23 12:00:00', '2025-08-23 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-525';

-- INV-0073: Hibisco #72, Floresta Villa ciruelos → matched imp-v2-quo-499
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0073', 'Hibisco #72, Floresta Villa ciruelos', 'imp-v2-vta-inv-0073', 'a8a6af85eb023bf9162f846bdcce9ddaaa52f2cdb6f999903ecee20c74edb1c3', 22800, 22800, 0, 'pagada', '2025-08-14 12:00:00', '2025-08-14 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-499';

-- INV-0072: Rosa María Martínez → matched imp-v2-quo-498
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0072', 'Rosa María Martínez', 'imp-v2-vta-inv-0072', '52b6251ec326c83928dd7954336bb269526c06d2df500f43abed2bfc6092d276', 16000, 16000, 0, 'pagada', '2025-08-13 12:00:00', '2025-08-13 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-498';

-- INV-0071: Carlos Iván Rojas – Luesia 4 stanza Torralba 2 – 6442000471 → matched imp-v2-quo-407
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0071', 'Carlos Iván Rojas – Luesia 4 stanza Torralba 2 – 6442000471', 'imp-v2-vta-inv-0071', '34e10a2a3ea65782b36b75ab992d00d30df1274c9de2cc2c38eed9910dbb260c', 17100, 17100, 0, 'pagada', '2025-08-08 12:00:00', '2025-08-08 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-407';

-- INV-0069: Edgar Alonso Melgoza González Poder legislativo 418, colonia ley 57 6623375495 → matched imp-v2-quo-466
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0069', 'Edgar Alonso Melgoza González Poder legislativo 418, colonia ley 57 6623375495', 'imp-v2-vta-inv-0069', '3492fc32ae1e54fa00b50497589264e6cb26958a1d336456e36dcf82ed50511e', 15000, 15000, 0, 'pagada', '2025-07-30 12:00:00', '2025-07-30 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-466';

-- INV-0068: Lupita López – 6623493745 – Renato Girón #58 → matched imp-v2-quo-426
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0068', 'Lupita López – 6623493745 – Renato Girón #58', 'imp-v2-vta-inv-0068', '9a7bdf7746b6e660b5dc4994a021e578ff2efe09c5d93d42b35f4fd7afe9ceaa', 16300, 16300, 0, 'pagada', '2025-07-10 12:00:00', '2025-07-10 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-426';

-- INV-0067: Bahia Kino 24, Miramar, Guaymas – Muebles WC → matched imp-v2-quo-425
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0067', 'Bahia Kino 24, Miramar, Guaymas – Muebles WC', 'imp-v2-vta-inv-0067', '8d8e6949fd5f2f1e78bbbc686bd6000eac3958f5dcb544457d44dea2c4b61713', 20000, 20000, 0, 'pagada', '2025-07-07 12:00:00', '2025-07-07 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-425';

-- INV-0066: Paloma Miranda – Mérida 1447 col nueva Palmira – 6621505501 → matched imp-v2-quo-403
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0066', 'Paloma Miranda – Mérida 1447 col nueva Palmira – 6621505501', 'imp-v2-vta-inv-0066', '59a9dea81204ce3183f9cb6089b70ff680ddc333bd488d49fadd59ae737dcf6f', 17500, 17500, 0, 'pagada', '2025-07-01 12:00:00', '2025-07-01 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-403';

-- INV-0065: Cruz Delia molino de camou # 127 entre Israel Gonzales col. Insurgentes → matched imp-v2-quo-415
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0065', 'Cruz Delia molino de camou # 127 entre Israel Gonzales col. Insurgentes', 'imp-v2-vta-inv-0065', '3660c09d44f9665d8653f470dca052343a0b70b57efdf186c9f357714d15cc1b', 12000, 12000, 0, 'pagada', '2025-06-27 12:00:00', '2025-06-27 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-415';

-- INV-0064: Rodolfo León Montecarlo, san servan #28  6311574113 → matched imp-v2-quo-412
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0064', 'Rodolfo León Montecarlo, san servan #28  6311574113', 'imp-v2-vta-inv-0064', '356aaf1c39dfb8c3fb5b39bf3a08366a969628cd8b0893d6b745077e635979e0', 27000, 27000, 0, 'pagada', '2025-06-24 12:00:00', '2025-06-24 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-412';

-- INV-0063: Jonathan salido Moroyoqui – Stanza torralba etapa 3, Retorno Guadalquivir -10 6442603032 → matched imp-v2-quo-405
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0063', 'Jonathan salido Moroyoqui – Stanza torralba etapa 3, Retorno Guadalquivir -10 6442603032', 'imp-v2-vta-inv-0063', 'ed4ff34d1ecee30e0e9339216fde477949b9be385a91f256c99203c73e2bf200', 15000, 15000, 0, 'pagada', '2025-06-16 12:00:00', '2025-06-16 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-405';

-- INV-0062: Navarra 24 puerta real etapa 1 → matched imp-v2-quo-395
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0062', 'Navarra 24 puerta real etapa 1', 'imp-v2-vta-inv-0062', 'a76948a290b753d597e94d3384fdecd7d73e4657869bb46e4938ee420b06ab00', 16900, 16900, 0, 'pagada', '2025-06-12 12:00:00', '2025-06-12 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-395';

-- INV-0061: Guasave 68 col. Emiliano zapata – 6622331858 → matched imp-v2-quo-400
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0061', 'Guasave 68 col. Emiliano zapata – 6622331858', 'imp-v2-vta-inv-0061', 'b616956c49ab68e20029fa15a0413ed0c7443200353be41b306fcbe38b6dc311', 18900, 18900, 0, 'pagada', '2025-06-11 12:00:00', '2025-06-11 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-400';

-- INV-0060: primera privada de bugambilias 55 entre calle uno y dos → matched imp-v2-quo-399
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0060', 'primera privada de bugambilias 55 entre calle uno y dos', 'imp-v2-vta-inv-0060', 'fb06e73944405b067a00adb3722adb0c1a3ac219954d592902df06f594c19082', 15900, 15900, 0, 'pagada', '2025-05-31 12:00:00', '2025-05-31 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-399';

-- INV-0059: Maritza Ávila – Árbol de alcanfor 14 – 6453315190 → matched imp-v2-quo-396
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0059', 'Maritza Ávila – Árbol de alcanfor 14 – 6453315190', 'imp-v2-vta-inv-0059', '8e773deb232826ff815e0aea84ede997e9e4aca48a3478a49682edb2fc93a133', 17800, 17800, 0, 'pagada', '2025-05-27 12:00:00', '2025-05-27 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-396';

-- INV-0058: Brenda – Espiridion Ahumada 107 villa de seris – 6623775944 → matched imp-v2-quo-391
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0058', 'Brenda – Espiridion Ahumada 107 villa de seris – 6623775944', 'imp-v2-vta-inv-0058', '462b628151544935347b1c257677484670a87ac1ee5d2a112697b82d6b39d027', 16500, 16500, 0, 'pagada', '2025-05-26 12:00:00', '2025-05-26 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-391';

-- INV-0057: Carlos Iván Rojas – Luesia 4 stanza Torralba 2  – 6442000471 → matched imp-v2-quo-388
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0057', 'Carlos Iván Rojas – Luesia 4 stanza Torralba 2  – 6442000471', 'imp-v2-vta-inv-0057', '706b486fae966246bbca41e7edfce5c4efa2deb5324221d76317f1449c5cfeec', 28800, 28800, 0, 'pagada', '2025-05-23 12:00:00', '2025-05-23 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-388';

-- INV-0056: Bahía de kino n.24, Vista miramar , Guaymas, Son → matched imp-v2-quo-385
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0056', 'Bahía de kino n.24, Vista miramar , Guaymas, Son', 'imp-v2-vta-inv-0056', '23ec32e0ca763dd5d0d8194bbe20540da68abb3d0a1a11962202536f37e10880', 120000, 120000, 0, 'pagada', '2025-05-23 12:00:00', '2025-05-23 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-385';

-- INV-0055: Alfonso Cisneros – Real del 14 – 6621245997 → matched imp-v2-quo-387
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0055', 'Alfonso Cisneros – Real del 14 – 6621245997', 'imp-v2-vta-inv-0055', 'f0167d70a62a4fe89a75e3a5e075a5796b3b6997671353e8013a07d23ebcde10', 29800, 29800, 0, 'pagada', '2025-05-20 12:00:00', '2025-05-20 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-387';

-- INV-0054: Erika lopez – Villa Azueta 196 villas del sur – 6624754155 → matched imp-v2-quo-382
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0054', 'Erika lopez – Villa Azueta 196 villas del sur – 6624754155', 'imp-v2-vta-inv-0054', 'f8d2146f13f5fb76dc032e27cba80bf3a54c8824dfe1e11686552fbe99fc94d5', 15000, 15000, 0, 'pagada', '2025-05-19 12:00:00', '2025-05-19 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-382';

-- INV-0053: Costa de Marfil 60 – Closet → matched imp-v2-quo-386
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0053', 'Costa de Marfil 60 – Closet', 'imp-v2-vta-inv-0053', '63a49c3446d50f462fd55ba3a61b47f6a5a11c9f04886324fce5d75fdc040a83', 11500, 11500, 0, 'pagada', '2025-05-19 12:00:00', '2025-05-19 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-386';

-- INV-0052: Consuelo Bustamante Nieblas – Crevillente 23, Montesinos secc. crevillente → matched imp-v2-quo-366
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0052', 'Consuelo Bustamante Nieblas – Crevillente 23, Montesinos secc. crevillente', 'imp-v2-vta-inv-0052', 'e756b110468aa5f07564672faac4e860ecbed921441efaf986cf50ef6bbd3d6b', 17000, 17000, 0, 'pagada', '2025-04-14 12:00:00', '2025-04-14 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-366';

-- INV-0051: Leticia Maytorena fraccionamiento esplendor 1 → matched imp-v2-quo-324
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0051', 'Leticia Maytorena fraccionamiento esplendor 1', 'imp-v2-vta-inv-0051', '6c950bf2d2444efaed337a00de7f56ca67184918a638b40904eec9ed79ea256f', 18000, 18000, 0, 'pagada', '2025-04-01 12:00:00', '2025-04-01 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-324';

-- INV-0049: Nerva 24, Real de Sevilla → matched imp-v2-quo-335
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0049', 'Nerva 24, Real de Sevilla', 'imp-v2-vta-inv-0049', 'eae26a7edb089208ae49606b11408c96d5d360a851a2fc3277b3d101d3b07177', 18900, 18900, 0, 'pagada', '2025-03-07 12:00:00', '2025-03-07 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-335';

-- INV-0048: cerrada del marquez, col paseo real → matched imp-v2-quo-336
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0048', 'cerrada del marquez, col paseo real', 'imp-v2-vta-inv-0048', 'f57c1268d1610a27d8ad11a9df8ffb82861120f97fb8ef6ca5f5bf75fb0417bf', 30600, 30600, 0, 'pagada', '2025-02-26 12:00:00', '2025-02-26 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-336';

-- INV-0047: Maria yolanda santoyo – San pedro de la conquista 29, piedra bola → matched imp-v2-quo-329
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0047', 'Maria yolanda santoyo – San pedro de la conquista 29, piedra bola', 'imp-v2-vta-inv-0047', '2476bede0838219199ee9f234d159c19d33227216d2bddb81bfc3129e4a15da5', 17500, 17500, 0, 'pagada', '2025-02-17 12:00:00', '2025-02-17 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-329';

-- INV-0046: Gabriela urias – Dr villa robledo 27, villa sol → matched imp-v2-quo-318
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0046', 'Gabriela urias – Dr villa robledo 27, villa sol', 'imp-v2-vta-inv-0046', '2f967ace82ecbccce5e87076b8a60ded587be4a5ace5dca760891bb32757fe11', 17500, 17500, 0, 'pagada', '2025-02-01 12:00:00', '2025-02-01 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-318';

-- INV-0044: Daniel Aello Blvd. JARDA 84 VILLA BONITA → matched imp-v2-quo-307
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0044', 'Daniel Aello Blvd. JARDA 84 VILLA BONITA', 'imp-v2-vta-inv-0044', 'dea06e47811a4d31d1ead68e7072c5e6d4797a7d4c26bbda4ef73589f6169706', 22500, 22500, 0, 'pagada', '2025-01-09 12:00:00', '2025-01-09 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-307';

-- INV-0043: Angelica Gocobachi – Bonete 9 Monte de Calabria → matched imp-v2-quo-280
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0043', 'Angelica Gocobachi – Bonete 9 Monte de Calabria', 'imp-v2-vta-inv-0043', 'fa1cc1c9dc68446aadf906207b5e30b35f6fd74690c8d28d0b1a9e58bce15ed7', 28500, 28500, 0, 'pagada', '2025-01-09 12:00:00', '2025-01-09 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-280';

-- INV-0042: Carla Valadez – Llanura costera 77, Ondaterra, Banus → matched imp-v2-quo-271
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0042', 'Carla Valadez – Llanura costera 77, Ondaterra, Banus', 'imp-v2-vta-inv-0042', 'ed05732157c03cc7150ab7750a8e9cc869cdff0141d6f11b1a9c9639170060c4', 16000, 16000, 0, 'pagada', '2025-01-06 12:00:00', '2025-01-06 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-271';

-- INV-0041: Carla Valadez – Llanura costera 77, Ondaterra, Banus → matched imp-v2-quo-271
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0041', 'Carla Valadez – Llanura costera 77, Ondaterra, Banus', 'imp-v2-vta-inv-0041', 'e109b85eef83e59a9bda4c6fb5567a62b4387e8963258f2aa3312dd7cd88b5fb', 8000, 8000, 0, 'pagada', '2025-01-06 12:00:00', '2025-01-06 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-271';

-- INV-0040: Manuel Alvarez – Jesus Siqueiros 1471, Buena Vista → matched imp-v2-quo-291
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0040', 'Manuel Alvarez – Jesus Siqueiros 1471, Buena Vista', 'imp-v2-vta-inv-0040', '825b89681e151814c7ea2b71f5281e01d5db4b420e15e7bc62d5222d942dda22', 17800, 17800, 0, 'pagada', '2024-12-27 12:00:00', '2024-12-27 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-291';

-- INV-0039: EMMA GUADALUPE RODRIGUEZ MOLINA – VICAM NO. 1, MODELO. → matched imp-v2-quo-281
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0039', 'EMMA GUADALUPE RODRIGUEZ MOLINA – VICAM NO. 1, MODELO.', 'imp-v2-vta-inv-0039', 'd1873ae34e8ff5f8efef0bcb1ea0864a8b96f221e774913daa60ce045b7b8c42', 11000, 11000, 0, 'pagada', '2024-12-06 12:00:00', '2024-12-06 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-281';

-- INV-0038: Noe corrales Gorostegui  – Cabo San Antonio 595 colonia dunas → matched imp-v2-quo-285
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0038', 'Noe corrales Gorostegui  – Cabo San Antonio 595 colonia dunas', 'imp-v2-vta-inv-0038', '0d6ab69af2b964472f5b2d31b20369849404997a0e01456b33adcf1fe167a26c', 23500, 23500, 0, 'pagada', '2024-12-05 12:00:00', '2024-12-05 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-285';

-- INV-0037: Carla Valadez – Llanura costera 77, Ondaterra, Banus → matched imp-v2-quo-271
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0037', 'Carla Valadez – Llanura costera 77, Ondaterra, Banus', 'imp-v2-vta-inv-0037', '94856238c501b6fb877dee5b82f4f37f6926ab73880a5fe6b4dcae33216b2697', 19000, 19000, 0, 'pagada', '2024-11-27 12:00:00', '2024-11-27 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-271';

-- INV-0036: William Edgardo Fimbres Morales – Colinas 16, San Francisco Valle recidencial, cerrada Balboa → matched imp-v2-quo-273
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0036', 'William Edgardo Fimbres Morales – Colinas 16, San Francisco Valle recidencial, cerrada Balboa', 'imp-v2-vta-inv-0036', '41f68b9d7ddc4165f0abbf5b0a44831e4df1e39af137eec78bb608c676fcd571', 25000, 25000, 0, 'pagada', '2024-11-25 12:00:00', '2024-11-25 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-273';

-- INV-0035: Yaneth Romero – Cerrada Kikapu 33, Pueblitos → matched imp-v2-quo-272
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0035', 'Yaneth Romero – Cerrada Kikapu 33, Pueblitos', 'imp-v2-vta-inv-0035', '206f60d3c9404d1c90d2678308076903c47138fa5e5c36e4b8c96f4cbab70e00', 16000, 16000, 0, 'pagada', '2024-11-23 12:00:00', '2024-11-23 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-272';

-- INV-0033: Circuito del encinar oriente 32, La Campiña Sección Girasoles → matched imp-v2-quo-263
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0033', 'Circuito del encinar oriente 32, La Campiña Sección Girasoles', 'imp-v2-vta-inv-0033', 'e26fd5dc12eca4bc95dfb063db40c89096d9564915c49f882e0423a455f01042', 15500, 15500, 0, 'pagada', '2024-11-08 12:00:00', '2024-11-08 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-263';

-- INV-0032: Leonor Rodriguez – San Simon 53, Paseo San Angel → matched imp-v2-quo-261
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0032', 'Leonor Rodriguez – San Simon 53, Paseo San Angel', 'imp-v2-vta-inv-0032', '40d68c00b815c68c019a1b9ecaa1c376a44c86dbc7129544dc6f08748a93a54d', 25000, 25000, 0, 'pagada', '2024-11-06 12:00:00', '2024-11-06 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-261';

-- INV-0031: Susana Ruiz – Costa de Marfil → matched imp-v2-quo-259
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0031', 'Susana Ruiz – Costa de Marfil', 'imp-v2-vta-inv-0031', '279bf610b0986dcbe5f7d4cbd34432325f5d7a69cf831b12c2d34795cb33b02e', 17500, 17500, 0, 'pagada', '2024-11-06 12:00:00', '2024-11-06 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-259';

-- INV-0030: Circuito del Misterio #84 → matched imp-v2-quo-185
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0030', 'Circuito del Misterio #84', 'imp-v2-vta-inv-0030', '5d0b88a2ca842571fd606a15de5c8b2a472fa1abf36c5a1528a95a0fe50c1fb0', 92500, 92500, 0, 'pagada', '2024-10-22 12:00:00', '2024-10-22 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';

-- INV-0029: BEATRIZ ADRIANA ENCINAS – BENITO JUAREZ 393, VILLA HERMOSA → matched imp-v2-quo-200
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0029', 'BEATRIZ ADRIANA ENCINAS – BENITO JUAREZ 393, VILLA HERMOSA', 'imp-v2-vta-inv-0029', '4c545611900222eb72825e393c2d997c0c448b1189584da0fd5fa39813e2aa7b', 17500, 17500, 0, 'pagada', '2024-10-14 12:00:00', '2024-10-14 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-200';

-- INV-0028: Renato Leduc 7 Los Encinos 2 Etapa 2 → matched imp-v2-quo-162
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0028', 'Renato Leduc 7 Los Encinos 2 Etapa 2', 'imp-v2-vta-inv-0028', 'a236d093ae17bc91a36312622d8c90aa39a0d5ddd596b1ba3fdf469aab4f8f7d', 42500, 42500, 0, 'pagada', '2024-09-17 12:00:00', '2024-09-17 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-162';

-- INV-0027: Orissa oriente 3, Tosali residencial → matched imp-v2-quo-219
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0027', 'Orissa oriente 3, Tosali residencial', 'imp-v2-vta-inv-0027', '4d95c90312d788a92bdb4c2aac1c4ccd1dabe2468e03be3e4fb171a6b11b5e47', 24500, 24500, 0, 'pagada', '2024-09-11 12:00:00', '2024-09-11 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-219';

-- INV-0026: Geovana Romandia – Abuyacab 5, Puerta Real, 7ma etapa → matched imp-v2-quo-186
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0026', 'Geovana Romandia – Abuyacab 5, Puerta Real, 7ma etapa', 'imp-v2-vta-inv-0026', '0e090cee9320a8826adb58426e60e862fbc5cd8f381f10ec3adf4e0602dd176f', 17500, 17500, 0, 'pagada', '2024-08-16 12:00:00', '2024-08-16 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-186';

-- INV-0025: Nelva Martínez – Puerta Real → matched imp-v2-quo-195
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0025', 'Nelva Martínez – Puerta Real', 'imp-v2-vta-inv-0025', 'fe0cfcb623bfb6a4a66be09b02e6204278200de89a91291e7d8fcd5fb09779da', 38000, 38000, 0, 'pagada', '2024-08-12 12:00:00', '2024-08-12 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-195';

-- INV-0024: Circuito del Misterio #84 → matched imp-v2-quo-185
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0024', 'Circuito del Misterio #84', 'imp-v2-vta-inv-0024', '7d3fd1238f246449e53ff97e9d0e4dbf8f299532d7216b35e11ee9f12faa943d', 57500, 57500, 0, 'pagada', '2024-08-06 12:00:00', '2024-08-06 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-185';

-- INV-0022: Almar 29, La Coruna → NO MATCH (venta sin cotización)
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) VALUES (2, NULL, 4, 2, 'INV-0022', 'Almar 29, La Coruna', 'imp-v2-vta-inv-0022', '21c3dc26a1effc202761960d6e1a4487d4d78716934d881259112a00f453f495', 30000, 30000, 0, 'pagada', '2024-07-27 12:00:00', '2024-07-27 12:00:00');

-- INV-0021: Silvia Acosta – Olivares #286 col las granjas → matched imp-v2-quo-180
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0021', 'Silvia Acosta – Olivares #286 col las granjas', 'imp-v2-vta-inv-0021', 'e4c21c183e2d5e859d6f4c2ba7a2cc8126b74ab485fc1bd66880890e5fa5e6dd', 14500, 14500, 0, 'pagada', '2024-08-26 12:00:00', '2024-08-26 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-180';

-- INV-0020: Benito juarez 393, Col villa hermosa → matched imp-v2-quo-174
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0020', 'Benito juarez 393, Col villa hermosa', 'imp-v2-vta-inv-0020', '220998581e25eedab1aac0262ac9241f6a94d790f2073871552d8520b772b33a', 18500, 18500, 0, 'pagada', '2024-07-22 12:00:00', '2024-07-22 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-174';

-- INV-0019: Alma castro – Etchojoa 1070 Misión del arco → matched imp-v2-quo-178
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0019', 'Alma castro – Etchojoa 1070 Misión del arco', 'imp-v2-vta-inv-0019', 'bc39296be762430ff70c65ea8ace6010626ec7f0fa6f13413785b62408b82398', 45500, 45500, 0, 'pagada', '2024-07-21 12:00:00', '2024-07-21 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-178';

-- INV-0018: Cosala 56, Emiliano Zapata → matched imp-v2-quo-117
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0018', 'Cosala 56, Emiliano Zapata', 'imp-v2-vta-inv-0018', '2de48611165376757045ea2f6771d118b07982248d6c4c6338b6f41da714c6e5', 32800, 32800, 0, 'pagada', '2024-07-18 12:00:00', '2024-07-18 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-117';

-- INV-0017: Paseo san isidro 14 col paseo san Angel – copy – copy → matched imp-v2-quo-169
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0017', 'Paseo san isidro 14 col paseo san Angel – copy – copy', 'imp-v2-vta-inv-0017', 'b5208caa625dd571a20189d966bac77751a99ce4bfd7b550fa5ad942ef6b8e31', 18400, 18400, 0, 'pagada', '2024-07-16 12:00:00', '2024-07-16 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-169';

-- INV-0016: Palma Takil 9 → matched imp-v2-quo-156
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0016', 'Palma Takil 9', 'imp-v2-vta-inv-0016', '457f6a594c40fb40d9e165346a890926d1ae2aa346e708959adf1fcbe870c6d6', 39500, 39500, 0, 'pagada', '2024-06-28 12:00:00', '2024-06-28 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-156';

-- INV-0015: Tonatiuh #295 col. Valle del marquez → matched imp-v2-quo-122
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0015', 'Tonatiuh #295 col. Valle del marquez', 'imp-v2-vta-inv-0015', 'ef00256d08e522c53559a3d3caca47a422b0df73dbd6b57245765566523aff12', 25000, 25000, 0, 'pagada', '2024-05-28 12:00:00', '2024-05-28 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-122';

-- INV-0014: Roseta 2, Aurea → matched imp-v2-quo-114
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0014', 'Roseta 2, Aurea', 'imp-v2-vta-inv-0014', '2520d60e44f9dae5327feea4d2eda9a027a4dbfe8ee40f044f8e96489680dd90', 152300, 152300, 0, 'pagada', '2024-04-30 12:00:00', '2024-04-30 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-114';

-- INV-0013: Privada Rocarraso 4, Villa Bonita → matched imp-v2-quo-118
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) SELECT 2, c.id, 4, 2, 'INV-0013', 'Privada Rocarraso 4, Villa Bonita', 'imp-v2-vta-inv-0013', '887ee576ddca3e472ecac2733a489c59dfc279613e69b4fce7faa4636fa5c4a1', 27300, 27300, 0, 'pagada', '2024-04-30 12:00:00', '2024-04-30 12:00:00' FROM cotizaciones c WHERE c.empresa_id = 2 AND c.slug = 'imp-v2-quo-118';

-- INV-0012: Ana Maria Puerta Real Closet 2 → NO MATCH (venta sin cotización)
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) VALUES (2, NULL, 4, 2, 'INV-0012', 'Ana Maria Puerta Real Closet 2', 'imp-v2-vta-inv-0012', '7f11d0b30efc11f5f0b26b74230a185ceca431c5d0d7490cbd8ba3401b6bfc7a', 15000, 15000, 0, 'pagada', '2024-03-22 12:00:00', '2024-03-22 12:00:00');

-- INV-0011: Chilpancingo 1324 → NO MATCH (venta sin cotización)
INSERT IGNORE INTO ventas (empresa_id, cotizacion_id, cliente_id, usuario_id, numero, titulo, slug, token, total, pagado, saldo, estado, created_at, updated_at) VALUES (2, NULL, 4, 2, 'INV-0011', 'Chilpancingo 1324', 'imp-v2-vta-inv-0011', '7d1e43847f67cbc4ecbf7830e78cab1ce7b0af962125caafeec12e8e18d67606', 18000, 18000, 0, 'pagada', '2024-03-20 12:00:00', '2024-03-20 12:00:00');


-- Mark matched cotizaciones as 'convertida'
UPDATE cotizaciones c
INNER JOIN ventas v ON v.cotizacion_id = c.id
SET c.estado = 'convertida', c.updated_at = NOW()
WHERE c.empresa_id = 2 AND c.slug LIKE 'imp-v2-%' AND v.slug LIKE 'imp-v2-vta-%';

-- Link cotizacion_lineas to ventas
UPDATE cotizacion_lineas cl
INNER JOIN ventas v ON v.cotizacion_id = cl.cotizacion_id
SET cl.venta_id = v.id
WHERE v.empresa_id = 2 AND v.slug LIKE 'imp-v2-vta-%' AND cl.venta_id IS NULL;

-- Update folio counters
INSERT INTO folios (empresa_id, tipo, anio, ultimo)
SELECT 2, 'VTA', YEAR(NOW()),
       (SELECT COUNT(*) FROM ventas WHERE empresa_id = 2)
ON DUPLICATE KEY UPDATE ultimo = GREATEST(ultimo,
       (SELECT COUNT(*) FROM ventas WHERE empresa_id = 2));

-- ════════════════════════════════════════════════════════════
-- VERIFICATION
-- ════════════════════════════════════════════════════════════
SELECT 'cotizaciones' AS tabla, COUNT(*) AS total FROM cotizaciones WHERE empresa_id = 2
UNION ALL SELECT 'ventas', COUNT(*) FROM ventas WHERE empresa_id = 2
UNION ALL SELECT 'lineas', COUNT(*) FROM cotizacion_lineas cl INNER JOIN cotizaciones c ON c.id = cl.cotizacion_id WHERE c.empresa_id = 2;

-- Ventas de marzo 2026
SELECT v.numero, v.titulo, v.total, v.estado, v.created_at
FROM ventas v WHERE v.empresa_id = 2
  AND v.created_at BETWEEN '2026-03-01' AND '2026-03-31 23:59:59'
ORDER BY v.created_at DESC;

SET FOREIGN_KEY_CHECKS = 1;
