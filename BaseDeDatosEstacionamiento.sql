CREATE DATABASE estacionamiento;
USE estacionamiento;

CREATE TABLE Usuario(
    ci VARCHAR(9) PRIMARY KEY,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(100),
    contraseña VARCHAR(100),
    rol BOOLEAN
);


CREATE TABLE Automovil(
    matricula VARCHAR(7) PRIMARY KEY,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    color VARCHAR(50),
    tamanio VARCHAR(50),
    estado BOOLEAN,
    ciUsuario VARCHAR(9),

    FOREIGN KEY(ciUsuario) REFERENCES Usuario(ci)
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
    ciUsuario VARCHAR(9),

    PRIMARY KEY(matriculaAutomovil, ciUsuario),

    FOREIGN KEY (ciUsuario) REFERENCES Usuario(ci),
    FOREIGN KEY (matriculaAutomovil) REFERENCES Automovil(matricula)
);


CREATE TABLE Ocupa(
    id INT PRIMARY KEY,
    coordenadaX INT,
    coordenadaY INT,
    ciUsuario VARCHAR(9),
    fechaInicio DATE,
    fechaFin DATE,
    matriculaAutomovil VARCHAR(7),

    FOREIGN KEY (ciUsuario) REFERENCES Usuario(ci),
    FOREIGN KEY (matriculaAutomovil) REFERENCES Automovil(matricula),
    FOREIGN KEY (coordenadaX, coordenadaY) REFERENCES Lugar(coordenadaX, coordenadaY)
);