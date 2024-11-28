-- Création de la table Utilisateur
CREATE TYPE role_type AS ENUM ('client', 'admin', 'serveur');
CREATE TABLE utilisateur (
    id_utilisateur SERIAL PRIMARY KEY,
    nom_utilisateur VARCHAR(100) NOT NULL CHECK (LENGTH(nom_utilisateur) <= 100),
    email VARCHAR(150) NOT NULL CHECK (email LIKE '%@%.%'),
    mot_de_passe VARCHAR(255) NOT NULL CHECK (LENGTH(mot_de_passe) >= 8),
    role role_type NOT NULL,
    date_inscription DATE NOT NULL CHECK (date_inscription <= CURRENT_DATE),
    telephone VARCHAR(15) NULL CHECK (telephone IS NULL OR telephone ~ '^\d{10}$'),
    photo BYTEA NULL
);

-- Création de la table Client
CREATE TABLE client (
    id_utilisateur INT PRIMARY KEY REFERENCES utilisateur(id_utilisateur),
    points_fidelite INTEGER NULL CHECK (points_fidelite >= 0),
    preferences_alimentaires TEXT NULL CHECK (LENGTH(preferences_alimentaires) <= 255),
    derniere_visite DATE NULL CHECK (derniere_visite <= CURRENT_DATE),
    statut_vip BOOLEAN NULL CHECK (statut_vip IN (TRUE, FALSE))
);

-- Création de la table Admin
CREATE TABLE admin (
    id_utilisateur INT PRIMARY KEY REFERENCES utilisateur(id_utilisateur),
    niveau_acces INTEGER NOT NULL CHECK (niveau_acces >= 1 AND niveau_acces <= 5),
    departement VARCHAR(50) NULL CHECK (departement IS NULL OR LENGTH(departement) <= 50),
    date_derniere_connexion TIMESTAMP NULL CHECK (date_derniere_connexion <= CURRENT_TIMESTAMP),
    ip_autorise VARCHAR(15) NULL CHECK (ip_autorise IS NULL OR ip_autorise ~ '^([01]?[0-9]{1,2}|2[0-4][0-9]|25[0-5])\.(?:([01]?[0-9]{1,2}|2[0-4][0-9]|25[0-5])\.){2}([01]?[0-9]{1,2}|2[0-4][0-9]|25[0-5])$')
);

-- Création de la table Serveur
CREATE TABLE serveur (
    id_utilisateur INT PRIMARY KEY REFERENCES utilisateur(id_utilisateur),
    numero_badge VARCHAR(20) NOT NULL UNIQUE,
    zone_service VARCHAR(50) NULL,
    horaires_travail TEXT NULL,
    specialite VARCHAR(100) NULL,
    date_embauche DATE NOT NULL CHECK (date_embauche <= CURRENT_DATE)
);

-- Création de la table Table_restaurant
CREATE TABLE table_restaurant (
    id_table SERIAL PRIMARY KEY,
    numero_table INTEGER NOT NULL UNIQUE,
    capacite INTEGER NOT NULL CHECK (capacite > 0),
    disponible BOOLEAN NOT NULL CHECK (disponible IN (TRUE, FALSE))
);

-- Création de la table Reservation
-- 创建枚举类型
CREATE TYPE statut_type AS ENUM ('confirmée', 'en attente', 'annulée');

-- 创建表
CREATE TABLE reservation (
    id_reservation SERIAL PRIMARY KEY,
    id_utilisateur INT NOT NULL REFERENCES utilisateur(id_utilisateur),
    date_reservation DATE NOT NULL CHECK (date_reservation >= CURRENT_DATE),
    heure_reservation TIME NOT NULL CHECK (heure_reservation >= '09:00' AND heure_reservation <= '23:59'),
    statut statut_type NOT NULL,
    nombre_personnes INTEGER NOT NULL CHECK (nombre_personnes > 0)
);


-- Création de la table Fournisseur
CREATE TABLE fournisseur (
    id_fournisseur SERIAL PRIMARY KEY,
    nom_fournisseur VARCHAR(100) NOT NULL UNIQUE,
    contact_fournisseur VARCHAR(100) NOT NULL UNIQUE,
    produits_fournis TEXT NULL
);

-- Création de la table Inventaire_de_stock
CREATE TABLE inventaire_de_stock (
    id_stock SERIAL PRIMARY KEY,
    nom_produit VARCHAR(100) NOT NULL UNIQUE,
    quantite NUMERIC NOT NULL CHECK (quantite >= 0),
    seuil_minimum NUMERIC NOT NULL CHECK (seuil_minimum >= 0),
    unite VARCHAR(20) NOT NULL,
    id_fournisseur INT NOT NULL REFERENCES fournisseur(id_fournisseur)
);




-- Création de la table Facture
CREATE TABLE facture (
    id_facture SERIAL PRIMARY KEY,
    id_reservation INT NOT NULL REFERENCES reservation(id_reservation),
    total_facture DECIMAL(10, 2) NOT NULL,
    date_facture DATE NOT NULL,
    mode_paiement VARCHAR(50) NOT NULL
);


-- Création de la table plat
CREATE TABLE plat (
    id_plat SERIAL PRIMARY KEY,
    id_stock INT NOT NULL REFERENCES inventaire_de_stock(id_stock),
    quantite INTEGER NOT NULL CHECK (quantite >= 1),
    nom_plat VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    prix_plat DECIMAL(10, 2) NOT NULL CHECK (prix_plat >= 0)
);

CREATE TABLE plat_ingredient (
    id_plat INT NOT NULL REFERENCES plat(id_plat),     -- 关联到菜品
    id_stock INT NOT NULL REFERENCES inventaire_de_stock(id_stock), -- 关联到库存的食材
    quantite DECIMAL(10, 2) NOT NULL CHECK (quantite > 0), -- 每道菜所需的食材数量
    PRIMARY KEY (id_plat, id_stock) -- 复合主键确保每种菜与食材的唯一性
);


-- Création de la table commande
CREATE TABLE commande (
    id_reservation INT NOT NULL REFERENCES reservation(id_reservation),
    id_plat INT NOT NULL REFERENCES plat(id_plat),
    quantite INTEGER NOT NULL CHECK (quantite >= 1),
    PRIMARY KEY (id_reservation, id_plat)
);


-- Création de la table Avis_clients
CREATE TABLE avis_clients (
    id_avis INT PRIMARY KEY,
    id_utilisateur INT NOT NULL REFERENCES utilisateur(id_utilisateur),
    id_reservation INT NOT NULL REFERENCES reservation(id_reservation),
    note INT NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT NULL,
    date_avis TIMESTAMP NOT NULL
);
CREATE SEQUENCE avis_clients_id_avis_seq;
ALTER TABLE avis_clients 
ALTER COLUMN id_avis SET DEFAULT nextval('avis_clients_id_avis_seq');
SELECT setval('avis_clients_id_avis_seq', 1, false);


-- Création de la table reserver n-n association entre Reservation et Table_Restaurant
CREATE TABLE reserver (
    id_reservation INT NOT NULL REFERENCES reservation(id_reservation) ON DELETE CASCADE,
    id_table INT NOT NULL REFERENCES table_restaurant(id_table) ON DELETE CASCADE,
    PRIMARY KEY (id_reservation, id_table)
);




-- Créer des fonctions de déclenchement
CREATE OR REPLACE FUNCTION calcule_total_facture() 
RETURNS TRIGGER AS $$
BEGIN
    -- Calcule automatiquement le montant total donné id_reservation
    UPDATE facture
    SET total_facture = (
        SELECT SUM(c.quantite * p.prix_plat)
        FROM commande c
        JOIN plat p ON c.id_plat = p.id_plat
        WHERE c.id_reservation = NEW.id_reservation
    )
    WHERE id_reservation = NEW.id_reservation;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Créer des déclencheurs
CREATE TRIGGER trigger_calcule_total_facture
AFTER INSERT OR UPDATE ON commande
FOR EACH ROW
EXECUTE PROCEDURE calcule_total_facture();



-- Créer des fonctions de déclenchement
CREATE OR REPLACE FUNCTION verifier_stock()
RETURNS TRIGGER AS $$
BEGIN
    -- Demande la quantité restante du plat actuel en stock
    DECLARE
        stock_disponible INTEGER;
    BEGIN
        SELECT quantite INTO stock_disponible
        FROM inventaire_de_stock
        WHERE id_stock = (SELECT id_stock FROM plat WHERE id_plat = NEW.id_plat);

        -- : : Vérifier si les stocks sont suffisants
        IF stock_disponible < NEW.quantite THEN
            RAISE EXCEPTION 'Stock insuffisant pour le plat %: disponible %, demande %',
            NEW.id_plat, stock_disponible, NEW.quantite;
        END IF;

        --- Autorisé à opérer si le stock est suffisant
        RETURN NEW;
    END;
END;
$$ LANGUAGE plpgsql;

--- Créer un déclencheur qui se déclenche lors de l'insertion d'une commande
CREATE TRIGGER trigger_verifier_stock
BEFORE INSERT ON commande
FOR EACH ROW
EXECUTE PROCEDURE verifier_stock();


CREATE OR REPLACE FUNCTION deduct_stock_on_order()
RETURNS TRIGGER AS $$
DECLARE
    ingredient_record RECORD;
BEGIN
    -- First, find the dish ingredients for the ordered dish
    FOR ingredient_record IN 
        SELECT pi.id_stock, pi.quantite * NEW.quantite AS total_required
        FROM plat_ingredient pi
        WHERE pi.id_plat = NEW.id_plat
    LOOP
        -- Update the stock, subtracting the required quantity
        UPDATE inventaire_de_stock
        SET quantite = quantite - ingredient_record.total_required
        WHERE id_stock = ingredient_record.id_stock;
    END LOOP;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_stock_after_order
AFTER INSERT ON commande
FOR EACH ROW
EXECUTE PROCEDURE deduct_stock_on_order();



INSERT INTO utilisateur (nom_utilisateur, email, mot_de_passe, role, date_inscription, telephone, photo)
VALUES 
('John Doe', 'john.doe@example.com', 'hashedpassword123456789', 'client', '2024-01-15', '1234567890', NULL),
('Jane Smith', 'jane.smith@example.com', 'hashedpassword987654321', 'admin', '2024-01-17', '0987654321', NULL),
('Paul Server', 'paul.server@example.com', 'hashedpasswordabcdefg', 'serveur', '2024-02-05', '1122334455', NULL);


INSERT INTO client (id_utilisateur, points_fidelite, preferences_alimentaires, derniere_visite, statut_vip)
VALUES 
(2, 100, 'Vegan', '2024-02-25', TRUE);


INSERT INTO admin (id_utilisateur, niveau_acces, departement, date_derniere_connexion, ip_autorise)
VALUES 
(3, 3, 'IT', '2024-03-01 12:34:56', '192.168.1.1');



INSERT INTO serveur (id_utilisateur, numero_badge, zone_service, horaires_travail, specialite, date_embauche)
VALUES 
(4, 'SRV123', 'Zone A', 'Mon-Fri 09:00-17:00', 'Beverages', '2024-02-05');


INSERT INTO table_restaurant (numero_table, capacite, disponible)
VALUES 
(1, 4, TRUE),
(2, 2, FALSE),
(3, 6, TRUE);


INSERT INTO reservation (id_utilisateur, date_reservation, heure_reservation, statut, nombre_personnes)
VALUES 
(2, '2024-12-05', '12:30', 'confirmée', 3),
(2, '2024-12-06', '18:00', 'en attente', 2);



INSERT INTO fournisseur (nom_fournisseur, contact_fournisseur, produits_fournis)
VALUES 
('FreshFarm Supplies', 'contact@freshfarm.com', 'Vegetables, Fruits'),
('MeatHouse', 'contact@meathouse.com', 'Beef, Chicken'),
('Bakery Delights', 'contact@bakerydelights.com', 'Bread, Pastry');


INSERT INTO inventaire_de_stock (nom_produit, quantite, seuil_minimum, unite,id_fournisseur)
VALUES
    ('Lettuce', 100, 10, 'kg',1),
    ('Tomato', 100, 10, 'kg',1),
    ('Caesar Dressing', 50, 5, 'L',1),
    ('Beef', 200, 20, 'kg',2),
    ('Bread', 150, 10, 'units',3),
    ('Pasta', 100, 10, 'kg',3),
    ('Cheese', 100, 10, 'kg',3),
    ('Mozzarella', 80, 8, 'kg',3),
    ('Chicken', 100, 10, 'kg',2),
    ('Noodles', 100, 10, 'kg',3),
    ('Turkey', 50, 5, 'kg',2),
    ('Potato', 200, 20, 'kg',1),
    ('Chocolate', 50, 5, 'kg',3),
    ('Tea Leaves', 30, 3, 'kg',1),
    ('Coffee Beans', 20, 2, 'kg',1),
    ('Orange', 50, 5, 'kg',1),
    ('Beer', 100, 10, 'L',1),
    ('Wine', 100, 10, 'L',1),
    ('Rum', 50, 5, 'L',1),
    ('Vodka', 50, 5, 'L',1),
    ('Whiskey', 50, 5, 'L',1),
    ('Mint', 30, 3, 'kg',1);


INSERT INTO plat (id_stock, quantite, nom_plat, description, prix_plat)
VALUES 
    (1, 10, 'Caesar Salad', 'Fresh salad with lettuce, tomato, and Caesar dressing', 10.50),
    (2, 20, 'Grilled Beef Steak', 'Juicy steak with seasoning', 20.00),
    (3, 15, 'Cheeseburger', 'Classic burger with cheese and fries', 12.75),
    (4, 25, 'Spaghetti Bolognese', 'Pasta with meat sauce and parmesan cheese', 15.25),
    (5, 10, 'Turkey Sandwich', 'Fresh sandwich with turkey, lettuce, and tomato', 9.50),
    (6, 30, 'Chicken Noodle Soup', 'Homemade soup with chicken and noodles', 8.75),
    (7, 20, 'French Fries', 'Crispy fries with ketchup', 5.00),
    (8, 10, 'Chocolate Cake', 'Rich chocolate cake with frosting', 7.50),
    (9, 15, 'Iced Tea', 'Refreshing iced tea with lemon', 3.00),
    (10, 5, 'Cappuccino', 'Italian coffee with frothy milk', 4.50),
    (11, 10, 'Green Tea', 'Japanese green tea with antioxidants', 3.50),
    (12, 25, 'Orange Juice', 'Freshly squeezed orange juice', 4.00),
    (13, 10, 'Lager Beer', 'Light beer with a crisp taste', 5.00),
    (14, 20, 'Chardonnay Wine', 'White wine with fruity notes', 8.00),
    (15, 30, 'Mojito Cocktail', 'Refreshing cocktail with rum and mint', 10.00),
    (16, 10, 'Scotch Whiskey', 'Aged whiskey with a smoky flavor', 12.00),
    (17, 5, 'Vodka Martini', 'Classic cocktail with vodka and vermouth', 9.00),
    (18, 10, 'Rum Punch', 'Tropical cocktail with rum and fruit juice', 8.50);



-- Caesar Salad
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(2, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Lettuce'), 0.2),
(2, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Tomato'), 0.1),
(2, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Caesar Dressing'), 0.05);

-- Grilled Beef Steak
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(3, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Beef'), 0.25);

-- Cheeseburger
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(4, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Beef'), 0.15),
(4, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Bread'), 1),
(4, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Cheese'), 0.05);

-- Spaghetti Bolognese
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(5, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Pasta'), 0.2),
(5, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Beef'), 0.1),
(5, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Cheese'), 0.05);

-- Turkey Sandwich
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(6, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Turkey'), 0.1),
(6, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Bread'), 1),
(6, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Lettuce'), 0.05);

-- Chicken Noodle Soup
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(7, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Chicken'), 0.15),
(7, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Noodles'), 0.1);

-- French Fries
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(8, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Potato'), 0.2);

-- Chocolate Cake
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(9, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Chocolate'), 0.1);

-- Iced Tea
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(10, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Tea Leaves'), 0.02),
(10, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Orange'), 0.1);

-- Cappuccino
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(11, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Coffee Beans'), 0.02);

-- Green Tea
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(12, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Tea Leaves'), 0.02);

-- Orange Juice
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(13, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Orange'), 0.2);

-- Lager Beer
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(14, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Beer'), 0.3);

-- Chardonnay Wine
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(15, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Wine'), 0.25);

-- Mojito Cocktail
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(16, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Rum'), 0.05),
(16, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Mint'), 0.02);

-- Scotch Whiskey
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(17, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Whiskey'), 0.05);

-- Vodka Martini
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(18, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Vodka'), 0.05);

-- Rum Punch
INSERT INTO plat_ingredient (id_plat, id_stock, quantite) VALUES
(19, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Rum'), 0.05),
(19, (SELECT id_stock FROM inventaire_de_stock WHERE nom_produit = 'Orange'), 0.1);





INSERT INTO facture (id_reservation, date_facture, mode_paiement)
VALUES 
(2, '2024-12-05', 'Carte de crédit'),
(3, '2024-12-06', 'Espèces');




INSERT INTO commande (id_reservation, id_plat, quantite)
VALUES
    (2, 2, 2),
    (2, 3, 1),
    (2, 4, 1),
    (3, 5, 2),
    (3, 6, 1),
    (3, 7, 1);






--INSERT INTO avis_clients (id_utilisateur, id_reservation, note, commentaire, date_avis)
--VALUES 
--(1, 1, 5, 'Excellent service and food quality!', '2024-12-05 22:30:00'),
--(1, 2, 3, 'Food was good but service could be faster.', '2024-12-06 20:00:00');

INSERT INTO reserver (id_reservation, id_table)
VALUES 
(2, 1),
(3, 2);
