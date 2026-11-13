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
    
    FOREIGN KEY(id_usuario) REFERENCES usuario(id)
    ON DELETE CASCADE,
    FOREIGN KEY(id_admin) REFERENCES admin(id)
    ON DELETE CASCADE,
    PRIMARY KEY(id_usuario, id_admin)
);

CREATE TABLE automovil(
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    id_usuario INT NOT NULL,

    FOREIGN KEY(id_usuario) REFERENCES usuario(id)
    ON DELETE CASCADE
);

/*
CREATE TABLE lugar(
    coordenadaX INT,
    coordenadaY INT,
    calle VARCHAR(100),
    numero_puerta INT,
    esquina VARCHAR(100),

    PRIMARY KEY(coordenadaX, coordenadaY)
);*/


CREATE TABLE registra(
    id_automovil INT,
    id_usuario INT,

    PRIMARY KEY(id_automovil, id_usuario),

    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
    ON DELETE CASCADE,
    FOREIGN KEY (id_automovil) REFERENCES automovil(id)
    ON DELETE CASCADE
);

CREATE TABLE ocupa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_automovil INT NOT NULL,
    hora_estacionamiento TIME NOT NULL,
    hora_liberacion TIME NOT NULL,
    calle VARCHAR(150) NOT NULL,
    latitud DECIMAL(10,7),
    longitud DECIMAL(10,7),

    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
    ON DELETE CASCADE,

    FOREIGN KEY (id_automovil) REFERENCES automovil(id)
    ON DELETE CASCADE
);

CREATE TABLE tramos_no_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tramo INT NOT NULL UNIQUE
);

CREATE TABLE lugares_liberados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_automovil INT NOT NULL,
    calle VARCHAR(150) NOT NULL,
    latitud DECIMAL(10,7) NOT NULL,
    longitud DECIMAL(10,7) NOT NULL,
    liberado_en DATETIME NOT NULL
);

INSERT INTO admin(id,nombre_admin,contrasenia)
VALUES(1,'admin','admin');

INSERT INTO usuario(id,nombre_usuario,nombre,apellido,email,contrasenia)
VALUES(1,'prof1','Felipe','Perez','felipe123@gmail.com','111');


INSERT INTO usuario(id,nombre_usuario,nombre,apellido,email,contrasenia)
VALUES(2,'prof2','Juaquin','Lorenzo','lorenzjuaqui@gmail.com','222');

INSERT INTO automovil(id, marca, modelo, color, id_usuario)
VALUES(1,'Volkswagen','Vento','#F72F07',2);

INSERT INTO automovil(id, marca, modelo, color, id_usuario)
VALUES(2,'Ferrari','LaFerrari','#000000',1);

INSERT INTO automovil(id, marca, modelo, color, id_usuario)
VALUES(3,'Lancia','Delta Integrale HF','#C9C9C9',1);

INSERT INTO tramos_no_disponibles (id_tramo)
VALUES (1208968468),
       (178813178),
       (78107707),
       (818926871),
       (312271939),
       (587346004),
       (1209833944),
       (78113037),
       (482252495),
       (178813202),
       (78119130),
       (853323695),
       (538982971),
       (951586227),
       (178813176),
       (995423482),
       (951586232),
       (818075623),
       (951586225),
       (951586224),
       (1195244183),
       (951586226),
       (78110032),
       (951586223),
       (818075624),
       (1195244179),
       (951591650),
       (78108147),
       (1044589059),
       (481897992),
       (1082199150),
       (458356853),
       (555721673),
       (1291983717),
       (1195244184),
       (78117558),
       (78113042),
       (995423484),
        (853323698),
        (853323697),
        (78105310),
        (178813160),
        (1209535310),
        (1209535309),
        (219566992),
        (1209833939),
        (236741391),
        (540053597),
        (180467201),
        (176416993),
        (180467169),
        (1375766177),
        (180467175),
        (1497748191),
        (1560965319),
        (371937948),
        (180467198),
        (371937947),
        (78245866),
        (78120488),
        (371937949),
        (78245867),
        (78245863),
        (538739980),
        (538739986),
        (456412312),
        (78108989),
        (237888503),
        (1209833940),
        (572370904),
        (1497749264),
        (165789245),
        (78109558),
        (983895897),
        (95181819),
        (1195244180),
        (1206984784),
        (707089565),
        (832731037),
        (78117829),
        (1460728494),
        (78115669);