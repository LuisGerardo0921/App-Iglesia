-- Schema para "Las Casas de Mi Amistad" (PostgreSQL)

DROP TABLE IF EXISTS integrantes CASCADE;
DROP TABLE IF EXISTS materiales CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS casas CASCADE;

CREATE TABLE casas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion TEXT NOT NULL,
    ciudad_sector VARCHAR(100) DEFAULT '',
    anfitrion_nombre VARCHAR(150) NOT NULL,
    facilitador_nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(50) NOT NULL,
    dia_reunion VARCHAR(50) NOT NULL,
    horario VARCHAR(50) NOT NULL,
    mapa_url TEXT DEFAULT '',
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE integrantes (
    id SERIAL PRIMARY KEY,
    codigo_id VARCHAR(20) UNIQUE NOT NULL,
    casa_id INTEGER REFERENCES casas(id) ON DELETE SET NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    telefono VARCHAR(50) DEFAULT '',
    email VARCHAR(150) DEFAULT '',
    rol VARCHAR(50) DEFAULT 'Integrante',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE materiales (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT DEFAULT '',
    semana VARCHAR(100) NOT NULL,
    archivo_path VARCHAR(255) NOT NULL,
    publicado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Datos Iniciales (Seed Data)

INSERT INTO casas (nombre, direccion, ciudad_sector, anfitrion_nombre, facilitador_nombre, telefono, dia_reunion, horario, mapa_url, activa)
VALUES 
('Casa de Amistad Norte - Valle Real', 'Calle Los Olivos #420, Col. Valle Real', 'Zona Norte', 'Carlos Ruiz', 'Mateo Fernández', '5551234567', 'Jueves', '19:30', 'https://www.google.com/maps/search/?api=1&query=Calle+Los+Olivos+420', TRUE),
('Casa de Amistad Sur - Coyoacán', 'Av. Hidalgo #108, Barrio Santa Catarina', 'Zona Sur', 'Elena Gómez', 'Sofía Morales', '5559876543', 'Viernes', '20:00', 'https://www.google.com/maps/search/?api=1&query=Av+Hidalgo+108+Coyoacan', TRUE),
('Casa de Amistad Poniente - Del Valle', 'Calle San Lorenzo #812, Col. Del Valle', 'Zona Poniente', 'Roberto Méndez', 'Daniel Vega', '5552345678', 'Miércoles', '19:00', 'https://www.google.com/maps/search/?api=1&query=Calle+San+Lorenzo+812+Del+Valle', TRUE),
('Casa de Amistad Oriente - Lindavista', 'Calle Matanzas #55, Col. Lindavista', 'Zona Oriente', 'Lucía Torres', 'Andrés Ramírez', '5558765432', 'Sábado', '18:00', 'https://www.google.com/maps/search/?api=1&query=Calle+Matanzas+55+Lindavista', TRUE);

INSERT INTO integrantes (codigo_id, casa_id, nombre_completo, telefono, email, rol)
VALUES 
('1001', 1, 'Carlos Ruiz', '5551234567', 'carlos.ruiz@email.com', 'Anfitrión'),
('1002', 1, 'Mateo Fernández', '5551112233', 'mateo.f@email.com', 'Facilitador'),
('1003', 1, 'Mariana Sánchez', '5553334455', 'mariana.s@email.com', 'Integrante'),
('1004', 1, 'Fernando López', '5554445566', 'fernando.l@email.com', 'Integrante'),

('1005', 2, 'Elena Gómez', '5559876543', 'elena.g@email.com', 'Anfitrión'),
('1006', 2, 'Sofía Morales', '5552223344', 'sofia.m@email.com', 'Facilitador'),
('1007', 2, 'Jorge Reyes', '5556667788', 'jorge.r@email.com', 'Integrante'),

('1008', 3, 'Roberto Méndez', '5552345678', 'roberto.m@email.com', 'Anfitrión'),
('1009', 3, 'Daniel Vega', '5557778899', 'daniel.v@email.com', 'Facilitador'),
('1010', 3, 'Claudia Jiménez', '5558889900', 'claudia.j@email.com', 'Integrante'),

('1011', 4, 'Lucía Torres', '5558765432', 'lucia.t@email.com', 'Anfitrión'),
('1012', 4, 'Andrés Ramírez', '5559990011', 'andres.r@email.com', 'Facilitador');

INSERT INTO materiales (titulo, descripcion, semana, archivo_path)
VALUES 
('Estudio 1: Fundamentos de la Comunidad', 'Guía de discusión para grupos pequeños enfocada en el apoyo mutuo y la comunión.', 'Semana 1 - Agosto 2026', 'uploads/estudio_semana_1.pdf'),
('Estudio 2: La Oración Diaria', 'Material práctico sobre hábitos de oración personal y en grupo.', 'Semana 2 - Agosto 2026', 'uploads/estudio_semana_2.pdf');

-- Usuario Administrador (password: admin123)
-- Usuario Facilitador (password: admin123)
INSERT INTO usuarios (username, password_hash, nombre, rol)
VALUES 
('admin', '$2y$12$Tkx8vRVHijyDdRwF1lMnnufBMKkiFe0H5H7EWBwGFUAdckg12qiua', 'Administrador Principal', 'admin'),
('facilitador', '$2y$12$Tkx8vRVHijyDdRwF1lMnnufBMKkiFe0H5H7EWBwGFUAdckg12qiua', 'Facilitador General', 'facilitador');
