DROP DATABASE IF EXISTS enseignement;
CREATE DATABASE enseignement;
USE enseignement;
CREATE Table Vacataire (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50),
    prenom VARCHAR(50),    
    tel VARCHAR(50),
    email VARCHAR(200),
    profession VARCHAR(50)
);

CREATE Table Cours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50),
    description TEXT
);

CREATE TABLE enseigne(
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_vacataire INT,
    id_cours INT,
    nb_heures INT
);

ALTER TABLE enseigne ADD CONSTRAINT fk_vacataire FOREIGN KEY (id_vacataire) REFERENCES Vacataire(id);
ALTER TABLE enseigne ADD CONSTRAINT fk_cours FOREIGN KEY (id_cours) REFERENCES Cours(id);

INSERT INTO Vacataire (nom, prenom, tel, email, profession) VALUES ('Lesplinguies', 'Tristan', '01 02 03 04 05', 'moi@exemple.com', 'Game Master');
INSERT INTO Cours (nom, description) VALUES ('Mécanique Quantique', 'Étude des applications de l\'ordinateur quantique et de son fonctionnement');
INSERT INTO enseigne (id_vacataire, id_cours, nb_heures) VALUES (1, 1, 20);

INSERT into Cours (nom, description) VALUES ('Mathématiques', 'Étude des mathématiques appliquées à la physique quantique');
