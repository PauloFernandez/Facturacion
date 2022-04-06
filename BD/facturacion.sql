-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-04-2022 a las 03:51:24
-- Versión del servidor: 10.4.20-MariaDB
-- Versión de PHP: 8.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `facturacion`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_producto` (`n_cantidad` INT, `n_precio` DECIMAL(10,2), `codigo` INT)  BEGIN
    	DECLARE nueva_existencia int;
        DECLARE nuevo_total decimal(10,2);
        DECLARE nuevo_precio decimal(10,2);
        
        DECLARE cantidad_actual int;
        DECLARE precio_actual decimal(10,2);
        
        DECLARE actual_cantidad int;
        DECLARE actual_precio decimal(10,2);
        
        SELECT precio, existencia INTO actual_precio, actual_cantidad FROM producto WHERE codproducto = codigo;
        SET nueva_existencia = actual_cantidad + n_cantidad;
        SET nuevo_total = (actual_cantidad * actual_precio) + (n_cantidad * n_precio);
        SET nuevo_precio = nuevo_total / nueva_existencia;
        
        UPDATE producto SET existencia = nueva_existencia, precio = nuevo_precio WHERE codproducto = codigo;
        
        SELECT nueva_existencia, nuevo_precio;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_temp` (`codigo` INT, `cantidad` INT, `token_user` VARCHAR(50))  BEGIN
    DECLARE precio_actual decimal(10,2);
	SELECT precio INTO precio_actual FROM producto WHERE codproducto = codigo;
	
    INSERT INTO detalle_temp(token_user,codproducto,cantidad,precio_venta) VALUES(token_user,codigo,cantidad,precio_actual);
    
    SELECT tmp.correlativo, tmp.codproducto,p.descripcion, tmp.cantidad, tmp.precio_venta FROM detalle_temp tmp
    INNER JOIN producto p
    ON tmp.codproducto = p.codproducto
    WHERE tmp.token_user = token_user;

   END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `anular_factura` (`no_factura` INT)  BEGIN
    	DECLARE existe_factura INT;
        DECLARE registros INT;
        DECLARE a INT;
        
        DECLARE cod_producto INT;
        DECLARE cant_producto INT;
        DECLARE existencia_actual INT;
        DECLARE nueva_existencia INT;
        
        SET existe_factura = (SELECT COUNT(*) FROM factura WHERE nofactura = no_factura AND estatus = 1);
        
        IF existe_factura > 0 THEN
        	CREATE TEMPORARY TABLE tbl_tmp( id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                							cod_prod BIGINT, cant_prod INT );
                                            
            SET a = 1;
            SET registros =(SELECT COUNT(*) FROM detallefactura WHERE nofactura = no_factura);
            
            IF registros > 0 THEN
            	INSERT INTO tbl_tmp(cod_prod, cant_prod) SELECT codproducto, cantidad FROM detallefactura 
                										 WHERE nofactura = no_factura;
                                                         
                WHILE a <= registros DO
                	SELECT cod_prod, cant_prod INTO cod_producto, cant_producto FROM tbl_tmp WHERE id = a;
                    SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                    SET nueva_existencia = existencia_actual + cant_producto;
                    UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto; 
                    
                    SET a=a+1;
                END WHILE;
                
                UPDATE factura SET estatus = 2 WHERE nofactura = no_factura;
                DROP TABLE tbl_tmp;
                SELECT * FROM factura WHERE nofactura = no_factura;
            END IF;
            
        ELSE
        	SELECT 0 factura;
        END IF;
        
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dataDashboard` ()  BEGIN
		DECLARE usuarios 	INT;
        DECLARE clientes 	INT;
        DECLARE proveedores INT;
        DECLARE productos 	INT;
        DECLARE ventas 		INT;
        
        SELECT COUNT(*) INTO usuarios 	 FROM usuario 	WHERE estatus != 10;
        SELECT COUNT(*) INTO clientes 	 FROM cliente 	WHERE estatus != 10;
        SELECT COUNT(*) INTO proveedores FROM proveedor WHERE estatus != 10;
        SELECT COUNT(*) INTO productos   FROM producto 	WHERE estatus != 10;
        SELECT COUNT(*) INTO ventas 	 FROM factura 	WHERE fecha > CURDATE() AND estatus != 10;
        
        SELECT usuarios,clientes,proveedores,productos,ventas;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `del_detalle_temp` (`id_detalle` INT, `token` VARCHAR(50))  BEGIN
    	DELETE FROM detalle_temp WHERE correlativo = id_detalle;
        
        SELECT tmp.correlativo, tmp.codproducto, tmp.cantidad, tmp.precio_venta, p.descripcion
        FROM detalle_temp tmp INNER JOIN producto p
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token;
     END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `procesar_venta` (`cod_usuario` INT, `cod_cliente` INT, `token` VARCHAR(50))  BEGIN
    	DECLARE numfactura INT;
        DECLARE registros INT;
        DECLARE total DECIMAL(10,2);
        DECLARE nueva_existencia INT;
        DECLARE existencia_actual INT;
        DECLARE tmp_cod_producto INT;
        DECLARE tmp_cant_producto INT;
        DECLARE a INT;
        SET a = 1;
        
        CREATE TEMPORARY TABLE tbl_tem_tokenuser(id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                                cod_prod BIGINT, cant_prod INT);
                                                
        SET registros =(SELECT COUNT(*) FROM detalle_temp WHERE token_user = token);
        
        IF registros > 0 THEN
        	INSERT INTO tbl_tem_tokenuser(cod_prod, cant_prod) SELECT codproducto, cantidad FROM detalle_temp WHERE token_user = token;
            
            INSERT INTO factura (usuario, codcliente) VALUES(cod_usuario, cod_cliente);
            SET numfactura = LAST_INSERT_ID();
            
            INSERT INTO detallefactura (nofactura, codproducto, cantidad, precio_venta) 
            SELECT (numfactura) AS nofactura, codproducto, cantidad, precio_venta FROM detalle_temp WHERE token_user = token;
            
            WHILE a <= registros DO
            	SELECT cod_prod, cant_prod INTO tmp_cod_producto, tmp_cant_producto FROM tbl_tem_tokenuser WHERE id = a;
                SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = tmp_cod_producto;
                
                SET nueva_existencia = existencia_actual - tmp_cant_producto;
                UPDATE producto SET existencia = nueva_existencia WHERE codproducto = tmp_cod_producto;
                
                SET a = a + 1;
            END WHILE;
            
            SET total = (SELECT SUM(cantidad * precio_venta) FROM detalle_temp WHERE token_user = token);
            UPDATE factura SET totalfactura = total WHERE nofactura = numfactura;
            
            DELETE FROM detalle_temp WHERE token_user = token;
            TRUNCATE TABLE tbl_tem_tokenuser;
            SELECT * FROM factura WHERE nofactura = numfactura;
        ELSE
        	SELECT 0; 
        END IF;
	END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `doc` int(11) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` varchar(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `dateadd` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `doc`, `nombre`, `telefono`, `direccion`, `dateadd`, `usuario_id`, `estatus`) VALUES
(1, 11045186, 'Jose Estevez', '23597809', '18 de julio 3764', '2022-02-08 10:54:52', 1, 1),
(2, 19420829, 'Gonzalo Brandon', '23557533', 'Minas 2673', '2022-02-09 11:03:21', 1, 1),
(3, 5126734, 'Maximo Vecino', '2200-1611', 'Magallanes 3465', '2022-02-09 11:03:54', 1, 1),
(4, 11229801, 'Gabriel Bertucci', '2204-3318', 'Ellauri 2345', '2022-02-09 11:04:24', 1, 1),
(5, 9041130, 'Antonio Rizzo', '23557533', 'Mercedes 3409', '2022-02-09 11:04:55', 1, 1),
(6, 11441984, 'Nelsa Vique', '98634225', 'Santa Ana 3987', '2022-02-09 11:05:30', 1, 1),
(7, 18243929, 'Hugo Rodriguez', '2204-3132', 'Inca 3876', '2022-02-09 11:05:59', 1, 1),
(8, 16914948, 'Isaac Acher', '2409-9979', 'Las heras 3422', '2022-02-09 11:06:35', 1, 1),
(9, 12030231, 'Jose Garcia', '91829113', 'Irlanda 3765', '2022-02-09 11:07:04', 1, 1),
(10, 8389719, 'Angela Mendez', '97131392', 'Arenal Grande 2876', '2022-02-09 11:07:35', 1, 1),
(11, 17263548, 'Natalia Duarte', '24401890', 'Minas 1234', '2022-02-09 11:08:07', 1, 1),
(12, 12345678, 'Editado', '2204-3318', 'La calle 21', '2022-02-17 21:27:24', 1, 0),
(13, 34343434, 'Juan Pineda', '22146465', 'Ciudad de Solymar', '2022-03-17 16:33:21', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id_configuracion` bigint(20) NOT NULL,
  `rut` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `direccion` text NOT NULL,
  `iva` double(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id_configuracion`, `rut`, `nombre`, `razon_social`, `telefono`, `email`, `direccion`, `iva`) VALUES
(1, '214454098009', 'PJ Software Soluciones', 'PJ Software S.A.', '22225757', 'info@pjsoluciones.com', 'La gran esperanza 2223 ', 22.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallefactura`
--

CREATE TABLE `detallefactura` (
  `correlativo` bigint(11) NOT NULL,
  `nofactura` bigint(11) DEFAULT NULL,
  `codproducto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detallefactura`
--

INSERT INTO `detallefactura` (`correlativo`, `nofactura`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(1, 1, 2, 5, '12366.67'),
(2, 1, 1, 10, '14882.94'),
(4, 2, 2, 5, '12366.67'),
(5, 3, 3, 1, '321.25'),
(6, 3, 2, 1, '12366.67'),
(7, 4, 2, 1, '12366.67'),
(8, 5, 3, 4, '321.25'),
(9, 5, 2, 1, '12366.67'),
(11, 6, 2, 2, '12366.67'),
(12, 7, 2, 1, '12366.67'),
(13, 7, 3, 1, '321.25'),
(15, 8, 3, 1, '321.25'),
(16, 8, 2, 1, '12366.67'),
(18, 9, 2, 1, '12366.67'),
(19, 10, 2, 1, '12366.67'),
(20, 11, 2, 1, '12366.67'),
(21, 12, 3, 1, '321.25'),
(22, 13, 1, 1, '14882.94'),
(23, 13, 3, 1, '321.25'),
(24, 14, 2, 1, '12366.67'),
(25, 14, 3, 2, '321.25'),
(26, 15, 2, 1, '12366.67'),
(27, 16, 2, 1, '12366.67'),
(28, 17, 3, 1, '321.25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_temp`
--

CREATE TABLE `detalle_temp` (
  `correlativo` int(11) NOT NULL,
  `token_user` varchar(50) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detalle_temp`
--

INSERT INTO `detalle_temp` (`correlativo`, `token_user`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(49, 'c81e728d9d4c2f636f067f89cc14862c', 2, 1, '12366.67'),
(50, 'c81e728d9d4c2f636f067f89cc14862c', 3, 2, '321.25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `correlativo` int(11) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`correlativo`, `codproducto`, `cantidad`, `precio`, `fecha`, `usuario_id`) VALUES
(1, 1, 9, '25000.00', '2022-02-25 11:32:43', 1),
(2, 2, 20, '13000.00', '2022-02-25 11:34:45', 1),
(3, 2, 10, '15000.00', '2022-03-04 10:31:28', 1),
(4, 2, 10, '10000.00', '2022-03-04 11:13:49', 1),
(5, 2, 5, '9000.00', '2022-03-04 11:17:24', 1),
(6, 1, 5, '15000.00', '2022-03-04 11:29:10', 1),
(7, 1, 6, '10150.00', '2022-03-04 11:37:13', 1),
(8, 1, 6, '10150.00', '2022-03-04 11:37:16', 1),
(9, 1, 6, '10150.50', '2022-03-04 11:37:24', 1),
(10, 1, 5, '11000.00', '2022-03-04 11:52:29', 1),
(11, 1, 5, '9000.00', '2022-03-11 23:44:28', 1),
(12, 2, 5, '12000.00', '2022-03-12 00:29:25', 1),
(13, 1, 5, '12000.00', '2022-03-12 00:30:49', 1),
(14, 3, 15, '320.00', '2022-03-14 21:52:52', 1),
(15, 3, 5, '320.00', '2022-03-14 21:53:20', 1),
(16, 3, 5, '320.00', '2022-03-14 22:01:23', 1),
(17, 3, 5, '320.00', '2022-03-14 22:01:55', 1),
(18, 2, 5, '12400.00', '2022-03-14 22:02:26', 1),
(19, 1, 5, '14882.94', '2022-03-14 22:02:58', 1),
(20, 3, 10, '325.00', '2022-03-14 22:03:54', 1),
(21, 2, 5, '12400.00', '2022-03-14 22:04:39', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `nofactura` bigint(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `totalfactura` decimal(10,2) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`nofactura`, `fecha`, `usuario`, `codcliente`, `totalfactura`, `estatus`) VALUES
(1, '2022-03-25 18:38:42', 1, 2, '210662.75', 1),
(2, '2022-03-25 21:01:47', 1, 4, '61833.35', 1),
(3, '2022-03-25 21:10:10', 1, 1, '12687.92', 1),
(4, '2022-03-28 11:26:48', 1, 4, '12366.67', 1),
(5, '2022-03-28 15:32:10', 1, 13, '13651.67', 1),
(6, '2022-03-28 15:34:43', 1, 3, '24733.34', 1),
(7, '2022-03-28 16:15:14', 1, 3, '12687.92', 1),
(8, '2022-03-28 17:08:32', 1, 3, '12687.92', 1),
(9, '2022-03-28 17:17:33', 1, 3, '12366.67', 1),
(10, '2022-03-28 17:21:09', 1, 2, '12366.67', 2),
(11, '2022-03-28 17:22:43', 1, 2, '12366.67', 2),
(12, '2022-03-28 17:35:03', 1, 5, '321.25', 2),
(13, '2022-03-28 18:12:25', 2, 13, '15204.19', 2),
(14, '2022-03-29 11:13:54', 1, 13, '13009.17', 2),
(15, '2022-03-30 11:32:28', 1, 3, '12366.67', 2),
(16, '2022-03-31 19:59:41', 1, 9, '12366.67', 1),
(17, '2022-04-04 10:16:52', 1, 3, '321.25', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `codproducto` int(11) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `existencia` int(11) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `foto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`codproducto`, `descripcion`, `proveedor`, `precio`, `existencia`, `date_add`, `usuario_id`, `estatus`, `foto`) VALUES
(1, 'Iphone E9', 8, '14882.94', 30, '2022-02-25 11:32:43', 2, 1, 'img_1dc41ed0ac92361907fe5620882309a4.jpg'),
(2, 'ProBook 6460b', 11, '12366.67', 40, '2022-02-25 11:34:45', 2, 1, 'img_1372bd3a99af2b0769758a5e369c4200.jpg'),
(3, 'Pendrive Cruzer Blade 16gb', 10, '321.25', 32, '2022-03-14 21:52:52', 1, 1, 'img_5e5b80a459255792ca0bced60570b800.jpg');

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `entradas_A_I` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
    	INSERT INTO entradas(codproducto, cantidad, precio, usuario_id)
        VALUES(new.codproducto, new.existencia, new.precio, new.usuario_id);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `codproveedor` int(11) NOT NULL,
  `rut` varchar(15) DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` bigint(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`codproveedor`, `rut`, `proveedor`, `contacto`, `telefono`, `direccion`, `date_add`, `usuario_id`, `estatus`) VALUES
(1, '213587750019', 'BIC', 'Claudia Rosales', 27894877, 'Avenida las Americas', '2022-02-18 20:00:00', 1, 1),
(2, '212427750012', 'CASIO', 'Jorge Herrera', 25656563, 'Calzada Las Flores', '2022-02-18 20:00:00', 1, 1),
(3, '210035000015', 'Omega', 'Julio Estrada', 982877489, 'Avenida Elena Zona 4, Guatemala', '2022-02-18 20:00:00', 1, 1),
(4, '0', 'Dell Compani', 'Roberto Estrada', 2147483647, 'Guatemala, Guatemala', '2022-02-18 20:00:00', 0, 0),
(5, '213430060011', 'Olimpia S.A', 'Elena Franco Morales', 564535676, '5ta. Avenida Zona 4 Ciudad', '2022-02-18 20:00:00', 1, 1),
(6, '214206180015', 'Oster', 'Fernando Guerra', 78987678, 'Calzada La Paz, Guatemala', '2022-02-18 20:00:00', 1, 1),
(7, '216583670015', 'ACELTECSA S.A', 'Ruben PÃ©rez', 789879889, 'Colonia las Victorias', '2022-02-18 20:00:00', 1, 1),
(8, '214796271906', 'Sony', 'Julieta Contreras', 89476787, 'Antigua Guatemala', '2022-02-18 20:00:00', 1, 1),
(9, '0', 'VAIO', 'Felix Arnoldo Rojas', 476378276, 'Avenida las Americas Zona 13', '2022-02-18 20:00:00', 0, 0),
(10, '214472910011', 'SUMAR', 'Oscar Maldonado', 97883767, 'Colonia San Jose, Zona 5 Guatemala', '2022-02-18 20:00:00', 1, 1),
(11, '214613330016', 'HP', 'Angel Cardona', 91736574, '5ta. calle zona 4 Guatemala', '2022-02-18 20:00:00', 1, 1),
(12, '210194800016', 'Luis G. Bonomi & Cía S.A.', 'Alejandra Fernandez', 22155555, 'Av. Gral. San Martín 4751', '2022-02-18 21:24:20', 1, 1),
(13, '110250000017', 'Luis G. Bonomi', 'Pablo Josman', 22003456, '18 de julio 276', '2022-02-18 21:25:13', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` int(11) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `correo`, `usuario`, `clave`, `rol`, `estatus`) VALUES
(1, 'Paulo F. J', 'paulo@info.com', 'Admin', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1),
(2, 'Karina Gomez', 'karina@info.com', 'Karina', 'b0e8a3d7b0f5004fcb918eafbdaeb741', 2, 1),
(3, 'Jose Silveira', 'Jose@info.com.uy', 'Jose', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(4, 'Editado', 'pruevadelproe@sistemaventas.com', 'Prueva1', '81dc9bdb52d04dc20036dbd8313ed055', 2, 0),
(5, 'Antonio', 'prueva@sistemaventas.com', 'Prueva2', '202cb962ac59075b964b07152d234b70', 3, 0),
(6, 'Fredy', 'info@algo.com.uy', 'fredy', '81dc9bdb52d04dc20036dbd8313ed055', 3, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id_configuracion`);

--
-- Indices de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `nofactura` (`nofactura`);

--
-- Indices de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `nofactura` (`token_user`),
  ADD KEY `codproducto` (`codproducto`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`nofactura`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `codcliente` (`codcliente`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`codproducto`),
  ADD KEY `proveedor` (`proveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`codproveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id_configuracion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  MODIFY `correlativo` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `nofactura` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `codproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `codproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`);

--
-- Filtros para la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD CONSTRAINT `detallefactura_ibfk_1` FOREIGN KEY (`nofactura`) REFERENCES `factura` (`nofactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detallefactura_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD CONSTRAINT `detalle_temp_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `entradas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`codcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
