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
    contrasenia VARCHAR(200) NOT NULL UNIQUE
);

CREATE TABLE gestiona(
    id_usuario INT, 
    id_admin INT,
    
    FOREIGN KEY(id_usuario) REFERENCES usuario(id),
    FOREIGN KEY(id_admin) REFERENCES admin(id),
    PRIMARY KEY(id_usuario, id_admin)
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

    FOREIGN KEY (id_usuario) REFERENCES usuario(id),
    FOREIGN KEY (id_automovil) REFERENCES automovil(id)
);


CREATE TABLE ocupa(
    id INT PRIMARY KEY AUTO_INCREMENT,
    coordenadaX INT,
    coordenadaY INT,
    id_usuario INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    id_automovil INT,

    FOREIGN KEY (id_usuario) REFERENCES usuario(id),
    FOREIGN KEY (id_automovil) REFERENCES automovil(id),
    FOREIGN KEY (coordenadaX, coordenadaY) REFERENCES lugar(coordenadaX, coordenadaY)
);

INSERT INTO admin(id,nombre_admin,contrasenia)
VALUES(1,'admin','admin');

INSERT INTO usuario(id,nombre_usuario,nombre,apellido,email,contrasenia)
VALUES(1,'prof1','Felipe','Perez','felipe123@gmail.com','111');


INSERT INTO usuario(id,nombre_usuario,nombre,apellido,email,contrasenia)
VALUES(2,'prof2','Juaquin','Lorenzo''lorenzjuaqui@gmail.com','222');

INSERT INTO automovil(id, marca, modelo, color, id_usuario)
VALUES(1,'Volkswagen','Vento','#F72F07',2);

INSERT INTO automovil(id, marca, modelo, color, id_usuario)
VALUES(2,'Ferrari','LaFerrari','#000000',1);

INSERT INTO automovil(id, marca, modelo, color, id_usuario)
VALUES(3,'Lancia','Delta Integrale HF','#C9C9C9',1);