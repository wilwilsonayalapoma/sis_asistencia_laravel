-- =============================================================
--  MIGRACIÓN: Agregar tabla TURNO y actualizar ASIGNACION_OFICINA
--  Para base de datos: sistema_asistencia existente
--  Generado: 2026-04-15
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Crear tabla TURNO si no existe
CREATE TABLE IF NOT EXISTS turno (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(60)     NOT NULL,
    hora_entrada    TIME            NOT NULL
                        COMMENT 'Hora oficial de entrada',
    hora_tardanza   TIME            NOT NULL
                        COMMENT 'Hora límite para evitar tardanza',
    hora_salida     TIME                NULL DEFAULT NULL
                        COMMENT 'Hora esperada de salida (informativo)',
    estado          TINYINT(1)      NOT NULL DEFAULT 1
                        COMMENT '1=activo, 0=inactivo',
    creado_el       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_el  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_turno_nombre (nombre),
    INDEX idx_turno_estado (estado)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci
  COMMENT='Definición de turnos de trabajo';

-- Insertar turnos por defecto si es la primera vez
INSERT IGNORE INTO turno (nombre, hora_entrada, hora_tardanza, hora_salida) VALUES
    ('Mañana',          '08:00:00', '08:30:00', '16:30:00'),
    ('Tarde',           '14:00:00', '14:30:00', '22:30:00'),
    ('Noche',           '22:00:00', '22:30:00', '06:30:00'),
    ('Flexible',        '07:00:00', '09:00:00', NULL);

-- Agregar columna turno_id a asignacion_oficina si no existe
ALTER TABLE asignacion_oficina
ADD COLUMN turno_id INT UNSIGNED NOT NULL DEFAULT 1
    COMMENT 'Referencia al turno asignado'
    AFTER tipo_personal_id;

-- Agregar constraint (FK) a turno si no existe
ALTER TABLE asignacion_oficina
ADD CONSTRAINT fk_ao_turno
    FOREIGN KEY (turno_id) REFERENCES turno (id)
    ON UPDATE CASCADE ON DELETE RESTRICT;

-- Agregar índice para turno_id si no existe
ALTER TABLE asignacion_oficina
ADD INDEX idx_ao_turno (turno_id);

SET FOREIGN_KEY_CHECKS = 1;
