# Metas como dato fijo de CotizaCloud AI: diseño final

> **Premisa.** Las cuatro metas son de la **empresa**, no de cada asesor: punto de equilibrio, meta pesimista, meta optimista y tasa de conversión deseada. Por eso, todo lo que un asesor lea sobre metas se escribe **en tercera persona y con "la empresa" como sujeto**. Por ejemplo: *"La empresa va baja en estos 3 días"* o *"La empresa ni siquiera llega al punto de equilibrio en estos 3 días de septiembre"*. Nunca se le dice "vas bajo". Decirle "vas" a alguien sobre algo que no controla él solo lo convierte en un juicio personal. Ya pasó una vez con "ESTABLE" (`ScoreLectura.php:399-405`).
>
> **Alcance.** Solo módulos de la SaaS. Quedan fuera `modules/superadmin/executive.php`, `modules/supervisor/*` (su alcance sale de `data/supervisores.json`, `supervisor/index.php:19-20`, y su contrato dice *"CERO CAMBIOS AL SISTEMA"*, `:9-11`) y todo `data/`.
>
> Cada cita archivo:línea la verifiqué en el código. Lo que no verifiqué lo marco como **(sin verificar)**.

---

## 1. Qué captura la empresa y dónde

### Dónde
Una pestaña nueva **"Metas"** en `modules/config/index.php`:
- Se agrega `'metas'` a la lista blanca de pestañas (`:12`).
- El enlace va en el bloque que solo ve Business (`:373-377`).
- La pantalla la ve y la edita **solo el admin**, con `Auth::es_admin()` y Business.

### Qué se captura
| Dato | Frecuencia | Dónde se guarda |
|---|---|---|
| Punto de equilibrio | por mes | `empresa_metas_mes.equilibrio` |
| Meta pesimista | por mes | `empresa_metas_mes.meta_pesimista` |
| Meta optimista | por mes | `empresa_metas_mes.meta_optimista` |
| Moneda de la captura | por mes | `empresa_metas_mes.moneda` (se copia de `empresas.moneda` al guardar) |
| Tasa de conversión deseada | fija | `empresas.tasa_conv_meta` + `tasa_conv_meta_desde` |

La pantalla es una rejilla de 12 meses: el actual, los 5 anteriores y los 6 siguientes. Así la estacionalidad se captura de una sola vez.

### Validaciones (endpoint nuevo `modules/config/guardar_metas.php`)
- Lleva `csrf_check()` y exige `Auth::es_admin()` y Business.
- Los tres montos se capturan juntos o ninguno. Los tres deben ser > 0.
- **Solo se exige `pesimista ≤ optimista`.** El equilibrio es independiente. Una empresa puede declarar a propósito un mes de pérdida (pesimista < equilibrio) y hoy no hay razón para impedírselo. Los niveles de la §3 están definidos para ese caso. Se confirma con el CEO en §10.3.
- La tasa deseada va **entre 3 y 90 %**, que es el rango real del motor: piso de 3 % en `ActividadScore.php:1848-1849` (`max(..., 0.03)`) y techo de 90 % en `:1803` y `:1827` (`min(..., 0.90)`).
- Bajo el campo de la tasa, el texto literal: *"De cada 100 cotizaciones que envías, cuántas quieres vender."*

### Herencia
Si un mes no tiene fila, hereda la del último mes capturado antes que él:

```sql
WHERE empresa_id=? AND (anio<? OR (anio=? AND mes<=?)) ORDER BY anio DESC, mes DESC LIMIT 1
```

Es el mismo patrón que usa `ActividadScore.php:1461-1462` con `historial_mensual`.

- La herencia **solo mira hacia atrás**. Un día anterior a la primera captura **no tiene meta**.
- Una meta heredada queda marcada `provisional`. **El admin** ve *"Metas de octubre heredadas de septiembre"*. **El asesor** no ve esa marca: el nivel se calcula igual.
- Si la fila vigente tiene una `moneda` distinta de `empresas.moneda`, esa ventana queda en `sin_metas`. El admin ve *"Tus metas están capturadas en MXN y la empresa ahora opera en USD: recaptúralas."*
- Si la empresa no tiene ninguna fila, el estado es `sin_metas` y **no se enciende ningún texto de metas en ninguna pantalla**.
- Si la empresa baja de plan, **no se borra nada**: la pestaña se oculta y el estado pasa a `sin_metas`.

---

## 2. Una sola capa de datos

### Migración `migrations/add_empresa_metas.sql` (correr antes de desplegar)

```sql
CREATE TABLE IF NOT EXISTS empresa_metas_mes (
  empresa_id INT UNSIGNED NOT NULL,
  anio SMALLINT UNSIGNED NOT NULL,
  mes TINYINT UNSIGNED NOT NULL,
  equilibrio DECIMAL(14,2) NOT NULL,
  meta_pesimista DECIMAL(14,2) NOT NULL,
  meta_optimista DECIMAL(14,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'MXN',
  capturado_por INT UNSIGNED NULL,
  capturado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (empresa_id, anio, mes),
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE empresas
  ADD COLUMN tasa_conv_meta DECIMAL(5,2) NULL,
  ADD COLUMN tasa_conv_meta_desde DATETIME NULL;

CREATE TABLE IF NOT EXISTS empresa_metas_estado (   -- memoria de la histéresis
  empresa_id INT UNSIGNED NOT NULL,
  ventana ENUM('mes','d30') NOT NULL,
  periodo CHAR(7) NOT NULL,        -- 'YYYY-MM' en 'mes'; fecha de corte 'YYYY-MM' en 'd30'
  nivel VARCHAR(12) NOT NULL,
  cambiado_at DATETIME NOT NULL,
  PRIMARY KEY (empresa_id, ventana)
) ENGINE=InnoDB;
```

- Toma como modelo `historial_mensual`, pero **no la reutiliza**: esa tabla guarda lo que se vendió, no lo que se declaró.
- `tasa_conv_meta = NULL` significa "no declarada", que no es lo mismo que 0.
- **`periodo` en la memoria de histéresis.** Si el `periodo` guardado en la ventana `mes` es de otro mes, se trata como si no hubiera estado previo. Así el nivel de cierre de septiembre no se arrastra al 1 de octubre, y la alerta no se dispara falsa cada día 1.

### Clase `core/MetasEmpresa.php`, la única puerta de entrada
Se carga con `if (!class_exists('MetasEmpresa')) require_once __DIR__ . '/MetasEmpresa.php';`, igual que `RitmoReporte.php:314`. Toda la clase va dentro de `try/catch`: si la tabla no existe, devuelve `sin_metas` y registra `error_log('[Metas] …')` una sola vez.

| Función | Devuelve | Quién la usa |
|---|---|---|
| `MetasEmpresa::estado(int $e): array` | Todo con cifras: metas de cada ventana, vendido, `n`, faltante, `provisional`, `origen`, niveles, tasa real y deseada, N/M | Solo pantallas del admin |
| `MetasEmpresa::nivel(int $e): array` | **Solo etiquetas y datos de calendario**: `['mes'=>'abajo','d30'=>'muy_bajo','dias'=>3,'mes_nombre'=>'septiembre','corte'=>'2026-09-25','conv'=>'debajo\|en\|arriba\|gris']` | Todo lo que puede leer un asesor |
| `MetasEmpresa::frases(array $nivel): array` | Las frases ya redactadas de la §3 | Reporte, termómetro, tips |

- Las tres funciones guardan su resultado en memoria (`static $memo[$e]`), igual que `RitmoReporte::expediente`.
- **Qué cuenta como "cifra de meta":** montos (de meta, de vendido o de faltante), porcentajes, tasas y N/M. **Los días y las fechas NO cuentan como cifra de meta**: "3 días" o "25/Sep" son calendario, no revelan nada de la meta. `nivel()` y `frases()` nunca llevan cifras de meta. Por eso la regla de que el asesor no ve cifras queda garantizada en la capa de datos, no en cada plantilla.
- **La receta de plan vive aquí:** si `trial_info($e)['es_business']` es falso, `estado()` devuelve `sin_metas`.
- **Ticket propio:** `MetasEmpresa` calcula su propio ticket. **La Mesa conserva el suyo** (`Mesa.php:405-421`, que incluye DI y no exige `pagado > 0`). Si la Mesa leyera el de Metas cambiaría su umbral de monto, y con él el orden por calor → monto (decisión 7).

### Qué cuenta como "vendido" (decisión 2)
Una sola consulta para las dos ventanas, **con `?` posicionales**. La razón: `DB.php:28` fija `ATTR_EMULATE_PREPARES => false`, y con preparados nativos un marcador con nombre repetido lanza una excepción. Esa excepción se la tragaría el `try/catch` y dejaría a la empresa en `sin_metas` para siempre.

```sql
SELECT
  COALESCE(SUM(CASE WHEN v.created_at >= ? THEN v.total END),0) AS mes,   -- ini_mes
  COALESCE(SUM(v.created_at >= ?),0)                           AS n_mes,  -- ini_mes
  COALESCE(SUM(CASE WHEN v.created_at >= ? THEN v.total END),0) AS d30,   -- ini_30
  COALESCE(SUM(v.created_at >= ?),0)                           AS n_30    -- ini_30
FROM ventas v
WHERE v.empresa_id = ? AND v.estado != 'cancelada'
  AND v.pagado > 0 AND v.total > 0
  AND v.created_at >= ? AND v.created_at < ?      -- LEAST(ini_mes, ini_30), manana
  AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di
                  WHERE di.cotizacion_id = v.cotizacion_id AND di.estado='utilizado')
```

- `v.total > 0` alinea la consulta con `_mesa.php:363`.
- La exclusión del DI es el mismo patrón que `$no_di_v` de ActividadScore. **Va comentada en el código**: la vara de empresa del motor **sí** cuenta el DI (`ActividadScore.php:346-347`), y Metas lo excluye **por decisión del CEO**. Sin el comentario, la próxima persona lo "corrige".
- Los límites se calculan en PHP:
  - `ini_mes = date('Y-m-01 00:00:00')`
  - `ini_30 = date('Y-m-d 00:00:00', strtotime('-29 days'))` → 30 fechas contando hoy. Es la lección de RitmoCot, cuya ventana de "7 días" abarcaba 8.
  - `manana = date('Y-m-d 00:00:00', strtotime('+1 day'))`
- **Zona horaria (limitación aceptada):** se usa la global `APP_TIMEZONE` (`index.php:88-90`), no una por empresa. Para una empresa en otro huso, el corte del día se mueve una o dos horas.
- **Receta muerta.** `monto_mes_actual` (`ActividadScore.php:1493-1497`) no excluye el DI y nadie lo lee. **Se elimina junto con `dia_mes` y `dias_mes`.** No debe quedar una segunda receta de "vendido del mes".

**El vendido es "vivo" y cambia después. Así se muestra.** Hay cuatro formas en que cambia:
- La venta entra al total cuando llega su primer pago. Nace con `pagado=0` (`quote_action.php:262-265`), así que un anticipo de octubre puede subir septiembre.
- `ventas.total` cambia con extras, descuentos y guardados.
- Quitar el DI pasa la activación a `cancelado` y **sube** el total (`acciones.php:472-478`). La venta entra de golpe a Metas.
- Cancelar un recibo puede regresar `pagado` a 0.

La pestaña Metas de Reportes lo dice textual: *"Los meses se recalculan con los pagos y cambios que llegan después."* Si prefieres congelar el mes al cerrarlo, está en §10.6.

### Las dos ventanas (decisión 3), cada una con sus tres variables
| Ventana | Vendido | Meta de comparación |
|---|---|---|
| **Mes calendario** (día 1 → hoy) | `mes` | Meta del mes prorrateada: `meta × (dia_mes − 1 + fracción_del_día_transcurrida) / dias_mes`. Así no se cuenta el día de hoy como completo desde las 9 am. Para decir "ya sobrepasó" se usa la meta completa (§3). |
| **Últimos 30 días** (móvil) | `d30` | Suma día por día de `meta_del_mes(d) / días_de_ese_mes(d)`. Si **algún** día de la ventana no tiene meta (ni propia ni heredada), toda la ventana d30 queda `sin_metas`. No se prorratea a medias, porque eso haría la meta artificialmente fácil. |

### Tasa real (una sola receta)
- Se calcula en las mismas dos ventanas.
- **Numerador:** número de ventas con la receta de "vendido" (fecha en `ventas.created_at`).
- **Denominador:** cotizaciones **enviadas** en esa ventana, con la misma definición del embudo del dashboard (no borrador, no suspendidas, por `created_at`; `dashboard/index.php:260-264`).
- **Limitación conocida, escrita en la tarjeta:** numerador y denominador van por fechas distintas, como en el motor. Con backlog, la tasa puede pasar de 100 %; se topa en 90 %, igual que `ActividadScore.php:1803`.

### Muestra mínima (estado gris)
Una sola lectura con pocas ventas es muy ruidosa. Esto es lo que ya se midió:

| Ventas al mes | Lecturas falsas de "abajo del equilibrio" |
|---|---|
| 1 | 56 % |
| 10 | 26 % |
| 40 | 7 % |

- **La muestra se mide sobre ventas esperadas:** `meta_equilibrio_de_la_ventana ÷ ticket`. Si se midiera sobre observadas, una empresa con cero ventas saldría siempre gris y se escondería la peor noticia.
- **De dónde sale el ticket (en este orden):**
  1. Ventas reales de 180 días con la receta de "vendido", n ≥ 5.
  2. Si no hay, `historial_mensual`: `SUM(ventas_monto)/SUM(ventas_cantidad)` de los últimos 6 meses capturados.
  3. Si tampoco hay, **gris explícito**.
- Constante `MetasEmpresa::MUESTRA_MIN` (el valor lo decide el CEO, §10.2). Si `esperadas < MUESTRA_MIN`, la ventana queda en `gris`.

### Histéresis
Se lee y se escribe en `empresa_metas_estado` dentro de `try/catch`. Hay precedente de escribir durante una lectura: `Mesa::armar` → `mesa_vencidos`.

- **Subir de nivel** requiere cruzar el umbral.
- **Bajar de nivel** requiere quedar debajo de `umbral × (1 − MetasEmpresa::HISTERESIS)`, con una propuesta de 0.10.
- La primera evaluación de cada `periodo` **no cuenta como "cambio"** para las alertas.

---

## 3. Los estados que produce

### Zona de cada ventana
Se evalúa en este orden: el equilibrio va aparte porque puede quedar por encima de la pesimista.

| `nivel` | Condición | Frase (tercera persona, sin cifras de meta) |
|---|---|---|
| `muy_bajo` | vendido < equilibrio de la ventana | **"La empresa ni siquiera llega al punto de equilibrio {ventana}."** |
| `abajo` | ≥ equilibrio y < pesimista | **"La empresa va baja {ventana}."** |
| `casi` | ≥ pesimista y < optimista | **"La empresa casi llega a su meta {ventana}."** |
| `sobrepasada` | ≥ optimista | **"La empresa ya sobrepasó su meta {ventana}."** |
| `gris` | muestra insuficiente o sin ticket | **"Todavía es pronto para leer cómo va la empresa {ventana}."** |
| `sin_metas` | no hay metas (o la ventana d30 no está cubierta) | no hay renglón |

Si la pesimista es menor que el equilibrio, el tramo `abajo` queda vacío y la lectura sigue siendo coherente.

**Cómo se escribe `{ventana}`** (las fechas con `RitmoCot::fecha_corta()`, `RitmoCot.php:243`, que traduce el mes; nunca `date('d/M')`):
- Mes calendario: "en estos N días de {mes}". El día 1: "en este primer día de {mes}".
- Últimos 30 días: "en los últimos 30 días, al {fecha_corta}".

**Tres reglas de redacción:**
- `sobrepasada` en el mes calendario se decide contra la **meta completa**. Si va adelantada al ritmo pero no ha llegado, queda en `casi`.
- Ninguna frase lleva "vas", "te faltan" ni "tu meta".
- Los textos viven **solo** en `MetasEmpresa::frases()`.

### Conversión deseada (`nivel['conv']`)
- `debajo` si la tasa real < deseada × 0.9.
- `en` si está entre deseada × 0.9 y deseada × 1.1.
- `arriba` si supera deseada × 1.1.
- `gris` con menos de `MetasEmpresa::CONV_MIN = 8` enviadas en la ventana. Es una constante **propia**, sobre enviadas de la empresa. `RitmoAsesor::CERO_MIN` es privada y mide cotizaciones abiertas de un solo asesor (`RitmoAsesor.php:23, :150`), así que no se reutiliza.

Frases:
- *"La empresa cierra por debajo de lo que busca."*
- *"La empresa cierra en lo que busca."*
- *"La empresa cierra por encima de lo que busca."*

Si el asesor las recibe o no se decide en §10.7.

---

## 4. Qué lee cada consumidor

| Consumidor | Archivo:línea | Qué lee | Quién lo ve | ¿Cifras? |
|---|---|---|---|---|
| Tarjeta "Metas" (nueva) | `dashboard/index.php`, antes del include de `_ritmo.php` (`:893`), con gate propio `Auth::es_admin() && es_business` | `MetasEmpresa::estado()`: 2 renglones (mes y 30 días) con barra, 3 marcas, chip de nivel, tasa real contra deseada y N/M. Debajo: *"Aquí solo cuentan ventas con anticipo y sin Descuento Inteligente; se recalculan con pagos posteriores."* | admin | **sí** |
| Alerta de cambio de nivel | **dentro de la tarjeta Metas**, no en el bloque "Alertas" (`:1403-1404`, que no tiene gate y lo ven todos los roles) | `MetasEmpresa::estado()`, solo cuando cambia el nivel según la histéresis, nunca en gris ni en la primera evaluación del periodo | admin | sí |
| KPI "Ventas del período" | `dashboard/index.php:118-127` | nada; no se toca | todos | ya existentes |
| Renglón bajo el termómetro | `dashboard/index.php`, debajo de `.thermo-nivel` (`:979`) | `MetasEmpresa::frases()`, un renglón por ventana, omitido si es `sin_metas` | asesor y admin | **no** |
| `diag_ctx` | `dashboard/index.php`, después de `:59-61` | `$diag_ctx['metas_nivel'] = MetasEmpresa::nivel($empresa_id)`. **Se arma fuera de ActividadScore** para que el motor no mencione Metas (decisión 6) | — | no |
| DiagnosticoTips | `:448` (variante 3 de `motor_completo`), gate en `:479` y recorte en `:523-524` | Nuevo `$g_mes`: la variante sale solo si `$g_alza` **Y** además (`metas_nivel` ausente, `mes` en gris o `sin_metas`, o `mes` en `casi` / `sobrepasada`). Sin metas, el comportamiento es idéntico al de hoy. Con metas, la frase "el mes ya esté ganado" no contradice el renglón "la empresa va baja" de la misma tarjeta (principio de `:470-474`) | asesor | no |
| RitmoTip | `RitmoTip.php` (cascada, `_tono :385-391`) | **nada** | — | — |
| Reporte del Director, cuerpo | `RitmoReporte::_componer` (clave nueva `empresa`) + `render`, después de "Cómo vas" (`:770`) y antes de "Resumen" (`:771`) | `MetasEmpresa::frases()` fechadas | asesor (`:545-546`) | **no** |
| Reporte del Director, barra del modal | `_ritmo.php`, nodo nuevo `#rt-metas` hermano de `#rt-body` (`:88`) | Campo `metas` en `/api/reporte-asesor` (ramas de caché `:66-73` y fresca), **solo si `!$es_supervisor`** | admin (`_ritmo.php:11`) | **sí** |
| Pilar Conversión (`conv_txt`) | `RitmoAsesor.php:146-171` | **nada**. La tasa deseada **no se agrega** al texto: la tasa del pilar se calcula sobre abiertas (`:143-147`) y la deseada sobre enviadas. Mezclarlas repetiría el defecto de "tres números de tres cuentas" que se corrigió en PR #1034. Además `conv_txt` también lo pintan el supervisor (`:346`), el executive (`:1538`) y el reporte (`RitmoReporte.php:442`) | — | — |
| Pestaña "Metas" en Reportes (nueva) | `reportes/index.php`, pestañas `:761-769`, **solo `$es_admin`** | Mes a mes: declarado contra vendido (vivo), nivel, tasa real contra deseada (misma receta de `MetasEmpresa`) | admin | sí |
| Colores de tasa en Reportes y Config | `reportes/index.php:930, :1013`, `config/index.php:1480` | **no se tocan**. `:930` usa otra receta (`$tasa_conv`, `:149`: sobre no-borrador, con DI) y la ve el asesor con `ver_reportes` (DEFAULT 1, `add_permiso_reportes.sql:3`). `:1013` y config `:1480` son meses pasados | — | — |
| Mesa: orden, sugerencias, chip "📅 Mes" | `Mesa.php`, `MesaSugerencias.php`, `_mesa.php:357-364` | **nada**. El chip "📅 Mes" sigue con su receta; el nivel se pinta en el termómetro, no junto a él | — | — |
| RitmoCot, Score | `core/RitmoCot.php`, `ActividadScore::calcular` | **nada** | — | — |
| Push | `PushNotification.php` | nada en esta fase. Si se agrega: con cifras solo por `enviar_a_usuario` a cada admin, **nunca** por `enviar_a_empresa` | — | — |

---

## 5. La tasa de conversión deseada: dónde entra y dónde no

**Base:** cotizaciones **enviadas**, la base que entiende el dueño y la del embudo del dashboard (`:260-264`). La tasa real que se le compara usa la receta única de la §2. La tarjeta lo dice textual: *"Cierre = ventas con anticipo ÷ cotizaciones enviadas, sin Descuento Inteligente."* Hoy conviven cinco recetas de "tasa de cierre" en el sistema, y la tarjeta tiene que nombrar la suya.

### Dónde entra
1. **Tarjeta Metas y barra del modal (admin).** Por ejemplo: *"Cierre 22 % · deseado 30 %"*.
2. **Cotizaciones que faltan (admin), dos números:**
   - Con la tasa real: *"A como cierra hoy la empresa, hacen falta N cotizaciones más."*
   - Con la deseada: *"Si cierra al 30 %, bastan M."*

   Se calcula `faltante ÷ ticket ÷ tasa`, con los tres ingredientes sobre la misma base. Pasado el día `dias_mes − p75`, se aclara: *"lo que se cotice hoy ya cierra el mes siguiente."* Si la tasa real está en gris, solo se muestra M. Si no hay ticket, no se muestra ninguno.
3. **Pestaña Metas de Reportes (admin).**
4. **`nivel['conv']` en tercera persona para el asesor**, solo si el CEO lo aprueba (§10.7).

### Dónde NO entra
- `close_rate` como escala del motor, la sigmoide, `perf_ratio`, `pen_volumen_sin_cierre` ni `bonus_cierre`. Sería tocar el score (decisión 6).
- El texto y el color del pilar Conversión (decisión 7 y la mezcla de bases).
- Los colores existentes de Reportes y Config.
- La cascada de RitmoTip ("citas en cero primero"), el orden de la Mesa y RitmoCot.
- Ninguna superficie del asesor lleva la cifra deseada ni N/M.

---

## 6. El reporte del Director

**En el cuerpo impreso, solo niveles.**
- `_componer()` devuelve la clave `empresa`: un renglón como máximo por ventana, más el de conversión si se aprueba.
- `render()` la pinta como `$sec('La empresa en {mes} al {fecha_corta}', …)`, entre "Cómo vas" (`:770`) y "Resumen" (`:771`). El título **no dice "este mes"**. El HTML se sirve desde la caché hasta 7 días (`api/reporte_asesor.php:66-73`), y un reporte del 28/Sep que se imprime el 5/Oct no puede afirmar "este mes". Tampoco usa la palabra "Meta", para no chocar con "Meta de la semana".
- Cada frase nombra su ventana y su fecha absoluta (§3).
- El "Consejo del Director" puede sumar una línea impersonal cuando el nivel es `muy_bajo` o `abajo`.
- Se acepta pasar a 2 hojas. **No se esconde nada al imprimir.**

**En la barra del modal, cifras.**
- `#rt-metas` es hermano de `#rt-body`. `rtPrint()` solo copia `#rt-body` (`_ritmo.php:162-163`), así que la barra no se imprime ni se guarda en `ritmo_reportes.contenido`.
- Se calcula en vivo en las dos ramas.
- En la rama de caché, si `created_at` es de otro mes o el nivel ya cambió, la barra lo dice: *"El reporte guardado es del 28/Sep; hoy la empresa está en…"*.
- **El banner de caché se inyecta dentro de `#rt-body` (`_ritmo.php:210-214`) y sí se imprime**, así que nunca lleva cifras.
- **Supervisor fuera de alcance:** el endpoint no agrega `metas` cuando `$es_supervisor` (`api/reporte_asesor.php:17, :30-33`). El cuerpo que él recibe solo tiene niveles, así que no hay filtración.

---

## 7. Lo que queda fuera a propósito

- **Score (decisión 6):** `MetasEmpresa` no se usa en `ActividadScore`. Si algún día entra, será como componente propio con peso e interruptor por empresa (igual que `mesa_activa`), nunca como punto medio de la sigmoide.

| Decisión cerrada | Cómo se respeta |
|---|---|
| "Citas en cero primero" | El nivel es un renglón aparte bajo el termómetro; RitmoTip no lo lee |
| Escala de urgencia y orden de la Mesa | Metas no toca `Mesa.php`, `MesaSugerencias` ni el ticket de la Mesa |
| Castigo por vencidas | No se toca |
| La vara de RitmoCot es el propio asesor | Nunca recibe metas ni N/M |
| El color de Conversión lo decide el histórico | `conv_txt` y `conv_estado` quedan idénticos |
| El asesor no ve cifras | `nivel()` y `frases()` sin cifras de meta; la barra fuera de `#rt-body`; la tarjeta y la alerta con gate de admin; sin push por `enviar_a_empresa` |

**Filtraciones que ya existen hoy** (esto no las causa, pero conviven con esto): `RitmoTip.php:297` y `:356-358`, `ScoreLectura.php:280`, `RitmoReporte.php:571`, `RitmoAsesor.php:147`, y el punto de equilibrio **calculado** en pesos de `reportes/index.php:1926-1966`, visible con `ver_reportes`. En la pestaña Metas, ese último se renombra a *"Punto de equilibrio calculado (con tus gastos registrados)"*. Qué hacer con cada una lo decide el CEO (§10.8).

---

## 8. Pruebas

| Prueba | Tipo | Qué cubre |
|---|---|---|
| **`tools/sim_metas.php`** (nueva) | MariaDB real | La receta de vendido (pagado > 0, total > 0, no cancelada, sin DI, límites exactos). Cero ventas → `n` = 0, no NULL y sin excepción. 30 días = 30 fechas. Prorrateo cruzando meses de 31 y 28 días. d30 sin cobertura → `sin_metas`. Herencia y `provisional`. Moneda distinta → `sin_metas`. Tabla ausente → `sin_metas`. Gris con cero observadas y pocas esperadas. **Gris con muchas observadas y meta baja**, donde la frase no afirma volumen. Ticket con respaldo en `historial_mensual`, y empresa nueva sin nada → gris. Histéresis sin oscilar cuando una venta sale de la ventana. **Cambio de mes** sin arrastrar nivel y sin alerta. Primer pago al mes siguiente. Quitar el DI (la venta entra con total + monto_desc). Extra agregado después. Recibo cancelado → sale. Pesimista < equilibrio. Tasa real con el tope en 90 %. |
| **`tools/test_metas_fugas.php`** (nueva) | estática y de render | `nivel()` y `frases()` sin `$`, sin `%`, sin montos con separador y sin los valores de meta sembrados en el fixture (días y fechas sí se permiten). Ninguna frase con "vas", "te faltan" ni "tu meta". El HTML de `RitmoReporte::render` sin cifras de meta. `rtPrint` no copia `#rt-metas`. **Render del dashboard como asesor: sin la tarjeta Metas y sin cifras**. `ActividadScore` no menciona `MetasEmpresa`. `/api/reporte-asesor` como supervisor no devuelve `metas`. |
| **`tools/test_ritmo_tip.php`** (nueva) | unitaria | Con metas en cualquier nivel, la rama elegida y `_tono` no cambian. "Citas en cero primero" se mantiene. |
| `tools/test_tips_gates.php` (se extiende) | unitaria | La variante de `:448` con `sin_metas` → igual que hoy (solo `g_alza`). Con `mes=abajo` → no sale. Con `mes=casi` o `sobrepasada` y `g_alza` → sale. |
| `test_score_lectura`, `sim_mesa_armar` → `sim_mesa_render`, `sim_mesa_reporte`, `factlint_tips_v2` | existentes, obligatorias | Sin regresión; la Mesa sin cambios |

---

## 9. Orden de construcción

0. **Verificar en el servidor** (sin escribir código): el índice de `ventas` por `(empresa_id, created_at)` y cómo carga `config.php` las clases de `core/`. La ruta la tomé de CLAUDE.md **(sin verificar)**.

   ```bash
   CFG=/var/www/cotizacloud/config.php
   DBH=$(php -r "include '$CFG'; echo DB_HOST;"); DBP=$(php -r "include '$CFG'; echo DB_PORT;"); DBN=$(php -r "include '$CFG'; echo DB_NAME;"); DBU=$(php -r "include '$CFG'; echo DB_USER;"); DBW=$(php -r "include '$CFG'; echo DB_PASS;")
   grep -nE "spl_autoload|require|include" "$CFG"
   mysql -h"$DBH" -P"$DBP" -u"$DBU" -p"$DBW" "$DBN" <<'SQL'
   SHOW INDEX FROM ventas;
   SELECT DISTINCT moneda FROM empresas;
   SQL
   ```

1. **Datos:** la migración (más el índice si falta), `core/MetasEmpresa.php` y `sim_metas.php` en verde.
2. **Captura:** la pestaña Metas y `guardar_metas.php`.
3. **Admin:** la tarjeta y la alerta del dashboard, la barra `#rt-metas` y la pestaña Metas de Reportes.
4. **Asesor:** el renglón bajo el termómetro, `metas_nivel` en `diag_ctx`, el gate `$g_mes`, la sección del reporte, `test_metas_fugas` y `test_ritmo_tip`.
5. **Limpieza:** borrar `monto_mes_actual`, `dia_mes` y `dias_mes` de `diagnostico_ctx`, y atender las filtraciones de §7 según decida el CEO.
6. **Score:** fuera, hasta que se decida.

---

## 10. Preguntas para el CEO

1. **El día 3 con pocas ventas.** Para leer *"La empresa va baja en estos 3 días"* hace falta muestra. Una empresa chica sale gris los primeros días. ¿Se acepta el gris, o el mes calendario opina desde el día 1 aunque se equivoque más?
2. **Valor de `MUESTRA_MIN`** (ventas esperadas en la ventana). Con 10, todavía queda alrededor de 26 % de lecturas falsas.
3. **Pesimista por debajo del equilibrio.** ¿Se permite (como propongo, evaluando el equilibrio aparte) o se rechaza al capturar?
4. **"Casi llega".** ¿Es el tramo entre pesimista y optimista, o se mide contra la pesimista (por ejemplo, ≥ 90 % de ella)?
5. **Ritmo lineal en el mes.** Castiga a los negocios que cobran fuerte en la quincena o a fin de mes. ¿Se queda lineal?
6. **Meses pasados: ¿vivos o congelados al cierre?** Hoy se propone vivo, con una nota. Si cambia la tasa deseada, ¿hace falta un historial de la tasa para juzgar cada mes con la que regía entonces?
7. **¿El asesor recibe el nivel de la conversión deseada** (*"La empresa cierra por debajo de lo que busca"*) o eso es solo del admin?
8. **Cifras de empresa que ya se filtran hoy** (RitmoTip `:297` y `:356-358`, ScoreLectura `:280`, RitmoReporte `:571`, RitmoAsesor `:147`, equilibrio calculado en Reportes para `ver_reportes`): ¿se quitan, se vuelven cualitativas o se quedan?
9. **Asesores con `ver_todas_ventas`** ven el monto del mes de la empresa, y junto con el nivel pueden acotar la meta. ¿Se acepta esa filtración parcial?
10. **Con `termometro_visible = 0`**, ¿dónde ve el asesor el nivel, o no lo ve?

---

## Críticas descartadas

- **"Prorratear con `(día_mes − 1)/dias_mes`".** Descarté la fórmula, no el problema. El día 1 daría una meta prorrateada de 0 y una razón vendido/meta infinita. Queda `(día_mes − 1 + fracción_del_día)/dias_mes`.
- **"El supervisor tiene rol admin (`Auth.php:431`)".** No lo verifiqué. `es_admin()` solo acepta `admin` o `superadmin`, y el rol real del supervisor no lo consulté. Da igual para el diseño: el supervisor quedó fuera y el endpoint no le devuelve `metas`.
- **"Bloquear el cambio de moneda cuando hay metas capturadas".** No verifiqué que la moneda se pueda cambiar desde Config; solo vi que se fija en el registro (`registro_post.php:47`). En su lugar se guarda la `moneda` en cada fila y, si no coincide con la de la empresa, la ventana queda en `sin_metas`. Funciona en los dos casos.
- **"Recolorear Reportes solo en la vista del admin".** Lo descarté y fui más lejos: los colores de Reportes no se tocan en absoluto. `:930` usa otra receta (sobre no-borrador y con DI), así que compararla con la deseada mezclaría bases aunque solo la viera el admin.
- **Todas las demás críticas se sostienen en el código y quedaron incorporadas:** `conv_txt` y sus tres consumidores, supervisor fuera, alerta sin gate, contradicción con ActividadScore, frase del gris, gate de DiagnosticoTips, `CERO_MIN`, citas, marcadores PDO y COALESCE, dígitos en la prueba, `periodo` en la histéresis, mezcla de bases en el pilar, ticket nulo, d30 sin cobertura, vendido retroactivo, caché y fecha en inglés, validación de orden, receta de la tasa real, `total > 0`, zona horaria y ticket de la Mesa.
---

## Decisiones del CEO incorporadas (25 sep 2026)

1. La venta cuenta en la fecha de **aceptación**, pero solo si ya tiene su **primer pago** (`pagado > 0`).
2. Las tres variables (equilibrio, meta pesimista, meta optimista) se muestran en **las dos ventanas**: mes calendario y últimos 30 días. En Configuración también va la **tasa de conversión promedio deseada**.
3. Al asesor **nunca cifras**. Se le habla **de la empresa, en tercera persona**: *"La empresa va baja en estos 3 días"*, *"La empresa ni siquiera llega al punto de equilibrio"*.
4. El reporte del Director **lleva su sección** aunque pase a 2 hojas; después se compacta.
5. El **Descuento Inteligente no cuenta**.
6. El score queda **abierto**: primero se integra, de forma integral.
7. Todo es de la SaaS. **Nada** de `executive.php`, del bono ni de `data/`.

## Pregunta agregada

11. **Plan.** Este diseño lo deja solo en Business, porque todo el motor de CotizaCloud AI es Business. Antes se había planteado dar a Pro la captura y la tarjeta del admin. ¿Pro las recibe?

## Decisiones del CEO — segunda ronda (25 sep 2026). MANDAN sobre lo anterior

1. **Mínimo de historia:** se lee en cuanto la empresa tenga **30 días desde su primera venta con pago**. **Sustituye** `MUESTRA_MIN` y el cálculo de ventas esperadas de la §2. Sin esa historia: *"Todavía no hay suficiente historia para leer cómo va la empresa."*
2. **Escalones de 10%** (propuesta, pendiente de visto bueno de la redacción). Primero se revisa el equilibrio. Si se alcanza, el avance se mide contra la pesimista, y ya pasada la pesimista, contra la optimista. En el mes calendario, contra la meta prorrateada a los días transcurridos.

   | Avance | Frase |
   |---|---|
   | < equilibrio | La empresa ni siquiera llega al punto de equilibrio en estos N días. |
   | < 60% pesimista | La empresa va muy baja en estos N días. |
   | 60–69% | La empresa va baja en estos N días. |
   | 70–79% | La empresa va por debajo de su meta en estos N días. |
   | 80–89% | La empresa va cerca de su meta. |
   | 90–99% | La empresa casi llega a su meta. |
   | pesimista alcanzada, < 90% optimista | La empresa ya llegó a su meta. |
   | 90–99% optimista | La empresa casi llega a su meta optimista. |
   | ≥ optimista | La empresa ya sobrepasó su meta optimista. |

3. **Validación al capturar:** equilibrio ≤ pesimista ≤ optimista. Si no se cumple, se rechaza.
4. **Meses vivos:** la venta cuenta en su mes de **aceptación** aunque el anticipo llegue después, para bien o para mal. No se congela.
5. **El asesor también recibe los niveles y la conversión en los TIPS** (RitmoTip y DiagnosticoTips), no solo en el reporte del Director, con contextos distintos por tip. **Cambia la §4**: RitmoTip ya no queda en "nada". Los contextos se diseñan en su fase, sin pasar por encima de "citas en cero primero".
6. **Plan Pro:** abierto.

## Decisión del CEO — tercera ronda (25 sep 2026)

**El mes calendario NO se prorratea.** El avance se mide contra la meta **completa** del mes, y la frase dice *"en este mes"* (por ejemplo, *"La empresa va muy baja en este mes"*). Anula el prorrateo de la §2 (tabla "Las dos ventanas") y la nota "en el mes calendario, contra la meta prorrateada" de la segunda ronda. Razón del CEO: consistencia. Al inicio del mes la frase va a decir "muy baja"; eso es aceptado.
