-- ============================================================
--  database.sql v2 — Mise à jour selon recommandations Harena
-- ============================================================

CREATE DATABASE IF NOT EXISTS vieadeux
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vieadeux;

DROP TABLE IF EXISTS inscriptions;

CREATE TABLE inscriptions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    prenom              VARCHAR(50)   NOT NULL DEFAULT '',
    nom                 VARCHAR(50)   NOT NULL DEFAULT '',
    email               VARCHAR(150)  NOT NULL DEFAULT '',
    je_suis             VARCHAR(20)   NOT NULL,
    je_cherche          VARCHAR(20)   NOT NULL DEFAULT '',
    ville               VARCHAR(100)  NOT NULL,
    date_naissance      DATE          NOT NULL,
    telephone           VARCHAR(20)   NOT NULL,
    centres_interet     VARCHAR(255)  NOT NULL DEFAULT '',
    mot_de_passe        VARCHAR(255)  NOT NULL,
    date_inscription    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reset_demande       DATETIME      NULL DEFAULT NULL,
    actif               TINYINT(1)    NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données de test
INSERT INTO inscriptions (prenom, nom, email, je_suis, je_cherche, ville, date_naissance, telephone, centres_interet, mot_de_passe) VALUES
('Sarah',   'Martin',  'sarah@test.fr',   'Une femme', 'Un homme',   'Paris',    '1990-03-15', '+33612345678', 'Voyages,Yoga,Cuisine',        '$2y$10$examplehashonly'),
('Élodie',  'Bernard', 'elodie@test.fr',  'Une femme', 'Un homme',   'Lyon',     '1995-07-22', '+33723456789', 'Musique,Cinéma,Running',      '$2y$10$examplehashonly'),
('Clarisse','Dupont',  'clarisse@test.fr','Un homme',  'Une femme',  'Bordeaux', '1983-11-08', '+33634567890', 'Nature,Lecture,Art',          '$2y$10$examplehashonly'),
('Lydia',   'Moreau',  'lydia@test.fr',   'Une femme', 'Peu importe','Toulouse', '1978-05-30', '+33745678901', 'Gastronomie,Jardinage,Randonnée','$2y$10$examplehashonly');
