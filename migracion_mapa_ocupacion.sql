-- Ejecutar una sola vez en la base de datos estacionamiento ya existente.
-- Añade los datos necesarios para registrar hora, calle y coordenadas del mapa.
ALTER TABLE ocupa
    ADD COLUMN hora_estacionamiento TIME NULL,
    ADD COLUMN calle VARCHAR(150) NULL,
    ADD COLUMN latitud DECIMAL(10,7) NULL,
    ADD COLUMN longitud DECIMAL(10,7) NULL;
