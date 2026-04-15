-- =============================================================
--  SISTEMA DE ASISTENCIA CON MARCADO POR CI
--  Base de datos: MySQL 8.0+
--  Generado para uso en producción
-- =============================================================

-- Crear y seleccionar la base de datos
-- CREATE DATABASE IF NOT EXISTS sistema_asistencia
--   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE sistema_asistencia;

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================
-- 1. TIPO_PERSONAL
--    Clasificación del personal: administrativo, técnico, etc.
-- =============================================================
CREATE TABLE IF NOT EXISTS tipo_personal (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    tipo            VARCHAR(60)     NOT NULL,
    estado          TINYINT(1)      NOT NULL DEFAULT 1
                        COMMENT '1=activo, 0=inactivo',
    creado_el       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_el  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tipo_personal_tipo (tipo)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Clasificación de tipos de personal';


-- =============================================================
-- 2. PERSONAL
--    Registro de empleados. El CI es el identificador único
--    usado para marcar asistencia.
-- =============================================================
CREATE TABLE IF NOT EXISTS personal (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    ci              VARCHAR(20)     NOT NULL
                        COMMENT 'Carnet de identidad – clave de marcado',
    nombre          VARCHAR(80)     NOT NULL,
    paterno         VARCHAR(60)     NOT NULL,
    materno         VARCHAR(60)         NULL DEFAULT NULL,
    correo          VARCHAR(120)        NULL DEFAULT NULL,
    celular         VARCHAR(20)         NULL DEFAULT NULL,
    estado          TINYINT(1)      NOT NULL DEFAULT 1
                        COMMENT '1=activo, 0=inactivo',
    creado_el       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_el  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_personal_ci (ci),
    INDEX idx_personal_estado (estado)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Datos del personal. CI se usa para marcar asistencia';


-- =============================================================
-- 3. OFICINA
--    Unidades o departamentos donde se asigna personal.
-- =============================================================
CREATE TABLE IF NOT EXISTS oficina (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(100)    NOT NULL,
    descripcion     TEXT                NULL DEFAULT NULL,
    estado          TINYINT(1)      NOT NULL DEFAULT 1
                        COMMENT '1=activa, 0=inactiva',
    creado_el       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_el  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_oficina_nombre (nombre)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Oficinas o departamentos de la institución';


-- =============================================================
-- 4. ASIGNACION_OFICINA
--    Historial de asignaciones de personal a oficinas.
--    fecha_fin NULL  → asignación vigente.
--    Un mismo personal puede tener múltiples asignaciones
--    en distintos períodos o distintas oficinas.
-- =============================================================
CREATE TABLE IF NOT EXISTS asignacion_oficina (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    personal_id         INT UNSIGNED    NOT NULL,
    oficina_id          INT UNSIGNED    NOT NULL,
    tipo_personal_id    INT UNSIGNED    NOT NULL,
    fecha_inicio        DATE            NOT NULL,
    fecha_fin           DATE                NULL DEFAULT NULL
                            COMMENT 'NULL = asignación actualmente vigente',
    estado              TINYINT(1)      NOT NULL DEFAULT 1
                            COMMENT '1=activa, 0=cerrada',
    vigente_unica       TINYINT         GENERATED ALWAYS AS (
                            CASE
                                WHEN estado = 1 AND fecha_fin IS NULL THEN 1
                                ELSE NULL
                            END
                        ) STORED,
    creado_el           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_el      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT chk_ao_rango_fechas
        CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio),
    CONSTRAINT fk_ao_personal
        FOREIGN KEY (personal_id)       REFERENCES personal (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ao_oficina
        FOREIGN KEY (oficina_id)        REFERENCES oficina (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ao_tipo_personal
        FOREIGN KEY (tipo_personal_id)  REFERENCES tipo_personal (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_ao_id_personal    (id, personal_id),
    UNIQUE KEY uq_ao_vigente_por_personal (personal_id, vigente_unica),
    INDEX idx_ao_personal       (personal_id),
    INDEX idx_ao_oficina        (oficina_id),
    INDEX idx_ao_tipo_personal  (tipo_personal_id),
    INDEX idx_ao_vigencia_lookup (personal_id, estado, fecha_inicio, fecha_fin),
    INDEX idx_ao_vigente        (personal_id, fecha_fin)
        COMMENT 'Búsqueda rápida de asignación vigente por personal'
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Asignaciones de personal a oficinas con período de vigencia';


-- =============================================================
-- 5. ASISTENCIA
--    Un registro por persona por día.
--    El UNIQUE (personal_id, fecha) evita duplicados diarios.
--    entrada / salida son DATETIME para capturar hora exacta.
-- =============================================================
CREATE TABLE IF NOT EXISTS asistencia (
    id                      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    personal_id             INT UNSIGNED    NOT NULL,
    asignacion_oficina_id   INT UNSIGNED    NOT NULL,
    fecha                   DATE            NOT NULL
                                COMMENT 'Fecha del día laboral',
    entrada                 DATETIME            NULL DEFAULT NULL
                                COMMENT 'Registro de ingreso',
    salida                  DATETIME            NULL DEFAULT NULL
                                COMMENT 'Registro de salida',
    estado                  ENUM(
                                'presente',
                                'ausente',
                                'tardanza'
                            )               NOT NULL DEFAULT 'presente',
    creado_el               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_el          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_asistencia_personal_fecha (personal_id, fecha)
        COMMENT 'Un registro por persona por día',
    CONSTRAINT fk_as_asignacion_personal
        FOREIGN KEY (asignacion_oficina_id, personal_id)
        REFERENCES asignacion_oficina (id, personal_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_as_personal           (personal_id),
    INDEX idx_as_asignacion_oficina (asignacion_oficina_id),
    INDEX idx_as_fecha              (fecha),
    INDEX idx_as_fecha_estado       (fecha, estado)
        COMMENT 'Reportes filtrados por fecha y estado'
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Registro diario de asistencia del personal';

SET FOREIGN_KEY_CHECKS = 1;


-- =============================================================
--  DATOS INICIALES (semilla)
-- =============================================================

INSERT INTO tipo_personal (tipo) VALUES
    ('Administrativo'),
    ('Técnico'),
    ('Directivo'),
    ('Operativo'),
    ('Docente');

INSERT INTO oficina (nombre, descripcion) VALUES
    ('Recursos Humanos',    'Gestión de personal y nómina'),
    ('Tecnología',          'Infraestructura y sistemas informáticos'),
    ('Gerencia General',    'Dirección ejecutiva de la institución'),
    ('Operaciones',         'Logística y procesos operativos');


-- =============================================================
--  CONSULTAS PRINCIPALES DEL SISTEMA
-- =============================================================

-- -------------------------------------------------------------
-- Q1. MARCAR ENTRADA por CI
--     Busca la asignación vigente y registra la entrada.
--     Si ya existe el registro del día, solo actualiza entrada.
-- -------------------------------------------------------------
-- PASO 1: Obtener personal_id y asignacion_oficina_id activos
/*
SELECT
    p.id            AS personal_id,
    ao.id           AS asignacion_oficina_id,
    p.nombre,
    p.paterno,
    o.nombre        AS oficina,
    tp.tipo
FROM personal p
JOIN asignacion_oficina ao
    ON ao.personal_id = p.id
   AND ao.fecha_fin   IS NULL
   AND ao.estado      = 1
JOIN oficina o       ON o.id  = ao.oficina_id
JOIN tipo_personal tp ON tp.id = ao.tipo_personal_id
WHERE p.ci     = '1234567'    -- <-- CI escaneado
  AND p.estado = 1;
*/

-- PASO 2: Insertar o actualizar la asistencia del día
/*
INSERT INTO asistencia
    (personal_id, asignacion_oficina_id, fecha, entrada, estado)
VALUES
    (:personal_id, :asignacion_oficina_id, CURDATE(), NOW(), 'presente')
ON DUPLICATE KEY UPDATE
    entrada = IF(entrada IS NULL, NOW(), entrada);
    -- Si la entrada ya existe no se pisa; solo se registra una vez por día.
*/

-- -------------------------------------------------------------
-- Q2. MARCAR SALIDA por CI
-- -------------------------------------------------------------
/*
UPDATE asistencia
SET salida = NOW()
WHERE personal_id = :personal_id
  AND fecha       = CURDATE()
  AND salida      IS NULL;    -- Solo si aún no registró salida
*/

-- -------------------------------------------------------------
-- Q3. MARCAR TARDANZA
--     Se ejecuta si la entrada supera la hora límite permitida.
--     Ajustar '08:30:00' según política institucional.
-- -------------------------------------------------------------
/*
UPDATE asistencia
SET estado = 'tardanza'
WHERE personal_id = :personal_id
  AND fecha       = CURDATE()
  AND TIME(entrada) > '08:30:00';
*/

-- -------------------------------------------------------------
-- Q4. CONSULTAR ASISTENCIA POR OFICINA Y FECHA
-- -------------------------------------------------------------
/*
SELECT
    p.ci,
    CONCAT(p.paterno, ' ', COALESCE(p.materno,''), ' ', p.nombre) AS nombre_completo,
    tp.tipo                         AS cargo,
    a.fecha,
    TIME(a.entrada)                 AS hora_entrada,
    TIME(a.salida)                  AS hora_salida,
    CASE
        WHEN a.entrada IS NOT NULL AND a.salida IS NOT NULL
            THEN SEC_TO_TIME(TIMESTAMPDIFF(SECOND, a.entrada, a.salida))
        ELSE NULL
    END                             AS horas_trabajadas,
    a.estado
FROM asistencia a
JOIN personal           p  ON p.id  = a.personal_id
JOIN asignacion_oficina ao ON ao.id = a.asignacion_oficina_id
JOIN oficina            o  ON o.id  = ao.oficina_id
JOIN tipo_personal      tp ON tp.id = ao.tipo_personal_id
WHERE o.id    = :oficina_id          -- <-- ID de oficina
  AND a.fecha = :fecha               -- <-- '2025-04-07'
ORDER BY p.paterno, p.nombre;
*/

-- -------------------------------------------------------------
-- Q5. REPORTE DE ASISTENCIA POR RANGO DE FECHAS
-- -------------------------------------------------------------
/*
SELECT
    p.ci,
    CONCAT(p.paterno, ' ', p.nombre) AS nombre_completo,
    o.nombre                          AS oficina,
    COUNT(*)                          AS dias_registrados,
    SUM(a.estado = 'presente')        AS presentes,
    SUM(a.estado = 'tardanza')        AS tardanzas,
    SUM(a.estado = 'ausente')         AS ausentes
FROM asistencia a
JOIN personal           p  ON p.id  = a.personal_id
JOIN asignacion_oficina ao ON ao.id = a.asignacion_oficina_id
JOIN oficina            o  ON o.id  = ao.oficina_id
WHERE a.fecha BETWEEN :fecha_inicio AND :fecha_fin
GROUP BY p.id, o.id
ORDER BY o.nombre, p.paterno;
*/

-- -------------------------------------------------------------
-- Q6. BUSCAR ASIGNACIÓN VIGENTE DE UN PERSONAL (utilidad)
-- -------------------------------------------------------------
/*
SELECT
    ao.id                   AS asignacion_id,
    o.nombre                AS oficina,
    tp.tipo                 AS tipo_personal,
    ao.fecha_inicio,
    ao.fecha_fin
FROM asignacion_oficina ao
JOIN oficina       o  ON o.id  = ao.oficina_id
JOIN tipo_personal tp ON tp.id = ao.tipo_personal_id
WHERE ao.personal_id = :personal_id
ORDER BY ao.fecha_inicio DESC;
*/

-- -------------------------------------------------------------
-- Q7. CERRAR ASIGNACIÓN ANTERIOR AL REASIGNAR PERSONAL
--     Ejecutar ANTES de insertar la nueva asignación.
-- -------------------------------------------------------------
/*
UPDATE asignacion_oficina
SET fecha_fin      = CURDATE(),
    estado         = 0
WHERE personal_id  = :personal_id
  AND fecha_fin    IS NULL
  AND estado       = 1;
*/

-- =============================================================
--  FIN DEL SCRIPT
-- =============================================================
