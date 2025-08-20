-- Script di inizializzazione database con dati di esempio
-- Sostituisce i model MongoDB con tabelle MySQL e dati di test

USE virtual_fitting_room;

-- Inserimento prodotti di esempio
INSERT INTO products (nome, categoria, marca, descrizione, prezzo, taglie, colori, immagini, caratteristiche, dimensioni2D) VALUES
('T-Shirt Basic Bianca', 'magliette', 'BasicWear', 'T-shirt in cotone 100% traspirante e comoda per uso quotidiano', 19.99,
 '[{"taglia": "XS", "disponibile": true, "quantita": 15}, {"taglia": "S", "disponibile": true, "quantita": 20}, {"taglia": "M", "disponibile": true, "quantita": 25}, {"taglia": "L", "disponibile": true, "quantita": 18}, {"taglia": "XL", "disponibile": true, "quantita": 12}]', -- ERRORE 2 CORRETTO: taglia "L" ora è `taglia`
 '[{"nome": "Bianco", "codiceHex": "#FFFFFF", "immagine": "tshirt_basic_white.jpg"}, {"nome": "Nero", "codiceHex": "#000000", "immagine": "tshirt_basic_black.jpg"}, {"nome": "Grigio", "codiceHex": "#808080", "immagine": "tshirt_basic_gray.jpg"}]',
 '{"frontale": "tshirt_basic_front.jpg", "retro": "tshirt_basic_back.jpg", "dettagli": ["tshirt_basic_detail1.jpg", "tshirt_basic_detail2.jpg"]}',
 '{"materiale": "100% Cotone", "stagione": "Primavera/Estate", "occasione": ["casual", "sport"], "stile": "basic"}',
 '{"larghezza": 200, "altezza": 250, "puntiAncoraggio": [{"x": 100, "y": 50, "tipo": "spalla"}, {"x": 100, "y": 200, "tipo": "vita"}]}'),

('Camicia Oxford Azzurra', 'camicie', 'ClassicStyle', 'Camicia elegante in tessuto Oxford, perfetta per ufficio e occasioni formali', 49.99,
 '[{"taglia": "S", "disponibile": true, "quantita": 8}, {"taglia": "M", "disponibile": true, "quantita": 12}, {"taglia": "L", "disponibile": true, "quantita": 15}, {"taglia": "XL", "disponibile": true, "quantita": 10}]', -- ERRORE 2 CORRETTO
 '[{"nome": "Azzurro", "codiceHex": "#87CEEB", "immagine": "shirt_oxford_blue.jpg"}, {"nome": "Bianco", "codiceHex": "#FFFFFF", "immagine": "shirt_oxford_white.jpg"}, {"nome": "Rosa", "codiceHex": "#FFB6C1", "immagine": "shirt_oxford_pink.jpg"}]',
 '{"frontale": "shirt_oxford_front.jpg", "retro": "shirt_oxford_back.jpg", "dettagli": ["shirt_oxford_collar.jpg", "shirt_oxford_cuff.jpg"]}',
 '{"materiale": "Cotone Oxford", "stagione": "Tutto l\'anno", "occasione": ["formale", "business", "casual-elegante"], "stile": "classico"}',
 '{"larghezza": 220, "altezza": 300, "puntiAncoraggio": [{"x": 110, "y": 60, "tipo": "spalla"}, {"x": 110, "y": 280, "tipo": "vita"}]}'),

('Jeans Slim Fit', 'pantaloni', 'DenimCo', 'Jeans dalla vestibilità slim, realizzati in denim stretch per comfort e stile', 79.99,
 '[{"taglia": "28", "disponibile": true, "quantita": 5}, {"taglia": "30", "disponibile": true, "quantita": 10}, {"taglia": "32", "disponibile": true, "quantita": 15}, {"taglia": "34", "disponibile": true, "quantita": 8}, {"taglia": "36", "disponibile": true, "quantita": 6}]',
 '[{"nome": "Blu Scuro", "codiceHex": "#1e3a8a", "immagine": "jeans_dark_blue.jpg"}, {"nome": "Blu Medio", "codiceHex": "#3b82f6", "immagine": "jeans_medium_blue.jpg"}, {"nome": "Nero", "codiceHex": "#000000", "immagine": "jeans_black.jpg"}]',
 '{"frontale": "jeans_slim_front.jpg", "retro": "jeans_slim_back.jpg", "dettagli": ["jeans_pocket.jpg", "jeans_seam.jpg"]}',
 '{"materiale": "98% Cotone, 2% Elastan", "stagione": "Tutto l\'anno", "occasione": ["casual", "smart-casual"], "stile": "moderno"}',
 '{"larghezza": 180, "altezza": 400, "puntiAncoraggio": [{"x": 90, "y": 50, "tipo": "vita"}, {"x": 90, "y": 380, "tipo": "caviglia"}]}'),

('Giacca Blazer Elegante', 'giacche', 'ElegantWear', 'Blazer raffinato per occasioni formali e professionali', 149.99,
 '[{"taglia": "S", "disponibile": true, "quantita": 4}, {"taglia": "M", "disponibile": true, "quantita": 7}, {"taglia": "L", "disponibile": true, "quantita": 9}, {"taglia": "XL", "disponibile": true, "quantita": 5}]',
 '[{"nome": "Nero", "codiceHex": "#000000", "immagine": "blazer_black.jpg"}, {"nome": "Blu Navy", "codiceHex": "#1e1b4b", "immagine": "blazer_navy.jpg"}, {"nome": "Grigio", "codiceHex": "#6b7280", "immagine": "blazer_gray.jpg"}]',
 '{"frontale": "blazer_front.jpg", "retro": "blazer_back.jpg", "dettagli": ["blazer_button.jpg", "blazer_lapel.jpg"]}',
 '{"materiale": "Lana 70%, Poliestere 30%", "stagione": "Autunno/Inverno", "occasione": ["formale", "business", "cerimonie"], "stile": "elegante"}',
 '{"larghezza": 250, "altezza": 320, "puntiAncoraggio": [{"x": 125, "y": 70, "tipo": "spalla"}, {"x": 125, "y": 300, "tipo": "vita"}]}'),

('Vestito Estivo Floreale', 'vestiti', 'SummerVibes', 'Abito leggero con stampa floreale, perfetto per l\'estate', 89.99,
 '[{"taglia": "XS", "disponibile": true, "quantita": 6}, {"taglia": "S", "disponibile": true, "quantita": 10}, {"taglia": "M", "disponibile": true, "quantita": 12}, {"taglia": "L", "disponibile": true, "quantita": 8}]',
 '[{"nome": "Floreale Rosa", "codiceHex": "#fecaca", "immagine": "dress_floral_pink.jpg"}, {"nome": "Floreale Blu", "codiceHex": "#bfdbfe", "immagine": "dress_floral_blue.jpg"}, {"nome": "Floreale Giallo", "codiceHex": "#fef3c7", "immagine": "dress_floral_yellow.jpg"}]',
 '{"frontale": "dress_summer_front.jpg", "retro": "dress_summer_back.jpg", "dettagli": ["dress_pattern.jpg", "dress_neckline.jpg"]}',
 '{"materiale": "Viscosa 95%, Elastan 5%", "stagione": "Primavera/Estate", "occasione": ["casual", "vacanze", "tempo libero"], "stile": "femminile"}',
 '{"larghezza": 200, "altezza": 380, "puntiAncoraggio": [{"x": 100, "y": 40, "tipo": "spalla"}, {"x": 100, "y": 180, "tipo": "vita"}]}'),

('Gonna a Matita Nera', 'gonne', 'OfficeWear', 'Gonna elegante a matita, ideale per look professionali', 59.99,
 '[{"taglia": "XS", "disponibile": true, "quantita": 5}, {"taglia": "S", "disponibile": true, "quantita": 8}, {"taglia": "M", "disponibile": true, "quantita": 10}, {"taglia": "L", "disponibile": true, "quantita": 7}, {"taglia": "XL", "disponibile": false, "quantita": 0}]',
 '[{"nome": "Nero", "codiceHex": "#000000", "immagine": "skirt_pencil_black.jpg"}, {"nome": "Blu Navy", "codiceHex": "#1e1b4b", "immagine": "skirt_pencil_navy.jpg"}, {"nome": "Grigio", "codiceHex": "#6b7280", "immagine": "skirt_pencil_gray.jpg"}]',
 '{"frontale": "skirt_pencil_front.jpg", "retro": "skirt_pencil_back.jpg", "dettagli": ["skirt_waistband.jpg", "skirt_slit.jpg"]}',
 '{"materiale": "Poliestere 85%, Elastan 15%", "stagione": "Tutto l\'anno", "occasione": ["formale", "business", "ufficio"], "stile": "professionale"}',
 '{"larghezza": 160, "altezza": 220, "puntiAncoraggio": [{"x": 80, "y": 20, "tipo": "vita"}, {"x": 80, "y": 200, "tipo": "orlo"}]}');

-- Inserimento utente di esempio (password: "password123" hashata)
INSERT INTO users (email, password, nome, cognome, preferenze) VALUES
('demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mario', 'Rossi',
 '{"taglie": ["M", "L"], "colori": ["Blu", "Nero", "Grigio"], "stile": "casual-elegante"}'),
('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lucia', 'Bianchi',
 '{"taglie": ["S", "M"], "colori": ["Rosa", "Bianco", "Azzurro"], "stile": "femminile"}'); -- ERRORE 7 CORRETTO
 
-- Creazione directory uploads se non esistono
-- Nota: Questo deve essere fatto a livello di filesystem, non database
-- mkdir -p uploads/user-photos uploads/products
