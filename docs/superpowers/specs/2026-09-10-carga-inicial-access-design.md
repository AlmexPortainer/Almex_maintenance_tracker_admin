# Carga inicial desde Access (.mdb) → Laravel

**Fecha:** 2026-09-10
**Objetivo:** Migrar los datos históricos del sistema Access `BD InstrumentosCalidad2.mdb`
a las tablas `instruments` e `instrument_events` mediante un seeder reproducible.

## Contexto

El archivo fuente es una base Access (Jet 3 / Access 97). No se puede leer con ACE
OLEDB (rechaza el formato viejo) ni con Jet 4.0 (crashea en schema, bloquea `MSysObjects`).
Se extrae con **Jackcess (Java)** forzando charset `Cp1252` — sin él los acentos se
corrompen (`ó`→`�`) y se rompen nombres de columna como `Código`.

La herramienta de extracción vive en `scripts/` (ignorada por git):
- `scripts/mdblib/MdbDump.java` — vuelca cada tabla a JSON UTF-8 en `database/mdb_export/`.
- `scripts/json_to_sqlite.py` — construye `database/mdb_export/instrumentos.sqlite` para inspección.

El seeder consume los **JSON** de `database/mdb_export/`, no el `.mdb` directamente.

### Tablas fuente (archivo actual)

| Tabla Access | Filas | Destino |
|---|---|---|
| `Instrumentos` (65 cols) | 777 | `instruments` |
| `HistoricosCalibraciones-1` | 10,710 | `instrument_events` (CALIBRACION) |
| `HistoricosVerificaciones-1` | 12,912 | `instrument_events` (VALIDACION) |
| `HistoricosMantenimiento-1` | 4,681 | `instrument_events` (MANTENIMIENTO) |
| `Patrones`, `Errores de pegado` | 1, 1 | ignoradas |

## Decisiones

1. **Enums vs data.** `form` se expande a enum limpio de 5 valores.
   `variable_unit_of_measure` pasa a **`string` nullable** (los datos traen ~50 valores
   sucios con typos y duplicados — un enum sería frágil).
2. **`is_operational` = `(Estado === 'Activo')`.** Baja / Fuera de Servicio / Stock → false.
3. **Estrategia de carga:** bulk `DB::table()->insert()` por chunks, **evitando** el hook
   `saved` de `InstrumentEvent`. Los snapshots (`last_/next_`) se toman directo de las
   columnas de Access, no se recalculan del historial.
4. **Alcance:** los 777 instrumentos (incluye no operativos) + todos sus eventos.
5. **Eventos huérfanos** (Código sin instrumento padre) y **eventos con fecha null** se
   **saltan** e imprimen conteo al final (`fecha_evento` es NOT NULL en la migración).
6. **Idempotencia:** el seeder hace `truncate` de `instrument_events` e `instruments`
   al inicio (desactivando FK checks para el orden).

## Componentes

### 1. Migración `expand_instrument_enums`

Nueva migración (posterior a las existentes) que altera la tabla `instruments`:

```php
$table->enum('form', [
    'Instrumento Electrónico',
    'Instrumento Mecánico',
    'Instrumento Simple',
    'Reactivo',
    'Otro',
])->default('Instrumento Simple')->change();

$table->string('variable_unit_of_measure')->nullable()->change();
```

Laravel 12 + MySQL: `->change()` es nativo, **no** requiere `doctrine/dbal`.

`down()` revierte a los enums originales de la migración base.

### 2. `InstrumentImportSeeder`

`database/seeders/InstrumentImportSeeder.php`. Pasos:

1. `Schema::disableForeignKeyConstraints()`; `truncate` `instrument_events`, `instruments`;
   `enableForeignKeyConstraints()`.
2. Leer `database/mdb_export/Instrumentos.json`.
3. Construir filas `instruments` (mapa abajo), insertar en chunks de ~500.
4. Cargar mapa `code → id` de los instrumentos recién insertados.
5. Para cada tabla `Historicos*`, leer JSON, mapear a `instrument_events`, resolviendo
   `instrument_id` vía el mapa; saltar huérfanos y fecha-null (contar).
6. Insertar eventos en chunks de ~1000.
7. `echo` de conteos: instrumentos, eventos por tipo, huérfanos, fecha-null.

Registrar en `DatabaseSeeder`: reemplaza las llamadas a `InstrumentZacSeeder` (10 filas
curadas a mano, ya superadas por los 777 reales) e `InstrumentEventSeeder`. Los seeders
viejos se conservan en el repo pero se dejan de llamar.

### Mapa `Instrumentos` → `instruments`

| Access | Laravel | Regla |
|---|---|---|
| Equipo | name | crudo |
| Marca | brand | crudo |
| Modelo | model | crudo |
| Código | code | unique |
| Depto | department | crudo |
| Area | location | crudo |
| FormaInstrumento | form | `otro`→`Otro`; `null`→`Otro`; resto igual |
| Variable | variable_unit_of_measure | crudo (string) |
| Tipo | types_of_criticality | `Crítico`→`CRITICO`, `No Crítico`→`NO_CRITICO` |
| — | level_of_criticality | `BAJA` (sin fuente) |
| — | type | `INSTRUMENTO` (sin concepto PLANO en Access) |
| Tolerancia | emt_value | string; `emt_value_decimal`/`emt_unit`/`emt_symmetry` = null |
| FrecuenciaCalibracion | calibration_periodicity_days | `round(int)`, null→0 |
| FrecuenciaVerificacion | validation_periodicity_days | `round(int)`, null→0 |
| FrecuenciaMantenimiento | maintenance_periodicity_days | `round(int)`, null→0 |
| FechaUltimaCalibracion | last_calibration_date | fecha |
| FechaProximaCalibracion | next_calibration_date | fecha |
| FechaUltimaVerificacion | last_validation_date | fecha |
| FechaProximaVerificacion | next_validation_date | fecha |
| FechaUltimoMantto | last_maintenance_date | fecha |
| FechaProximoMantto | next_maintenance_date | fecha |
| PersonaCalibro | last_calibration_user | crudo |
| PersonaVerifico | last_validation_user | crudo |
| PersonaMantto | last_maintenance_user | crudo |
| Instructivo | file_manual | `?? 'N/A'` |
| Estado | is_operational | `=== 'Activo'` |
| Observaciones | observations | crudo |
| — | created_at / updated_at | `now()` |

### Mapa `Historicos*-1` → `instrument_events`

Común: link `Código` → `code` → `instrument_id`. `fecha_proxima` y `fecha_maxima` = null
(los históricos no traen fechas futuras). `event_type` según tabla.

| Destino | Calibraciones | Verificaciones | Mantenimiento |
|---|---|---|---|
| event_type | CALIBRACION | VALIDACION | MANTENIMIENTO |
| fecha_evento | FECHA_CALIB | FECHA_VERIF | FECHA_MANTTO |
| responsable | QUIEN_CALIB | QUIEN_VERIF | QUIEN DIO_MANTTO |
| reporte | REPORTE_CALIB | REPORTE_VERIF | REPORTE_MANTTO |
| resultados | RESULTADOS_CALIB | RESULTADOS_VERIF (+ `PATRON USADO_VERIF` anexado) | OBSERVACIONES_MANTTO |
| adecuado | `ADECUADO USO_CALIB === 'SI'` | `ADECUADO USO_VERIF === 'SI'` | `true` (sin campo) |

Notas:
- `adecuado`: valores fuera de `SI`/`NO` (`N/A`, `Correctivo`, `Inicia su programación`,
  null) → `false`. El texto original se conserva en `resultados` cuando aplica.
- `PATRON USADO_VERIF`: si presente, se anexa a `resultados` como `Patrón: <valor>`.

## Manejo de errores / casos borde

- **Fecha null** (~276 eventos): se saltan (columna NOT NULL). Contador reportado.
- **Huérfanos** (~2,666 eventos, Código sin padre): se saltan. Contador reportado.
- **Código duplicado en instruments:** no ocurre (777 únicos verificado), pero `code`
  tiene unique — si apareciera, el insert fallaría y debe abortarse con mensaje claro.
- **Encoding:** los JSON ya vienen UTF-8 correctos desde la extracción Cp1252.

## Pruebas

- Test de feature (Pest): correr `InstrumentImportSeeder` contra los JSON reales y aseverar:
  - `instruments` tiene 777 filas; un code conocido existe con sus campos mapeados.
  - `instrument_events` count = (total históricos − huérfanos − fecha_null).
  - Un instrumento conocido tiene sus eventos ligados por `instrument_id`.
  - Snapshots (`last_calibration_date` etc.) coinciden con el JSON de `Instrumentos`.
- Verificar que el hook `saved` NO se dispara (los snapshots quedan como Access, no
  recalculados).

## Addendum (2026-09-10): Estados y listados de administración

Requisito posterior: los instrumentos `Baja` y `Fuera de Servicio` no deben ensuciar el
catálogo principal, pero deben poder administrarse y revisarse (auditoría + histórico) en
listados aparte.

**Decisiones:**
- Se **almacena el estado** original de Access en una columna nueva `instruments.status`
  (string indexado: `Activo | Baja | Fuera de Servicio | Stock`). El seeder lo mapea desde
  `Estado`. Se conserva `is_operational` (= `status === 'Activo'`).
- **No se usa SoftDeletes.** La "omisión lógica" se logra con un scope: el catálogo
  principal (`InstrumentListScreen`) usa `Instrument::visibles()` que excluye
  `ESTADOS_OCULTOS = [Baja, Fuera de Servicio]`. Los registros siguen en BD y ligados a sus
  eventos.
- **Dos pantallas Orchid nuevas** (clases separadas, Opción B):
  `InstrumentBajaListScreen` y `InstrumentFueraServicioListScreen`, cada una lista por
  `status` y ofrece enlace a Ver (administrar) e Histórico (`platform.instruments.events`).
  Rutas `instruments/baja` y `instruments/fuera-de-servicio`, registradas **antes** de
  `instruments/{instrument}`. Menú nuevo "Administración de Estados" con badges de conteo.

**Conteos (archivo actual):** Activo 382, Baja 292, Fuera de Servicio 90, Stock 13 (=777).
Catálogo visible = Activo + Stock = **395**.

**Modelo:** constantes `ESTADO_*`, `ESTADOS_OCULTOS`, scopes `visibles()` y `status($s)`.

## Fuera de alcance

- `Patrones` (1 fila) — no hay tabla destino.
- Parseo fino de `emt_value` a decimal/unidad — se guarda como string crudo.
- Refactor del hook de `InstrumentEvent` o de seeders existentes no relacionados.
