CREATE DATABASE estacionamiento;
USE estacionamiento;

CREATE TABLE usuario(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    contrasenia VARCHAR(200) NOT NULL,
);

CREATE TABLE admin(
    id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nombre_admin VARCHAR(50) NOT NULL UNIQUE,
    contrasenia NOT NULL UNIQUE
);

CREATE TABLE gestiona(
    id_usuario INT 
    id_admin INT
    
    FOREIGN KEY(id_usuario) REFERENCES usuario(id),
    FOREIGN KEY(id_admin) REFERENCES admin(id),
    PRIMARY KEY(id_usuario, id_admin)
);

CREATE TABLE automovil(
    id INT PRIMARY KEY NOT NULL,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    tamanio VARCHAR(50) NOT NULL,
    estado BOOLEAN,
    id_usuario INT NOT NULL,

    FOREIGN KEY(id_usuario) REFERENCES usuario(id)
);


CREATE TABLE lugar(
    coordenadaX INT,
    coordenadaY INT,
    calle VARCHAR(100),
    numero_puerta INT,
    esquina VARCHAR(100),
    estado BOOLEAN,

    PRIMARY KEY(coordenadaX, coordenadaY)
);


CREATE TABLE registra(
    id_automovil INT,
    id_usuario INT,

    PRIMARY KEY(id_automovil, id_usuario),

    FOREIGN KEY (id_usuario) REFERENCES usuario(id),
    FOREIGN KEY (id_automovil) REFERENCES automovil(id)
);


CREATE TABLE ocupa(
    id INT PRIMARY KEY,
    coordenadaX INT,
    coordenadaY INT,
    id_usuario INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    id_automovil VARCHAR(7),

    FOREIGN KEY (idUsuario) REFERENCES usuario(id),
    FOREIGN KEY (id_automovil) REFERENCES automovil(id),
    FOREIGN KEY (coordenadaX, coordenadaY) REFERENCES lugar(coordenadaX, coordenadaY)
);
