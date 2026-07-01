CREATE DATABASE estacionamiento;
USE estacionamiento;

CREATE TABLE usuarios(
    id  INT PRIMARY KEY AUTO_INCREMENT,
    nombreUsuario VARCHAR(50) NOT NULL,
    nombre VARCHAR(50) NOT NULL ,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    contrasenia VARCHAR(200) NOT NULL ,
    rol BOOLEAN
);


CREATE TABLE Automovil(
    matricula VARCHAR(7) PRIMARY KEY NOT NULL,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    tamanio VARCHAR(50) NOT NULL,
    estado BOOLEAN,
    idUsuario INT NOT NULL,

    FOREIGN KEY(idUsuario) REFERENCES usuarios(id)
);


CREATE TABLE Lugar(
    coordenadaX INT,
    coordenadaY INT,
    calle VARCHAR(100),
    numeroDePuerta INT,
    estado BOOLEAN,

    PRIMARY KEY(coordenadaX, coordenadaY)
);


CREATE TABLE Registra(
    matriculaAutomovil VARCHAR(7),
    idUsuario INT,

    PRIMARY KEY(matriculaAutomovil, idUsuario),

    FOREIGN KEY (idUsuario) REFERENCES usuarios(id),
    FOREIGN KEY (matriculaAutomovil) REFERENCES Automovil(matricula)
);


CREATE TABLE Ocupa(
    id INT PRIMARY KEY,
    coordenadaX INT,
    coordenadaY INT,
    idUsuario INT,
    fechaInicio DATE,
    fechaFin DATE,
    matriculaAutomovil VARCHAR(7),

    FOREIGN KEY (idUsuario) REFERENCES usuarios(id),
    FOREIGN KEY (matriculaAutomovil) REFERENCES Automovil(matricula),
    FOREIGN KEY (coordenadaX, coordenadaY) REFERENCES Lugar(coordenadaX, coordenadaY)
);
