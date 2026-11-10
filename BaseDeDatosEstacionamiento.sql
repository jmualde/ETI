
CREATE DATABASE estacionamiento;
USE estacionamiento;

CREATE TABLE usuario(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    contrasenia VARCHAR(200) NOT NULL
);

CREATE TABLE admin(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_admin VARCHAR(50) NOT NULL UNIQUE,
    contrasenia VARCHAR(200) NOT NULL
);

CREATE TABLE gestiona(
    id_usuario INT,
    id_admin INT,

    PRIMARY KEY(id_usuario, id_admin),
    FOREIGN KEY(id_usuario) REFERENCES usuario(id),
    FOREIGN KEY(id_admin) REFERENCES admin(id)
);

CREATE TABLE automovil(
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    id_usuario INT NOT NULL,

    FOREIGN KEY(id_usuario) REFERENCES usuario(id)
);

CREATE TABLE lugar(
    coordenadaX INT,
    coordenadaY INT,
    calle VARCHAR(100),
    numero_puerta INT,
    esquina VARCHAR(100),

    PRIMARY KEY(coordenadaX, coordenadaY)
);

CREATE TABLE registra(
    id_automovil INT,
    id_usuario INT,

    PRIMARY KEY(id_automovil, id_usuario),
    FOREIGN KEY(id_usuario) REFERENCES usuario(id),
    FOREIGN KEY(id_automovil) REFERENCES automovil(id)
);

CREATE TABLE ocupa(
    id INT PRIMARY KEY AUTO_INCREMENT,
    latitud DOUBLE,
    longitud DOUBLE,
    id_usuario INT,
    hora_liberacion TIME,
    hora_estacionamiento TIME,
    id_automovil INT,
    calle VARCHAR(200),

    FOREIGN KEY(id_usuario) REFERENCES usuario(id),
    FOREIGN KEY(id_automovil) REFERENCES automovil(id)
);

CREATE TABLE calles_prohibidas(
    id_osm INT PRIMARY KEY
);

-- INSERTAR ADMINISTRADOR
INSERT INTO admin(id, nombre_admin, contrasenia)
VALUES(1, 'admin', 'admin');

-- INSERTAR USUARIOS
INSERT INTO usuario(id, nombre_usuario, nombre, apellido, email, contrasenia)
VALUES
(1, 'prof1', 'Felipe', 'Perez', 'felipe123@gmail.com', '111'),
(2, 'prof2', 'Juaquin', 'Lorenzo', 'lorenzjuaqui@gmail.com', '222');

-- INSERTAR AUTOMOVILES
INSERT INTO automovil(id, marca, modelo, color, id_usuario)
VALUES
(1, 'Volkswagen', 'Vento', '#F72F07', 2),
(2, 'Ferrari', 'LaFerrari', '#000000', 1),
(3, 'Lancia', 'Delta Integrale HF', '#C9C9C9', 1);

-- INSERTAR CALLES PROHIBIDAS
INSERT INTO calles_prohibidas(id_osm)
VALUES
(818075623),
(951586232),
(995423484),
(178813176);
