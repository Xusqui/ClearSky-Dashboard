# Hoja de ruta: mejoras de la Auditoría Científica ClearSky
Fecha: 2026-07-23
Origen: `AUDITORIA_CIENTIFICA_CLEARSKY_2026.md` (auditoría externa, 2026-01-20) + verificación contra el código actual.

## Objetivo
Convertir los hallazgos de la auditoría científica en tareas concretas, verificadas contra el estado real del código (no todo lo que decía la auditoría de enero sigue vigente), separando lo que es un cambio de código mecánico de lo que requiere una decisión de producto/diseño o recolección de datos científicos que yo no puedo tomar por mi cuenta.

## Qué ya quedó resuelto por el trabajo de rendimiento de esta sesión
Dos hallazgos de la auditoría se solapan al 100% con la hoja de ruta de rendimiento (`PROPUESTA_MEJORAS_RENDIMIENTO_2026-07-23.md`) y ya están implementados ahí:

| Hallazgo de la auditoría | Equivalente ya implementado |
|---|---|
| "Actualización cada segundo es excesiva" (`update_status.js`, polling 1s → sugería 60s) | R1: contador visual local + polling real al servidor cada 5s (no 60s, pero el problema de fondo —60 requests/min idénticas— ya no existe) |
| "Scripts cargados sin async/defer" (`suncalc.js`, `echarts`, `astronomy.browser.js`, `orb.v2.js`) | R5: `defer` aplicado a prácticamente todos los `<script>` del sitio, incluidos esos cuatro |

No hay nada más que hacer en esos dos puntos.

## Verificación contra el código actual (2026-07-23)
Antes de planificar, comprobé cada hallazgo restante contra el código real — algunos ya cambiaron desde enero, otros siguen exactamente igual:

- **Coeficientes de seeing** (0.015/0.02/0.03): siguen idénticos en `get_seeing.php`.
- **Pesos de factor de nubosidad** (0.5/0.7/1.0): siguen idénticos.
- **Índice horario de Open-Meteo**: `fetch_cloud_layers_openmeteo()` ya busca el índice más cercano a la hora actual (arreglado en algún momento, no por mí); `fetch_pressure_levels()` **todavía** usa el último índice del array — el mismo bug, sin corregir, en la otra mitad del cálculo de seeing.
- **Históricos sin límite**: ya no son `SELECT *` sin acotar — usan sentencias preparadas con rango de fechas o un `INTERVAL 24 HOUR` por defecto. Pero para rangos personalizados largos (meses), siguen sin agregación/downsampling — el riesgo de payload gigante sigue ahí para ese caso.
- **Widgets simultáneos**: siguen siendo ~11-14 (según qué sensores tenga activados el usuario), sin cambios desde la auditoría.
- **Iconos de Weather Underground**: no 25 como decía la auditoría, sino 87 variables en `theme-switcher.php` y 147 en `images.php` (día/noche duplican muchas). Sin tocar.
- **DeltaT sin explicar**: sigue sin tooltip ni texto explicativo en `modal_seeing.php`.

## Matriz de cambios
| ID | Cambio | Categoría | Prioridad | Esfuerzo | Tipo de decisión | Estado |
|---|---|---|---|---|---|---|
| C1 | Arreglar `fetch_pressure_levels()` para usar el índice horario más cercano (igual que ya hace `fetch_cloud_layers_openmeteo()`) | Backend | Alta | Bajo | Técnica (mecánica) | Implementado |
| C2 | `LIMIT`/downsampling en históricos con rango personalizado largo | Backend | Alta | Medio | Técnica (mecánica) | Implementado |
| C3 | Corregir pesos de `factor_nubes` (nubes bajas > altas) | Backend | Alta | Bajo | **Requiere tu criterio** — cambia el resultado científico mostrado al usuario | Pendiente |
| C4 | Calibrar coeficientes de seeing con datos reales de observación | Backend | Media | Alto | **Requiere datos que no tengo** — necesita meses de observación visual (escala Antoniadi) correlacionada | Bloqueado (no es tarea de código) |
| C5 | `LIMIT` de seguridad genérico en el resto de queries de `meteo` | Backend | Media | Bajo | Técnica (mecánica) | Implementado |
| C6 | Migrar caché de archivo a APCu | Backend | Baja | Medio | Técnica, pero depende de si APCu está disponible en el servidor | Implementado |
| C7 | Tooltip explicativo para DeltaT (y Shear) en `modal_seeing.php` | Frontend/UX | Media | Bajo | Técnica (mecánica) | Implementado |
| C8 | Decimación en frontend para gráficos con miles de puntos | Frontend | Media | Medio | Técnica (mecánica) | Implementado |
| C9 | Libración lunar en el filtrado del Catálogo Lunar 100 | Frontend/Astronomía | Baja | Alto | Técnica, pero de precisión marginal (±7° en casos extremos) | Pendiente |
| C10 | Reducir de ~12 a ~6-8 widgets visibles (pestañas o colapsado) | UX/Diseño | Media | Alto | **Decisión de producto** | Rechazado por el usuario — no se implementa |
| C11 | Unificar ~90 iconos a un set reducido (5-8 estados) | UX/Diseño | Baja | Alto | **Decisión de producto** — afecta identidad visual | Pendiente (necesita tu visto bueno) |
| C12 | Checklist "¿Puedo observar esta noche?" (versión reducida de "Modo Observador Nocturno", sin ocultar/resaltar widgets — esa parte se solapaba con C10) | UX/Feature nueva | Baja | Medio | Alcance acordado con el usuario tras rechazar C10 | Implementado |

## Cómo lo distingo del resto de la hoja de ruta de rendimiento
A diferencia de R1-R8 (todas mecánicas: aplicar un patrón conocido sin ambigüedad), varias de estas sí implican una decisión que no es solo técnica:

- **C3 y C4** cambian el *resultado científico* que ve el usuario (el índice de seeing). No es solo "arreglar un bug" — es decidir qué modelo físico usar. C4 directamente no se puede resolver con código: requiere recolectar datos de seeing observado durante meses y correlacionarlos.
- **C10, C11, C12** son rediseño de producto/UX, no arreglos de rendimiento. Cambian cómo se ve y se usa el dashboard.

Para el resto (C1, C2, C5, C6, C7, C8, C9) son cambios técnicos acotados, del mismo estilo que R1-R8 — puedo implementarlos uno a uno igual que hicimos con la hoja de ruta de rendimiento, sin necesitar más que confirmación de "adelante".

## Detalle de implementación

### C1. Índice horario más cercano en `fetch_pressure_levels()`
Estado: Implementado en `static/modules/widgets/get_seeing.php`.
Cambio: se reemplazó `$lastIndex = count(...) - 1` por la misma búsqueda de índice más cercano a "ahora" que ya usaba `fetch_cloud_layers_openmeteo()`, comparando en UTC (la petición pide `timezone=UTC` explícitamente, a diferencia de la función de nubes que usa la zona horaria de la estación).
Nota: la caché combinada (`seeing_external.json`, TTL 30 min) puede servir el valor calculado con el bug antiguo hasta que expire de forma natural — no hace falta invalidarla a mano, es el mismo TTL corto que ya limita el problema.

### C2. Downsampling en históricos con rango personalizado
Estado: Implementado en los 8 endpoints de histórico afectados (`get_temp_historic.php`, `get_hum_historic.php`, `get_humint_historic.php`, `get_pressure_historic.php`, `get_tempint_historic.php`, `get_uv_solar_historic.php`, `get_wind_historic.php`, y el modo `detailed` de `get_rain_data_api.php`).
Cambio: en la rama de rango personalizado válido, se calcula `diff_days` (días totales del rango) y se elige la consulta:
- **> 30 días**: `GROUP BY DATE(timestamp)` — un punto por día.
- **7-30 días**: `GROUP BY DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00')` — un punto por hora.
- **≤ 7 días**: sin agregar, como antes.

Todas las columnas se agregan con `AVG()`, excepto `viento_direccion` en `get_wind_historic.php`: al ser una magnitud circular (0°=360°), promediarla con `AVG()` normal da resultados incorrectos cerca del norte (ej. la media de 350° y 10° saldría 180°/sur en vez de 0°/norte). Se calcula la media circular vía `DEGREES(ATAN2(AVG(SIN(RADIANS(...))), AVG(COS(RADIANS(...)))))` y se normaliza a [0, 360) en PHP tras la consulta.
`get_rain_monthly.php` no necesitó cambios: ya agregaba por mes de forma nativa, acotado independientemente del rango.
Riesgo: ninguno para el caso ≤7 días (comportamiento idéntico al anterior). Para rangos más largos, el usuario ve un punto por hora/día en vez de cada muestra cruda — pérdida de resolución esperada y equivalente a lo que ya proponía la propia auditoría.

### C5. `LIMIT` de seguridad en históricos
Estado: Implementado en los mismos 8 endpoints de C2.
Cambio: en cada rama sin agregación (el tramo ≤7 días con `BETWEEN`, la rama de "formato inválido" y la de "comportamiento por defecto", ambas a 24h) se añadió `LIMIT 50000`. Las ramas agregadas por hora/día (>7 días) ya están inherentemente acotadas por el número de cubos de tiempo, no necesitan LIMIT adicional.
Nota de alcance: no se tocaron las consultas de los widgets en vivo (`get_temp_data.php`, `get_humidity_data.php`, `get_pressure_data.php`, `get_dew_data.php`, `get_seeing.php`, etc.) — todas ya están acotadas por `ORDER BY timestamp DESC LIMIT 1` (última lectura) o por una ventana de tiempo corta y fija (3-8h para tendencias), con un techo de filas ya bajo incluso en el peor caso. Añadir un `LIMIT` ahí no habría cambiado nada real, así que se dejó fuera para no añadir complejidad sin un riesgo concreto que mitigar.
Riesgo: ninguno bajo uso normal (una estación reportando cada 16-60s tarda meses en acercarse a 50.000 filas en una ventana de 7 días); actúa solo como cota de emergencia.

### C6. Migrar caché de archivo a APCu
Estado: Implementado en los 6 endpoints que cacheaban en `static/cache/*.json`: `get_forecast.php`, `get_temp_data.php`, `get_pressure_data.php`, `get_humidity_data.php`, `get_dew_data.php`, `get_seeing.php`.
Cambio: cada caché pasó de `fopen()`/`flock()`/lectura-escritura de archivo a `apcu_fetch()`/`apcu_store()` (memoria compartida entre procesos PHP-FPM, sin I/O de disco). Para evitar el "thundering herd" que antes resolvía `flock()` (varios procesos recalculando a la vez cuando expira el TTL), se usa `apcu_add($lockKey, true, 10)` como lock atómico: solo el proceso que consigue crear la clave de lock recalcula; el resto sirve la caché existente (aunque esté caducada) mientras tanto. Se conserva el mismo fallback a "última caché conocida" cuando Open-Meteo falla (`get_forecast.php`, `get_seeing.php`).
Salvaguarda: cada archivo comprueba `function_exists('apcu_fetch')` una vez al principio; si APCu no estuviera disponible, se degrada a calcular siempre sin cachear (nunca falla, solo pierde el beneficio de rendimiento).
Limpieza: se borraron los 6 archivos de caché en disco ya no usados (`cache_forecast.json`, `temp_trend.json`, `pressure_trend.json`, `humidity_trend.json`, `dew_cache.json`, `seeing_external.json`). `cache_astronomy.json` (de `get_astronomy_quality.php`, código muerto) y `schema_check.json` (de `config.php`, R2) no se tocaron — siguen en archivo, fuera del alcance de C6.
Verificación de entorno: confirmado con un endpoint temporal (`_check_apcu_temp.php`, creado y borrado tras comprobar) que `apcu_extension_loaded`, `apcu_enabled_runtime` y una prueba de escritura/lectura dan `true`/`OK` en producción (PHP 8.4.19, FPM).
Riesgo: la caché en APCu no sobrevive a un reinicio de PHP-FPM (a diferencia del archivo en disco, que persistía); tras un despliegue/reinicio, los primeros requests recalculan una vez y luego vuelve a estar caliente — trade-off estándar y aceptable de usar memoria en vez de disco.

### C7. Tooltip explicativo para DeltaT y Shear
Estado: Implementado en `static/modals/modal_seeing.php` y `static/css/modal-seeing.css`.
Cambio: se añadió un atributo `title=` (tooltip nativo del navegador, mismo patrón ya usado en `modal_ephemeris.php`, sin necesidad de un sistema de tooltips nuevo) a las tarjetas de DeltaT y Shear vertical, más un icono ℹ️ discreto junto al título para que el usuario note que hay una explicación al pasar el ratón. Se añadió `cursor: help` en CSS a cualquier `.card[title]`.
Importante: los umbrales del texto explicativo se sacaron del código real de puntuación en `get_seeing.php` (líneas 264-266: `deltaT < 15` excelente, `15-30` moderado, `>30` inestable; `shear < 20` excelente, `20-40` moderado, `>40` alto) — no de los umbrales que proponía la propia auditoría (`<10/10-20/>20` para DeltaT), que no coincidían con lo que la aplicación realmente calcula.

### C8. Decimación en frontend
Estado: Implementado en los 8 archivos JS de gráficos históricos (`modal_temp.js`, `modal_tempint.js`, `modal_humidity.js`, `modal_humidityint.js`, `modal_pressure.js`, `modal_solar.js`, `modal_wind.js`, `modal_rain.js`), como red de seguridad complementaria a C2/C5 (que ya acotan el backend, pero el tramo ≤7 días sigue pudiendo devolver hasta 50.000 filas crudas).
Cambio: se usó exactamente el criterio que proponía la propia auditoría — si el array de datos tiene más de 5000 elementos, se diezma a un paso fijo (`Math.ceil(length / 2000)`) quedándose ~1 de cada N filas, antes de extraer las series para ECharts. En `modal_rain.js` (que recibe `{labels, series}` ya separados desde el backend en vez de un array de filas) se decima `labels` y cada `series[i].data` con el mismo paso, para mantener los arrays paralelos.
Nota: no se adoptó el muestreo nativo de ECharts (`sampling: 'lttb'`), que da mejores resultados visuales pero requiere pasar a un eje `type: 'time'` con pares `[timestamp, valor]` en vez de `type: 'category'` con arrays paralelos — un refactor mayor en los 8 archivos, desproporcionado para lo que pedía este ítem. Queda como posible mejora futura si el "cada N puntos" se nota visualmente basto.

### C12. Checklist "¿Puedo observar esta noche?"
Estado: Implementado, con alcance reducido respecto a la propuesta original de la auditoría.
Alcance descartado: la propuesta original ("Modo Observador Nocturno") incluía ocultar widgets irrelevantes y resaltar los críticos — esa parte se solapaba directamente con C10, que el usuario rechazó explícitamente. Se implementó solo la segunda mitad: una tarjeta nueva, aditiva, que no oculta ni reordena nada existente.
Archivos nuevos:
- `static/widgets/widget_observing_checklist.php` — tarjeta con una rejilla de 5 insignias circulares (seeing, viento, nubosidad, humedad, luna), cada una con emoji + un valor corto debajo. Se añade al `.widgets` (misma zona que los demás widgets de sensores), gateada por `$show_sky` (no tiene sentido sin la función de seeing activada).
- `static/js/widgets/observing_checklist_widget.js` — no crea un backend nuevo: reutiliza `get_seeing.php` (seeing, viento actual, `cloud_index`) y `get_humidity_data.php` (humedad y su estado de confort), y calcula la fase lunar en el propio cliente con `SunCalc.getMoonIllumination()` (la misma librería que ya usa `moon.js`, cargada globalmente). Sigue el patrón ya establecido de los demás widgets (`function updateX() {...}` + llamada inmediata al final, sin `const`/`let` de nivel superior, para que recargar el `<script>` no falle).
- `static/css/observing-checklist-widget.css` — **dos ajustes tras feedback del usuario**: (1) versión inicial era más ancha que el resto y solo texto → rediseñada como rejilla de círculos con anillo de color (verde/ámbar/rojo, reutilizando `--green`/`--orange`/`--red`, las mismas variables que ya usan las flechas de tendencia de temperatura), sin override de ancho (hereda el `max-width:16%` estándar de `.widget`); (2) se añadió un veredicto agregado 👍/👎 (círculo arriba de la rejilla) — negativo si algún indicador está en rojo, positivo en caso contrario, calculado en `observing_checklist_widget.js` sincronizando la fase lunar (client-side) con los dos fetch antes de decidir; (3) la rejilla volvió a 3 columnas (2 filas para 5 elementos, en vez de 3) y se redujeron círculos/márgenes/gaps porque la versión de 2 columnas quedaba más alta que el resto de tarjetas — el ancho no cambia con el número de columnas (lo fija `max-width:16%` en `.widget`), así que este ajuste solo afecta a la altura.
Registrado en: `index.php` (nuevo `<link>` de CSS + `require_once` dentro del bloque `if ($show_sky == 1)`, justo después de `widget_seeing.php`) y `update_status.js` (añadido a `WIDGET_SCRIPTS` para que se recargue junto al resto de widgets cuando llega un dato nuevo de la estación).
Umbrales usados (no inventados, sacados del propio código):
- Seeing: `estrellas >= 2` ✅ (Bueno/Muy bueno/Excelente), `>= 1` ⚠️ (Regular), si no ❌ — reutiliza la escala ya definida en `get_seeing.php`.
- Viento: mismos cortes que la propia fórmula de puntuación de seeing (`< 10` ✅, `< 20` ⚠️, si no ❌).
- Nubosidad: sobre `cloud_index` (0-100, ya ponderado con los pesos 0.5/0.7/1.0 de `factor_nubes`): `< 10` ✅, `< 40` ⚠️, si no ❌.
- Humedad: `state === 'comfortable'` ✅, si no ⚠️ (riesgo de condensación en la óptica, no descarta observar).
- Luna: `fraction < 0.25` ✅, `< 0.6` ⚠️, si no ❌ (más iluminación lunar = más brillo de fondo de cielo, peor para cielo profundo).
Riesgo: ninguno para el resto del dashboard (tarjeta puramente aditiva, no toca ningún widget existente). Depende de que `get_seeing.php`/`get_humidity_data.php` respondan; si fallan, esas filas quedan en "Cargando…" y se loguea el error en consola sin romper el resto de la tarjeta.

## Orden sugerido si se implementan
1. **C1** (bug idéntico al ya arreglado en nubes, coherencia interna) y **C2**/**C5** (riesgo de payload/queries sin límite) — más parecidos a R1-R8, bajo riesgo.
2. **C7** (tooltip) y **C8** (decimación) — mejoras de UX/rendimiento acotadas.
3. **C6** (APCu) — depende de disponibilidad en el servidor, verificar primero.
4. **C3** — pedirte confirmación antes de tocar los pesos científicos.
5. **C9, C10, C11, C12, C4** — requieren tu decisión de producto o no son ejecutables como tarea de código (C4).
