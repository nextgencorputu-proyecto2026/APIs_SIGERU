-- ============================================================
-- Base de datos: recoleccion_residuos
-- Motor objetivo: MySQL 8.x
-- Charset: utf8mb4
-- ============================================================

DROP DATABASE IF EXISTS proyecto_sigeru;

SET NAMES utf8mb4;

CREATE DATABASE proyecto_sigeru
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE proyecto_sigeru;

-- ============================================================
-- TABLAS MAESTRAS
-- ============================================================

CREATE TABLE CENTRO (
    idCentro INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    direccion VARCHAR(120) NOT NULL,
    capacidad DECIMAL(10,2) NULL,
    tipo ENUM(
        'Central Operativa',
        'Centro de acopio',
        'Punto de depósito',
        'Vertedero'
    ) NOT NULL,
    ubicacionX DECIMAL(6,4) NOT NULL,
    ubicacionY DECIMAL(6,4) NOT NULL,

    CONSTRAINT pk_centro PRIMARY KEY (idCentro),
    CONSTRAINT chk_centro_capacidad CHECK (
        (tipo = 'Central Operativa' AND capacidad IS NULL)
        OR
        (tipo <> 'Central Operativa' AND capacidad IS NOT NULL AND capacidad > 0)
    )
) ENGINE=InnoDB;

CREATE TABLE USUARIO (
    idUsu INT UNSIGNED AUTO_INCREMENT,
    ci CHAR(8) NOT NULL,
    nombre1 VARCHAR(30) NOT NULL,
    nombre2 VARCHAR(30) NULL,
    apellido1 VARCHAR(30) NOT NULL,
    apellido2 VARCHAR(30) NULL,
    fec_nac DATE NOT NULL,
    tipo ENUM('Administrador', 'Operario', 'Chofer') NOT NULL,
    idCentro INT UNSIGNED NOT NULL,

    CONSTRAINT pk_usuario PRIMARY KEY (idUsu),
    CONSTRAINT uq_usuario_ci UNIQUE (ci),
    CONSTRAINT fk_usuario_centro
        FOREIGN KEY (idCentro) REFERENCES CENTRO(idCentro)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE CREDENCIALES (
    idUsu INT UNSIGNED NOT NULL,
    mail VARCHAR(254) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,

    CONSTRAINT pk_credenciales PRIMARY KEY (idUsu),
    CONSTRAINT uq_credenciales_mail UNIQUE (mail),
    CONSTRAINT fk_credenciales_usuario
        FOREIGN KEY (idUsu) REFERENCES USUARIO(idUsu)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE TELEFONO (
    idUsu INT UNSIGNED NOT NULL,
    telefono VARCHAR(20) NOT NULL,

    CONSTRAINT pk_telefono PRIMARY KEY (idUsu, telefono),
    CONSTRAINT fk_telefono_usuario
        FOREIGN KEY (idUsu) REFERENCES USUARIO(idUsu)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE VEHICULOS (
    idVehiculo INT UNSIGNED AUTO_INCREMENT,
    matricula VARCHAR(12) NOT NULL,
    marca VARCHAR(40) NOT NULL,
    tipo ENUM(
        'Camión recolector mixtos',
        'Camión recolector reciclables',
        'Camión de traslado',
        'Camioneta',
        'Barredora'
    ) NOT NULL,
    estado ENUM('Disponible', 'En mantenimiento', 'Inhabilitado') NOT NULL,
    capacidad DECIMAL(10,2) NULL,

    CONSTRAINT pk_vehiculos PRIMARY KEY (idVehiculo),
    CONSTRAINT uq_vehiculos_matricula UNIQUE (matricula),
    CONSTRAINT chk_vehiculos_capacidad CHECK (
        (
            tipo IN (
                'Camión recolector mixtos',
                'Camión recolector reciclables',
                'Camión de traslado'
            )
            AND capacidad IS NOT NULL
            AND capacidad > 0
        )
        OR
        (
            tipo IN ('Camioneta', 'Barredora')
            AND capacidad IS NULL
        )
    )
) ENGINE=InnoDB;

CREATE TABLE TALLER (
    idTaller INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    direccion VARCHAR(120) NOT NULL,
    telefono VARCHAR(20) NOT NULL,

    CONSTRAINT pk_taller PRIMARY KEY (idTaller)
) ENGINE=InnoDB;

CREATE TABLE RUTA (
    idRuta INT UNSIGNED AUTO_INCREMENT,
    descripcion VARCHAR(255) NOT NULL,
    horario TIME NOT NULL,
    tipoResiduo ENUM('Mixtos', 'Reciclables') NOT NULL,

    CONSTRAINT pk_ruta PRIMARY KEY (idRuta)
) ENGINE=InnoDB;

CREATE TABLE DIA (
    dia ENUM(
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo'
    ) NOT NULL,

    CONSTRAINT pk_dia PRIMARY KEY (dia)
) ENGINE=InnoDB;

CREATE TABLE CUADRILLA (
    idCuadrilla INT UNSIGNED AUTO_INCREMENT,

    CONSTRAINT pk_cuadrilla PRIMARY KEY (idCuadrilla)
) ENGINE=InnoDB;

CREATE TABLE INCIDENCIA (
    idIncidencia INT UNSIGNED AUTO_INCREMENT,
    tipo ENUM(
        'Vehículo averiado',
        'Sistema informático de vehículo defectuoso',
        'Sistema de recolección averiado',
        'Residuos fuera de contenedor',
        'Incendiado',
        'Roto',
        'Ruta a contenedor obstruida'
    ) NOT NULL,
    prioridad ENUM('Alta', 'Media', 'Baja') NOT NULL,
    estado ENUM('Pendiente', 'En curso', 'Resuelta') NOT NULL,
    fechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fechaResolucion DATETIME NULL,

    CONSTRAINT pk_incidencia PRIMARY KEY (idIncidencia),
    CONSTRAINT chk_incidencia_fechas CHECK (
        fechaResolucion IS NULL OR fechaResolucion >= fechaCreacion
    ),
    CONSTRAINT chk_incidencia_estado_resolucion CHECK (
        (estado = 'Resuelta' AND fechaResolucion IS NOT NULL)
        OR
        (estado <> 'Resuelta')
    )
) ENGINE=InnoDB;

-- ============================================================
-- RELACIONES / ENTIDADES ASOCIATIVAS
-- ============================================================

CREATE TABLE RECIBE_MANTENIMIENTO (
    idMantenimiento INT UNSIGNED AUTO_INCREMENT,
    idVehiculo INT UNSIGNED NOT NULL,
    idTaller INT UNSIGNED NOT NULL,
    fecha DATETIME NOT NULL,
    kilometraje INT UNSIGNED NOT NULL,
    descripcion VARCHAR(300) NOT NULL,
    estado ENUM('Pendiente', 'En proceso', 'Completado') NOT NULL,

    CONSTRAINT pk_recibe_mantenimiento PRIMARY KEY (idMantenimiento),
    CONSTRAINT fk_mantenimiento_vehiculo
        FOREIGN KEY (idVehiculo) REFERENCES VEHICULOS(idVehiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_mantenimiento_taller
        FOREIGN KEY (idTaller) REFERENCES TALLER(idTaller)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE CONTENEDOR (
    idContenedor INT UNSIGNED AUTO_INCREMENT,
    ubicacionX DECIMAL(6,4) NOT NULL,
    ubicacionY DECIMAL(6,4) NOT NULL,
    estado ENUM('Inhabilitado', 'En mantenimiento', 'Disponible') NOT NULL,
    nivelLlenado TINYINT UNSIGNED NOT NULL,
    tipo ENUM('Reciclables', 'Mixtos') NOT NULL,
    idRuta INT UNSIGNED NOT NULL,

    CONSTRAINT pk_contenedor PRIMARY KEY (idContenedor),
    CONSTRAINT chk_contenedor_nivel CHECK (nivelLlenado BETWEEN 0 AND 100),
    CONSTRAINT fk_contenedor_ruta
        FOREIGN KEY (idRuta) REFERENCES RUTA(idRuta)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE INTEGRA_CUADRILLA (
    idCuadrilla INT UNSIGNED NOT NULL,
    idUsu INT UNSIGNED NOT NULL,
    esResponsable BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT pk_integra_cuadrilla PRIMARY KEY (idCuadrilla, idUsu),
    CONSTRAINT fk_integra_cuadrilla
        FOREIGN KEY (idCuadrilla) REFERENCES CUADRILLA(idCuadrilla)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_integra_usuario
        FOREIGN KEY (idUsu) REFERENCES USUARIO(idUsu)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE CUADRILLA_VEHICULO (
    idCuadrilla INT UNSIGNED NOT NULL,
    idVehiculo INT UNSIGNED NOT NULL,

    CONSTRAINT pk_cuadrilla_vehiculo PRIMARY KEY (idCuadrilla, idVehiculo),
    CONSTRAINT fk_cv_cuadrilla
        FOREIGN KEY (idCuadrilla) REFERENCES CUADRILLA(idCuadrilla)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_cv_vehiculo
        FOREIGN KEY (idVehiculo) REFERENCES VEHICULOS(idVehiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE SE_REALIZA_EN (
    idRuta INT UNSIGNED NOT NULL,
    dia ENUM(
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo'
    ) NOT NULL,

    CONSTRAINT pk_se_realiza_en PRIMARY KEY (idRuta, dia),
    CONSTRAINT fk_se_realiza_ruta
        FOREIGN KEY (idRuta) REFERENCES RUTA(idRuta)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_se_realiza_dia
        FOREIGN KEY (dia) REFERENCES DIA(dia)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ASIGNA_RUTA (
    idAsignacion INT UNSIGNED AUTO_INCREMENT,
    idUsu INT UNSIGNED NOT NULL,
    idRuta INT UNSIGNED NOT NULL,
    idCuadrilla INT UNSIGNED NOT NULL,

    CONSTRAINT pk_asigna_ruta PRIMARY KEY (idAsignacion),
    CONSTRAINT fk_asigna_usuario
        FOREIGN KEY (idUsu) REFERENCES USUARIO(idUsu)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_asigna_ruta
        FOREIGN KEY (idRuta) REFERENCES RUTA(idRuta)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_asigna_cuadrilla
        FOREIGN KEY (idCuadrilla) REFERENCES CUADRILLA(idCuadrilla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE MAQUINARIA (
    idMaquinaria INT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    tipo ENUM('Aplanadora', 'Bobcat', 'Retroexcavadora') NOT NULL,
    estado ENUM('Inhabilitado', 'Disponible') NOT NULL,
    idCentro INT UNSIGNED NOT NULL,

    CONSTRAINT pk_maquinaria PRIMARY KEY (idMaquinaria),
    CONSTRAINT fk_maquinaria_centro
        FOREIGN KEY (idCentro) REFERENCES CENTRO(idCentro)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE TRASLADA (
    idTraslado INT UNSIGNED AUTO_INCREMENT,
    idVehiculo INT UNSIGNED NOT NULL,
    idCentroOrigen INT UNSIGNED NOT NULL,
    idCentroDestino INT UNSIGNED NOT NULL,
    fechaHora DATETIME NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,

    CONSTRAINT pk_traslada PRIMARY KEY (idTraslado),
    CONSTRAINT chk_traslada_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_traslada_centros CHECK (idCentroOrigen <> idCentroDestino),
    CONSTRAINT fk_traslada_vehiculo
        FOREIGN KEY (idVehiculo) REFERENCES VEHICULOS(idVehiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_traslada_origen
        FOREIGN KEY (idCentroOrigen) REFERENCES CENTRO(idCentro)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,
    CONSTRAINT fk_traslada_destino
        FOREIGN KEY (idCentroDestino) REFERENCES CENTRO(idCentro)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ATIENDE (
    idAtencion INT UNSIGNED AUTO_INCREMENT,
    idCuadrilla INT UNSIGNED NOT NULL,
    idVehiculo INT UNSIGNED NOT NULL,
    idIncidencia INT UNSIGNED NOT NULL,
    fechaHoraIni DATETIME NOT NULL,
    fechaHoraFin DATETIME NULL,

    CONSTRAINT pk_atiende PRIMARY KEY (idAtencion),
    CONSTRAINT chk_atiende_fechas CHECK (
        fechaHoraFin IS NULL OR fechaHoraFin > fechaHoraIni
    ),
    CONSTRAINT fk_atiende_cuadrilla
        FOREIGN KEY (idCuadrilla) REFERENCES CUADRILLA(idCuadrilla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_atiende_vehiculo
        FOREIGN KEY (idVehiculo) REFERENCES VEHICULOS(idVehiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_atiende_incidencia
        FOREIGN KEY (idIncidencia) REFERENCES INCIDENCIA(idIncidencia)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE CUMPLE_BARREDORA (
    idBarrido INT UNSIGNED AUTO_INCREMENT,
    idCuadrilla INT UNSIGNED NOT NULL,
    idVehiculo INT UNSIGNED NOT NULL,
    idRuta INT UNSIGNED NOT NULL,
    fechaHoraIni DATETIME NOT NULL,
    fechaHoraFin DATETIME NULL,

    CONSTRAINT pk_cumple_barredora PRIMARY KEY (idBarrido),
    CONSTRAINT chk_barrido_fechas CHECK (
        fechaHoraFin IS NULL OR fechaHoraFin > fechaHoraIni
    ),
    CONSTRAINT fk_barrido_cuadrilla
        FOREIGN KEY (idCuadrilla) REFERENCES CUADRILLA(idCuadrilla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_barrido_vehiculo
        FOREIGN KEY (idVehiculo) REFERENCES VEHICULOS(idVehiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_barrido_ruta
        FOREIGN KEY (idRuta) REFERENCES RUTA(idRuta)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE CUMPLE_RECOLECCION (
    idRecoleccion INT UNSIGNED AUTO_INCREMENT,
    idCuadrilla INT UNSIGNED NOT NULL,
    idVehiculo INT UNSIGNED NOT NULL,
    idRuta INT UNSIGNED NOT NULL,
    fechaHoraInicio DATETIME NOT NULL,
    fechaHoraFin DATETIME NULL,
    estado ENUM('En ejecución', 'Completado') NOT NULL,
    idCentroDestino INT UNSIGNED NOT NULL,

    CONSTRAINT pk_cumple_recoleccion PRIMARY KEY (idRecoleccion),
    CONSTRAINT chk_recoleccion_fechas CHECK (
        fechaHoraFin IS NULL OR fechaHoraFin > fechaHoraInicio
    ),
    CONSTRAINT chk_recoleccion_estado_fin CHECK (
        (estado = 'En ejecución' AND fechaHoraFin IS NULL)
        OR
        (estado = 'Completado' AND fechaHoraFin IS NOT NULL)
    ),
    CONSTRAINT fk_recoleccion_cuadrilla
        FOREIGN KEY (idCuadrilla) REFERENCES CUADRILLA(idCuadrilla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_recoleccion_vehiculo
        FOREIGN KEY (idVehiculo) REFERENCES VEHICULOS(idVehiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_recoleccion_ruta
        FOREIGN KEY (idRuta) REFERENCES RUTA(idRuta)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_recoleccion_centro
        FOREIGN KEY (idCentroDestino) REFERENCES CENTRO(idCentro)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE VEHICULO_PRESENTA (
    idIncidencia INT UNSIGNED NOT NULL,
    idVehiculo INT UNSIGNED NOT NULL,

    CONSTRAINT pk_vehiculo_presenta PRIMARY KEY (idIncidencia),
    CONSTRAINT fk_vp_incidencia
        FOREIGN KEY (idIncidencia) REFERENCES INCIDENCIA(idIncidencia)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_vp_vehiculo
        FOREIGN KEY (idVehiculo) REFERENCES VEHICULOS(idVehiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE CONTENEDOR_PRESENTA (
    idIncidencia INT UNSIGNED NOT NULL,
    idContenedor INT UNSIGNED NOT NULL,

    CONSTRAINT pk_contenedor_presenta PRIMARY KEY (idIncidencia),
    CONSTRAINT fk_cp_incidencia
        FOREIGN KEY (idIncidencia) REFERENCES INCIDENCIA(idIncidencia)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_cp_contenedor
        FOREIGN KEY (idContenedor) REFERENCES CONTENEDOR(idContenedor)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE REPORTA (
    idUsu INT UNSIGNED NOT NULL,
    idIncidencia INT UNSIGNED NOT NULL,

    CONSTRAINT pk_reporta PRIMARY KEY (idUsu, idIncidencia),
    CONSTRAINT fk_reporta_usuario
        FOREIGN KEY (idUsu) REFERENCES USUARIO(idUsu)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_reporta_incidencia
        FOREIGN KEY (idIncidencia) REFERENCES INCIDENCIA(idIncidencia)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE CONTROLA (
    idUsu INT UNSIGNED NOT NULL,
    idIncidencia INT UNSIGNED NOT NULL,

    CONSTRAINT pk_controla PRIMARY KEY (idUsu, idIncidencia),
    CONSTRAINT fk_controla_usuario
        FOREIGN KEY (idUsu) REFERENCES USUARIO(idUsu)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_controla_incidencia
        FOREIGN KEY (idIncidencia) REFERENCES INCIDENCIA(idIncidencia)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- CARGA DEL DOMINIO DIA
-- ============================================================
INSERT INTO DIA (dia) VALUES
('Lunes'),
('Martes'),
('Miércoles'),
('Jueves'),
('Viernes'),
('Sábado'),
('Domingo');

-- ============================================================
-- RNE QUE REQUIEREN VALIDACIÓN ENTRE TABLAS / TEMPORAL
-- Se recomienda implementarlas en Laravel y, si se desea reforzar
-- la integridad a nivel de BD, mediante triggers/procedimientos.
-- ============================================================
-- 1) INTEGRA_CUADRILLA: cada cuadrilla debe incluir al menos un Chofer
--    y uno o más Operarios; exactamente un responsable según la RNE.
-- 2) CONTROLA: idUsu debe corresponder a un USUARIO.tipo='Administrador'.
-- 3) CUMPLE_BARREDORA: idVehiculo debe ser VEHICULOS.tipo='Barredora'.
-- 4) CUMPLE_RECOLECCION: idVehiculo solo puede ser Camión recolector mixtos
--    o Camión recolector reciclables.
-- 5) Compatibilidad RUTA.tipoResiduo / CONTENEDOR.tipo / VEHICULOS.tipo.
-- 6) Centro destino de recolección:
--      Reciclables -> Centro de acopio
--      Mixtos      -> Punto de depósito
-- 7) TRASLADA: solo Camión de traslado y recorrido Punto de depósito -> Vertedero.
-- 8) Un vehículo En mantenimiento/Inhabilitado no puede participar en
--    recolección, barrido o traslado.
-- 9) No permitir solapamientos temporales de cuadrillas/vehículos.
-- 10) Una incidencia debe aparecer en VEHICULO_PRESENTA o CONTENEDOR_PRESENTA,
--     nunca en ambas; su INCIDENCIA.tipo debe ser compatible con el objeto.
-- 11) Una ruta debe estar asociada a por lo menos 1 y como máximo 2 días
--     distintos en SE_REALIZA_EN.

START TRANSACTION;

-- ============================================================
-- 1. CENTROS
-- Central Operativa: capacidad NULL.
-- Resto de centros: capacidad > 0.
-- ============================================================
INSERT INTO CENTRO
(idCentro, nombre, direccion, capacidad, tipo, ubicacionX, ubicacionY)
VALUES
(1, 'Central Operativa SIGERU', 'Av. 18 de Julio 1360', NULL, 'Central Operativa', -34.9058, -56.1913),
(2, 'Centro de Acopio Norte', 'Camino Edison 2450', 8500.00, 'Centro de acopio', -34.8432, -56.2301),
(3, 'Centro de Acopio Este', 'Cno. Maldonado 5100', 6200.00, 'Centro de acopio', -34.8544, -56.1095),
(4, 'Punto de Depósito Oeste', 'Av. Luis Batlle Berres 4200', 10000.00, 'Punto de depósito', -34.8717, -56.2518),
(5, 'Punto de Depósito Centro', 'Av. Dámaso A. Larrañaga 3100', 7800.00, 'Punto de depósito', -34.8772, -56.1605),
(6, 'Vertedero Municipal', 'Camino Felipe Cardoso 3220', 25000.00, 'Vertedero', -34.8340, -56.0790);

-- ============================================================
-- 2. USUARIOS
-- ============================================================
INSERT INTO USUARIO
(idUsu, ci, nombre1, nombre2, apellido1, apellido2, fec_nac, tipo, idCentro)
VALUES
(1,  '41000001', 'Ana',     'María',  'Pereira',   'Silva',     '1985-03-12', 'Administrador', 1),
(2,  '42000002', 'Martín',   NULL,     'Rodríguez', 'Sosa',      '1988-07-24', 'Administrador', 1),
(3,  '43000003', 'Carlos',   'Andrés', 'Gómez',     'López',     '1990-01-16', 'Chofer',         1),
(4,  '44000004', 'Laura',    NULL,     'Fernández', 'Pérez',     '1992-09-03', 'Operario',       1),
(5,  '45000005', 'Diego',    'Martín', 'Silva',     'Acosta',    '1987-11-21', 'Operario',       1),
(6,  '46000006', 'Sofía',    'Elena',  'Martínez',  'Ramos',     '1994-06-15', 'Chofer',         1),
(7,  '47000007', 'Pablo',    NULL,     'Suárez',    'Méndez',    '1989-12-10', 'Operario',       1),
(8,  '48000008', 'Valentina',NULL,     'Cabrera',   'Núñez',     '1996-02-28', 'Operario',       1),
(9,  '49000009', 'Jorge',    'Luis',   'Techera',   'Viera',     '1983-08-09', 'Chofer',         1),
(10, '50000010', 'Camila',   NULL,     'Olivera',   'Díaz',      '1995-04-19', 'Operario',       1),
(11, '51000011', 'Bruno',    'Nicolás','Correa',    'Machado',   '1991-10-30', 'Operario',       1),
(12, '52000012', 'Lucía',    NULL,     'Morales',   'Barrios',   '1993-05-11', 'Chofer',         1);

-- ============================================================
-- 3. CREDENCIALES
-- Contraseña para todos: Prueba123!
-- ============================================================
INSERT INTO CREDENCIALES (idUsu, mail, contrasena)
VALUES
(1,  'ana.pereira@sigeru.test',      '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(2,  'martin.rodriguez@sigeru.test', '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(3,  'carlos.gomez@sigeru.test',     '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(4,  'laura.fernandez@sigeru.test',  '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(5,  'diego.silva@sigeru.test',      '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(6,  'sofia.martinez@sigeru.test',   '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(7,  'pablo.suarez@sigeru.test',     '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(8,  'valentina.cabrera@sigeru.test','$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(9,  'jorge.techera@sigeru.test',    '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(10, 'camila.olivera@sigeru.test',   '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(11, 'bruno.correa@sigeru.test',     '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu'),
(12, 'lucia.morales@sigeru.test',    '$2y$12$rdX5p7XcYyQ7mrF8scivcesKAau4wSFlQP..T9tvaAAYzPVTIJ4Eu');

-- ============================================================
-- 4. TELÉFONOS
-- Algunos usuarios tienen más de un teléfono para probar la PK compuesta.
-- ============================================================
INSERT INTO TELEFONO (idUsu, telefono)
VALUES
(1, '099100001'),
(1, '29001001'),
(2, '099100002'),
(3, '099100003'),
(4, '099100004'),
(5, '099100005'),
(6, '099100006'),
(7, '099100007'),
(8, '099100008'),
(9, '099100009'),
(10,'099100010'),
(11,'099100011'),
(12,'099100012');

-- ============================================================
-- 5. VEHÍCULOS
-- Camiones: capacidad > 0.
-- Camioneta / Barredora: capacidad NULL.
-- ============================================================
INSERT INTO VEHICULOS
(idVehiculo, matricula, marca, tipo, estado, capacidad)
VALUES
(1, 'STC1001', 'Mercedes-Benz', 'Camión recolector mixtos',      'Disponible',       8500.00),
(2, 'STC1002', 'Volvo',         'Camión recolector reciclables', 'Disponible',       7200.00),
(3, 'STC1003', 'Iveco',         'Camión de traslado',            'Disponible',      12000.00),
(4, 'STC1004', 'Volkswagen',    'Camión recolector mixtos',      'En mantenimiento', 9000.00),
(5, 'STC1005', 'Ford',          'Camioneta',                     'Disponible',          NULL),
(6, 'STC1006', 'Scania',        'Barredora',                     'Disponible',          NULL),
(7, 'STC1007', 'Renault',       'Camión recolector reciclables', 'Inhabilitado',     7000.00),
(8, 'STC1008', 'Chevrolet',     'Camioneta',                     'Disponible',          NULL);

-- ============================================================
-- 6. TALLERES Y MANTENIMIENTOS
-- ============================================================
INSERT INTO TALLER (idTaller, nombre, direccion, telefono)
VALUES
(1, 'Taller Municipal Central', 'Av. General Flores 4020', '22110001'),
(2, 'Mecánica del Sur',         'Av. Italia 4550',         '25090002');

INSERT INTO RECIBE_MANTENIMIENTO
(idMantenimiento, idVehiculo, idTaller, fecha, kilometraje, descripcion, estado)
VALUES
(1, 4, 1, '2026-09-10 08:30:00', 184500, 'Revisión del sistema hidráulico de compactación.', 'En proceso'),
(2, 1, 2, '2026-08-20 10:00:00', 126000, 'Cambio de aceite, filtros y revisión general.',    'Completado'),
(3, 7, 1, '2026-09-08 09:15:00', 211400, 'Diagnóstico de falla eléctrica general.',         'Pendiente');

-- ============================================================
-- 7. RUTAS
-- ============================================================
INSERT INTO RUTA (idRuta, descripcion, horario, tipoResiduo)
VALUES
(1, 'Ruta Centro - residuos mixtos',          '06:00:00', 'Mixtos'),
(2, 'Ruta Pocitos - residuos reciclables',    '07:00:00', 'Reciclables'),
(3, 'Ruta Cordón - residuos mixtos',          '14:00:00', 'Mixtos'),
(4, 'Ruta Parque Batlle - reciclables',       '16:00:00', 'Reciclables');

-- Cada ruta queda asociada a 1 o 2 días.
INSERT INTO SE_REALIZA_EN (idRuta, dia)
VALUES
(1, 'Lunes'),
(1, 'Jueves'),
(2, 'Martes'),
(2, 'Viernes'),
(3, 'Miércoles'),
(4, 'Sábado');

-- ============================================================
-- 8. CONTENEDORES
-- El tipo coincide con el tipoResiduo de su ruta.
-- ============================================================
INSERT INTO CONTENEDOR
(idContenedor, ubicacionX, ubicacionY, estado, nivelLlenado, tipo, idRuta)
VALUES
(1,  -56.1908, -34.9055, 'Disponible',       72, 'Mixtos',      1),
(2,  -56.1875, -34.9071, 'Disponible',       45, 'Mixtos',      1),
(3,  -56.1842, -34.9104, 'En mantenimiento', 20, 'Mixtos',      1),
(4,  -56.1504, -34.9108, 'Disponible',       65, 'Reciclables', 2),
(5,  -56.1462, -34.9131, 'Disponible',       88, 'Reciclables', 2),
(6,  -56.1431, -34.9160, 'Disponible',       35, 'Reciclables', 2),
(7,  -56.1400, -34.9003, 'Disponible',       91, 'Mixtos',      3),
(8,  -56.1370, -34.8974, 'Disponible',       55, 'Mixtos',      3),
(9,  -56.1340, -34.8949, 'Inhabilitado',    100, 'Mixtos',      3),
(10, -56.1310, -34.8925, 'Disponible',       18, 'Reciclables', 4),
(11, -56.1280, -34.8899, 'Disponible',       60, 'Reciclables', 4),
(12, -56.1250, -34.8870, 'Disponible',       80, 'Reciclables', 4);

-- ============================================================
-- 9. CUADRILLAS
-- Cada una: 1 chofer + 2 operarios y exactamente un responsable.
-- El responsable se fija en un operario.
-- ============================================================
INSERT INTO CUADRILLA (idCuadrilla)
VALUES (1), (2), (3), (4);

INSERT INTO INTEGRA_CUADRILLA (idCuadrilla, idUsu, esResponsable)
VALUES
-- Cuadrilla 1
(1, 3, FALSE),
(1, 4, TRUE),
(1, 5, FALSE),
-- Cuadrilla 2
(2, 6, FALSE),
(2, 7, TRUE),
(2, 8, FALSE),
-- Cuadrilla 3
(3, 9, FALSE),
(3,10, TRUE),
(3,11, FALSE),
-- Cuadrilla 4
(4,12, FALSE),
(4, 4, TRUE),
(4, 8, FALSE);

-- Vehículos disponibles asociados a cuadrillas.
INSERT INTO CUADRILLA_VEHICULO (idCuadrilla, idVehiculo)
VALUES
(1,1),
(2,2),
(3,6),
(4,5);

-- ============================================================
-- 10. ASIGNACIONES DE RUTA
-- Se registra un administrador como usuario que realiza la asignación.
-- ============================================================
INSERT INTO ASIGNA_RUTA (idAsignacion, idUsu, idRuta, idCuadrilla)
VALUES
(1, 1, 1, 1),
(2, 1, 2, 2),
(3, 2, 3, 1),
(4, 2, 4, 2);

-- ============================================================
-- 11. MAQUINARIA
-- ============================================================
INSERT INTO MAQUINARIA (idMaquinaria, nombre, tipo, estado, idCentro)
VALUES
(1, 'Aplanadora A-01',       'Aplanadora',       'Disponible',   6),
(2, 'Bobcat B-02',           'Bobcat',           'Disponible',   4),
(3, 'Retroexcavadora R-03',  'Retroexcavadora',  'Inhabilitado', 6),
(4, 'Bobcat B-04',           'Bobcat',           'Disponible',   5);

-- ============================================================
-- 12. TRASLADOS
-- Solo Camión de traslado (vehículo 3), desde Punto de depósito
-- hacia Vertedero Municipal.
-- ============================================================
INSERT INTO TRASLADA
(idTraslado, idVehiculo, idCentroOrigen, idCentroDestino, fechaHora, cantidad)
VALUES
(1, 3, 4, 6, '2026-09-12 11:20:00', 5400.00),
(2, 3, 5, 6, '2026-09-13 15:40:00', 4800.00);

-- ============================================================
-- 13. INCIDENCIAS
-- Tipos compatibles con el objeto y cada incidencia pertenece
-- solamente a VEHICULO_PRESENTA o CONTENEDOR_PRESENTA.
-- ============================================================
INSERT INTO INCIDENCIA
(idIncidencia, tipo, prioridad, estado, fechaCreacion, fechaResolucion)
VALUES
(1, 'Vehículo averiado',                         'Alta',  'En curso',  '2026-09-10 08:00:00', NULL),
(2, 'Sistema informático de vehículo defectuoso','Media', 'Resuelta',  '2026-09-05 09:10:00', '2026-09-05 12:30:00'),
(3, 'Residuos fuera de contenedor',              'Media', 'Pendiente', '2026-09-13 07:25:00', NULL),
(4, 'Roto',                                      'Alta',  'En curso',  '2026-09-12 16:15:00', NULL),
(5, 'Ruta a contenedor obstruida',               'Baja',  'Resuelta',  '2026-09-11 06:45:00', '2026-09-11 08:00:00'),
(6, 'Sistema de recolección averiado',           'Alta',  'Pendiente', '2026-09-14 06:30:00', NULL);

INSERT INTO VEHICULO_PRESENTA (idIncidencia, idVehiculo)
VALUES
(1,4),
(2,1),
(6,7);

INSERT INTO CONTENEDOR_PRESENTA (idIncidencia, idContenedor)
VALUES
(3,7),
(4,9),
(5,5);

-- Quién reportó cada incidencia.
INSERT INTO REPORTA (idUsu, idIncidencia)
VALUES
(3,1),
(3,2),
(9,3),
(10,4),
(6,5),
(12,6);

-- Solo administradores controlan incidencias.
INSERT INTO CONTROLA (idUsu, idIncidencia)
VALUES
(1,1),
(1,2),
(2,3),
(2,4),
(1,5),
(2,6);

-- ============================================================
-- 14. ATENCIÓN DE INCIDENCIAS
-- Se usa camioneta disponible para asistencia.
-- ============================================================
INSERT INTO ATIENDE
(idAtencion, idCuadrilla, idVehiculo, idIncidencia, fechaHoraIni, fechaHoraFin)
VALUES
(1, 4, 5, 2, '2026-09-05 10:00:00', '2026-09-05 12:15:00'),
(2, 4, 5, 5, '2026-09-11 07:00:00', '2026-09-11 07:50:00'),
(3, 4, 8, 4, '2026-09-12 16:40:00', NULL);

-- ============================================================
-- 15. BARRIDO
-- Solo vehículo tipo Barredora (idVehiculo = 6).
-- ============================================================
INSERT INTO CUMPLE_BARREDORA
(idBarrido, idCuadrilla, idVehiculo, idRuta, fechaHoraIni, fechaHoraFin)
VALUES
(1, 3, 6, 3, '2026-09-09 14:00:00', '2026-09-09 17:10:00'),
(2, 3, 6, 3, '2026-09-14 14:00:00', NULL);

-- ============================================================
-- 16. RECOLECCIONES
-- Mixtos -> Punto de depósito.
-- Reciclables -> Centro de acopio.
-- Solo camiones recolectores disponibles.
-- ============================================================
INSERT INTO CUMPLE_RECOLECCION
(idRecoleccion, idCuadrilla, idVehiculo, idRuta, fechaHoraInicio, fechaHoraFin, estado, idCentroDestino)
VALUES
(1, 1, 1, 1, '2026-09-07 06:00:00', '2026-09-07 09:20:00', 'Completado',   4),
(2, 2, 2, 2, '2026-09-08 07:00:00', '2026-09-08 10:10:00', 'Completado',   2),
(3, 1, 1, 1, '2026-09-14 06:00:00', NULL,                  'En ejecución', 4),
(4, 2, 2, 2, '2026-09-13 07:00:00', NULL,                  'En ejecución', 3);

COMMIT;
