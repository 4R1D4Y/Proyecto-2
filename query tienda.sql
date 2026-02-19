DROP DATABASE IF EXISTS tienda;
CREATE DATABASE tienda;
USE tienda;

CREATE TABLE usuarios (
	id_usuario INT PRIMARY KEY AUTO_INCREMENT,
	nombre VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    rol VARCHAR(20) DEFAULT "cliente"
);

CREATE TABLE productos (
	id_producto INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(40) NOT NULL,
    img VARCHAR(100) NOT NULL,
    precio DECIMAL (10,2) NOT NULL,
    stock CHAR(2) DEFAULT "si"
);

CREATE TABLE compras (
	id_compra INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_producto INT NOT NULL,
    fecha VARCHAR(15) NOT NULL,
    cantidad INT NOT NULL,
    total DECIMAL (10,2),
       
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) 
);

INSERT INTO usuarios VALUE (DEFAULT, "admin", "1234", "administrador");

INSERT INTO productos VALUES 
	(DEFAULT, "Breach", "../images/breach.jpg", 32.99, DEFAULT), 
	(DEFAULT, "Clancy", "../images/clancy.jpg", 32.99, DEFAULT),
    (DEFAULT, "Scaled And Icy", "../images/scaled_and_icy.jpg", 32.99, DEFAULT),
    (DEFAULT, "Trench", "../images/trench.jpg", 32.99, DEFAULT),
    (DEFAULT, "Blurryface", "../images/blurryface.jpg", 32.99, DEFAULT),
    (DEFAULT, "Vessel", "../images/vessel.jpg", 32.99, DEFAULT),
	(DEFAULT, "Twenty One Pilots", "../images/twenty_one_pilots.jpg", 32.99, DEFAULT)
;