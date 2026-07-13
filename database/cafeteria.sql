-- ============================================================================
-- SISTEMA DE GESTIÓN DE CAFETERÍA
-- ============================================================================
-- Proyecto  : Sistema de Gestión de Cafetería
-- Autor     : Jesús Alexis Aldana García
-- Fecha     : 2026-07-01
-- Descripción: Script SQL completo que crea la base de datos, tablas,
--              datos de ejemplo y modificaciones estructurales para el
--              sistema de gestión de una cafetería.
-- Motor     : MySQL / MariaDB
-- Charset   : utf8mb4 (soporte completo de caracteres Unicode)
-- Indicador : 4 — CREATE DATABASE, CREATE TABLE, INSERT, ALTER
-- ============================================================================


-- ============================================================================
-- SECCIÓN 1: CREACIÓN DE LA BASE DE DATOS
-- ============================================================================
-- Se crea la base de datos con codificación utf8mb4 para soportar caracteres
-- especiales, emojis y acentos del idioma español.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS cafeteria_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cafeteria_db;


-- ============================================================================
-- SECCIÓN 2: CREACIÓN DE TABLAS (CREATE TABLE)
-- ============================================================================
-- Las tablas se crean en orden de dependencias para respetar las llaves
-- foráneas. Primero las tablas independientes y después las que dependen
-- de ellas.
-- ============================================================================


-- ----------------------------------------------------------------------------
-- Tabla: categorias
-- Descripción: Almacena las categorías de los productos de la cafetería.
-- ----------------------------------------------------------------------------

CREATE TABLE categorias (
    id_categoria    INT             AUTO_INCREMENT  PRIMARY KEY,
    nombre          VARCHAR(100)    NOT NULL        UNIQUE,
    descripcion     TEXT            NULL,
    fecha_creacion  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
-- Tabla: productos
-- Descripción: Almacena los productos disponibles en la cafetería con su
--              precio, stock y categoría asociada.
-- ----------------------------------------------------------------------------

CREATE TABLE productos (
    id_producto     INT             AUTO_INCREMENT  PRIMARY KEY,
    nombre          VARCHAR(150)    NOT NULL,
    descripcion     TEXT            NULL,
    precio          DECIMAL(10,2)   NOT NULL,
    stock           INT             NOT NULL        DEFAULT 0,
    id_categoria    INT             NOT NULL,
    imagen          VARCHAR(255)    NULL,
    activo          TINYINT(1)      DEFAULT 1,
    fecha_creacion  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,

    -- Llave foránea hacia la tabla categorias
    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
-- Tabla: clientes
-- Descripción: Registra la información de los clientes de la cafetería.
-- ----------------------------------------------------------------------------

CREATE TABLE clientes (
    id_cliente      INT             AUTO_INCREMENT  PRIMARY KEY,
    nombre          VARCHAR(100)    NOT NULL,
    apellido        VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    UNIQUE          NULL,
    telefono        VARCHAR(15)     NULL,
    fecha_registro  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
-- Tabla: empleados
-- Descripción: Registra la información de los empleados que laboran en la
--              cafetería (cajeros, baristas, etc.).
-- ----------------------------------------------------------------------------

CREATE TABLE empleados (
    id_empleado         INT             AUTO_INCREMENT  PRIMARY KEY,
    nombre              VARCHAR(100)    NOT NULL,
    apellido            VARCHAR(100)    NOT NULL,
    puesto              VARCHAR(50)     NOT NULL,
    telefono            VARCHAR(15)     NULL,
    username            VARCHAR(50)     NOT NULL UNIQUE,
    password            VARCHAR(255)    NOT NULL,
    activo              TINYINT(1)      DEFAULT 1,
    fecha_contratacion  DATE            NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
-- Tabla: pedidos
-- Descripción: Registra los pedidos realizados por los clientes, incluyendo
--              el empleado que los atendió, el total, estado y método de pago.
-- ----------------------------------------------------------------------------

CREATE TABLE pedidos (
    id_pedido       INT             AUTO_INCREMENT  PRIMARY KEY,
    id_cliente      INT             NOT NULL,
    id_empleado     INT             NOT NULL,
    fecha_pedido    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    total           DECIMAL(10,2)   NOT NULL        DEFAULT 0.00,
    estado          ENUM('pendiente','en_preparacion','completado','cancelado')
                                    DEFAULT 'pendiente',
    metodo_pago     ENUM('efectivo','tarjeta','transferencia')
                                    DEFAULT 'efectivo',

    -- Llave foránea hacia la tabla clientes
    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    -- Llave foránea hacia la tabla empleados
    CONSTRAINT fk_pedido_empleado
        FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------------------------------------------------------
-- Tabla: detalle_pedidos
-- Descripción: Almacena el detalle de cada pedido, es decir, los productos
--              individuales, cantidades y subtotales que componen un pedido.
-- ----------------------------------------------------------------------------

CREATE TABLE detalle_pedidos (
    id_detalle      INT             AUTO_INCREMENT  PRIMARY KEY,
    id_pedido       INT             NOT NULL,
    id_producto     INT             NOT NULL,
    cantidad        INT             NOT NULL        DEFAULT 1,
    precio_unitario DECIMAL(10,2)   NOT NULL,
    subtotal        DECIMAL(10,2)   NOT NULL,

    -- Llave foránea hacia la tabla pedidos (CASCADE al eliminar pedido)
    CONSTRAINT fk_detalle_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- Llave foránea hacia la tabla productos
    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- SECCIÓN 3: INSERCIÓN DE DATOS DE EJEMPLO (INSERT)
-- ============================================================================
-- Se insertan registros de ejemplo para poblar la base de datos y poder
-- realizar pruebas con datos realistas.
-- ============================================================================


-- ----------------------------------------------------------------------------
-- Insertar categorías (5 registros)
-- ----------------------------------------------------------------------------

INSERT INTO categorias (nombre, descripcion) VALUES
('Bebidas Calientes',   'Café, té, chocolate y bebidas calientes'),
('Bebidas Frías',       'Frappés, smoothies, jugos y bebidas frías'),
('Alimentos',           'Sándwiches, paninis, ensaladas y platillos'),
('Postres',             'Pasteles, galletas, muffins y repostería'),
('Snacks',              'Papas, frutos secos, barras energéticas');


-- ----------------------------------------------------------------------------
-- Insertar productos (15 registros)
-- Los id_categoria corresponden al orden de inserción de las categorías:
--   1 = Bebidas Calientes, 2 = Bebidas Frías, 3 = Alimentos,
--   4 = Postres, 5 = Snacks
-- ----------------------------------------------------------------------------

INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria) VALUES
('Café Americano',       'Café negro filtrado, intenso y aromático',              35.00, 100, 1),
('Cappuccino',           'Espresso con leche espumada y espuma de leche',         55.00,  80, 1),
('Latte',                'Espresso con abundante leche vaporizada',               50.00,  80, 1),
('Té Verde',             'Infusión de té verde natural, antioxidante',            30.00,  60, 1),
('Chocolate Caliente',   'Chocolate artesanal con leche caliente',                45.00,  70, 1),
('Frappé de Moka',       'Café frappé con chocolate y crema batida',              65.00,  50, 2),
('Smoothie de Fresa',    'Smoothie natural de fresa con yogurt',                  55.00,  40, 2),
('Limonada Natural',     'Limonada fresca preparada con limones naturales',       30.00,  60, 2),
('Sándwich de Jamón',    'Pan artesanal con jamón, queso y vegetales frescos',    50.00,  30, 3),
('Panini Caprese',       'Panini con mozzarella, tomate y albahaca fresca',       60.00,  25, 3),
('Ensalada César',       'Lechuga romana, crutones, parmesano y aderezo césar',   55.00,  20, 3),
('Pastel de Chocolate',  'Rebanada de pastel de chocolate con ganache',           45.00,  15, 4),
('Muffin de Arándano',   'Muffin esponjoso con arándanos frescos',               35.00,  25, 4),
('Galletas de Avena',    'Galletas caseras de avena con chispas de chocolate',    25.00,  40, 5),
('Barra de Granola',     'Barra energética de granola con miel y nueces',         20.00,  50, 5);


-- ----------------------------------------------------------------------------
-- Insertar clientes (5 registros)
-- ----------------------------------------------------------------------------

INSERT INTO clientes (nombre, apellido, email, telefono) VALUES
('María',   'García',       'maria.garcia@email.com',   '6141234567'),
('Juan',    'López',        'juan.lopez@email.com',     '6149876543'),
('Ana',     'Martínez',     'ana.martinez@email.com',   '6145551234'),
('Carlos',  'Hernández',    'carlos.hdz@email.com',     '6143334455'),
('Laura',   'Rodríguez',    'laura.rdz@email.com',      '6147778899');


-- ----------------------------------------------------------------------------
-- Insertar empleados (3 registros)
-- ----------------------------------------------------------------------------

INSERT INTO empleados (nombre, apellido, puesto, telefono, username, password, fecha_contratacion) VALUES
('Pedro',   'Sánchez',  'Cajero',   '6142223344', 'pedro', '123456', '2025-01-15'),
('Sofía',   'Ramírez',  'Barista',  '6145556677', 'sofia', '123456', '2025-03-01'),
('Diego',   'Torres',   'Administrador', '6148889900', 'admin', 'admin123', '2025-06-10');


-- ----------------------------------------------------------------------------
-- Insertar pedidos (5 registros)
-- NOTA: Los totales corresponden a la suma exacta de los subtotales de cada
-- pedido en la tabla detalle_pedidos.
--   Pedido 1: 35.00 + 50.00 + 25.00 = 110.00
--   Pedido 2: 65.00                  =  65.00
--   Pedido 3: 110.00 + 30.00         = 140.00
--   Pedido 4: 50.00                  =  50.00
--   Pedido 5: 45.00 + 30.00          =  75.00
-- ----------------------------------------------------------------------------

INSERT INTO pedidos (id_cliente, id_empleado, total, estado, metodo_pago) VALUES
(1, 1, 110.00, 'completado',       'tarjeta'),
(2, 2,  65.00, 'completado',       'efectivo'),
(3, 1, 140.00, 'en_preparacion',   'efectivo'),
(4, 3,  50.00, 'pendiente',        'transferencia'),
(5, 2,  75.00, 'completado',       'tarjeta');


-- ----------------------------------------------------------------------------
-- Insertar detalle de pedidos (9 registros)
-- Cada registro indica un producto específico dentro de un pedido, con su
-- cantidad, precio unitario y subtotal calculado.
-- Los subtotales deben coincidir con los totales de la tabla pedidos.
-- ----------------------------------------------------------------------------

-- Pedido 1: Café Americano x1 (35.00) + Sándwich de Jamón x1 (50.00)
--           + Galletas de Avena x1 (25.00) = 110.00
INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(1, 1,  1, 35.00,  35.00),
(1, 9,  1, 50.00,  50.00),
(1, 14, 1, 25.00,  25.00);

-- Pedido 2: Frappé de Moka x1 (65.00) = 65.00
INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(2, 6, 1, 65.00, 65.00);

-- Pedido 3: Cappuccino x2 (55.00 x 2 = 110.00) + Limonada Natural x1 (30.00) = 140.00
INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(3, 2, 2, 55.00, 110.00),
(3, 8, 1, 30.00,  30.00);

-- Pedido 4: Latte x1 (50.00) = 50.00
INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(4, 3, 1, 50.00, 50.00);

-- Pedido 5: Pastel de Chocolate x1 (45.00) + Té Verde x1 (30.00) = 75.00
INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(5, 12, 1, 45.00, 45.00),
(5, 4,  1, 30.00, 30.00);


-- ============================================================================
-- SECCIÓN 4: MODIFICACIÓN DE ESTRUCTURA (ALTER TABLE)
-- ============================================================================
-- Se realizan modificaciones a las tablas existentes para demostrar el
-- dominio del comando ALTER TABLE.
-- ============================================================================


-- ----------------------------------------------------------------------------
-- Agregar campo fecha_actualizacion a la tabla productos
-- Este campo se actualizará automáticamente cada vez que se modifique
-- un registro de producto.
-- ----------------------------------------------------------------------------

ALTER TABLE productos ADD COLUMN fecha_actualizacion TIMESTAMP
    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;


-- ----------------------------------------------------------------------------
-- Agregar campo direccion a la tabla clientes
-- Se ubica después del campo telefono usando la cláusula AFTER.
-- ----------------------------------------------------------------------------

ALTER TABLE clientes ADD COLUMN direccion VARCHAR(255) NULL AFTER telefono;


-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
-- Base de datos creada exitosamente con:
--   • 1 base de datos (cafeteria_db)
--   • 6 tablas (categorias, productos, clientes, empleados, pedidos,
--     detalle_pedidos)
--   • 38 registros de ejemplo en total
--   • 2 modificaciones ALTER TABLE
-- ============================================================================
