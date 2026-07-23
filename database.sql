-- ============================================================
--  database.sql — À importer dans phpMyAdmin (XAMPP)
--  1. Ouvre http://localhost/phpmyadmin
--  2. Clique sur "Importer" puis sélectionne ce fichier
-- ============================================================

CREATE DATABASE IF NOT EXISTS vieadeux
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vieadeux;

-- Supprimer la table si elle existe déjà (repart de zéro)
DROP TABLE IF EXISTS inscriptions;

-- Table des inscriptions (avec prenom et nom)
CREATE TABLE inscriptions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    prenom           VARCHAR(50)  NOT NULL DEFAULT '',
    nom              VARCHAR(50)  NOT NULL DEFAULT '',
    je_suis          VARCHAR(20)  NOT NULL,
    je_cherche       VARCHAR(20)  NOT NULL DEFAULT '',
    ville            VARCHAR(100) NOT NULL,
    age              TINYINT UNSIGNED NOT NULL,
    telephone        VARCHAR(20)  NOT NULL,
    mot_de_passe     VARCHAR(255) NOT NULL,
    date_inscription DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actif            TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données de test
INSERT INTO inscriptions (prenom, nom, je_suis, je_cherche, ville, age, telephone, mot_de_passe) VALUES
('Sarah',   'Martin',  'Une femme', 'Un homme',   'Paris 75001',    34, '+33 6 12 34 56 78', '$2y$10$examplehashonly'),
('Élodie',  'Bernard', 'Une femme', 'Un homme',   'Lyon 69001',     29, '+33 7 23 45 67 89', '$2y$10$examplehashonly'),
('Clarisse','Dupont',  'Un homme',  'Une femme',  'Bordeaux 33000', 41, '+33 6 34 56 78 90', '$2y$10$examplehashonly'),
('Lydia',   'Moreau',  'Une femme', 'Peu importe','Toulouse 31000', 46, '+33 7 45 67 89 01', '$2y$10$examplehashonly');
