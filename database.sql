-- Jungle Mart Database Schema

-- =========================
-- Categories (Hierarchical Structure)
-- =========================
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  parent_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- =========================
-- Products
-- =========================
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  wholesale_price DECIMAL(10,2) DEFAULT NULL,
  min_order_quantity INT DEFAULT 1,
  images JSON DEFAULT NULL,
  is_featured TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- =========================
-- Inquiries (Contact Form)
-- =========================
CREATE TABLE IF NOT EXISTS inquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- Quotations (RFQ System)
-- =========================
CREATE TABLE IF NOT EXISTS quotations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(255) NOT NULL,
  customer_email VARCHAR(255) NOT NULL,
  customer_phone VARCHAR(50),
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS quotation_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quotation_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  wholesale_price DECIMAL(10,2),
  moq INT DEFAULT 1,
  FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =========================
-- All Categories and Sub-Categories
-- =========================
-- Main Categories
INSERT INTO categories (name) VALUES 
('Plants'),
('Pots'),
('Plant Stands'),
('Accessories'),
('Garden Toys'),
('Garden Lights'),
('Plant Food'),
('Potting Media'),
('Organic Pesticides'),
('Organic Insecticides'),
('Bio Enzymes'),
('Garden Tools'),
('Garden Machineries'),
('Protective Gears'),
('Plug and Play Devices'),
('Irrigation'),
('Industrial Horticulture Products');

-- Plants Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Indoor Plants', 1),
('Outdoor Plants', 1),
('Shrubs & Bushes', 1),
('Palms', 1),
('Flowering Plants', 1),
('Seasonal Plants', 1),
('Exotic Plants', 1),
('Bonsai', 1),
('Air-Purifying Plants', 1),
('Medicinal & Herbal Plants', 1);

-- Pots Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Fiber Pots', 2),
('FRP Pots', 2),
('Plastic Pots', 2),
('Earthen / Terracotta Pots', 2),
('Ceramic Pots', 2),
('Metal Pots', 2),
('Concrete / Stone Pots', 2),
('Hanging Pots', 2),
('Vertical Planters', 2);

-- Plant Stands Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Wooden Stands', 3),
('Metal Stands', 3),
('Multi-Tier Stands', 3),
('Wall-Mounted Stands', 3),
('Designer / Decorative Stands', 3);

-- Accessories Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Pebbles & Stones', 4),
('Decorative Moss', 4),
('Artificial Grass Mats', 4),
('Plant Labels & Tags', 4),
('Drip Trays', 4),
('Miniatures for Terrariums', 4);

-- Garden Toys Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Mini Garden Figurines', 5),
('Children''s Gardening Kits', 5),
('DIY Terrarium Kits', 5),
('Interactive Garden Games', 5);

-- Garden Lights Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Solar Garden Lights', 6),
('LED Pathway Lights', 6),
('Decorative Lanterns', 6),
('String Lights / Festoon Lights', 6),
('Spotlights & Uplights', 6);

-- Plant Food Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Organic Fertilizers', 7),
('Liquid Fertilizers', 7),
('Slow-Release Fertilizers', 7),
('Specialized Fertilizers (Orchids, Succulents, etc.)', 7);

-- Potting Media Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Cocopeat', 8),
('Vermicompost', 8),
('Perlite', 8),
('Vermiculite', 8),
('Soil Mixes (Succulent, Orchid, General Potting Mix)', 8);

-- Organic Pesticides Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Neem Oil Extracts', 9),
('Botanical Sprays', 9),
('Bio Pesticides', 9);

-- Organic Insecticides Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Herbal Sprays', 10),
('Biological Control Products', 10),
('Plant-Based Insect Repellents', 10);

-- Bio Enzymes Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Compost Activators', 11),
('Growth Enhancers', 11),
('Waste Decomposers', 11);

-- Garden Tools Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Hand Tools (Trowels, Pruners, Shears)', 12),
('Digging Tools (Spades, Forks, Hoes)', 12),
('Watering Tools (Cans, Sprayers)', 12),
('Power Tools (Trimmers, Blowers, Lawn Mowers)', 12);

-- Garden Machineries Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Lawn Mowers', 13),
('Brush Cutters', 13),
('Hedge Trimmers', 13),
('Tillers / Cultivators', 13),
('Spraying Machines', 13);

-- Protective Gears Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Gloves', 14),
('Garden Aprons', 14),
('Safety Goggles', 14),
('Knee Pads', 14),
('Sun Protection Hats', 14);

-- Plug and Play Devices Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Self-Watering Pots', 15),
('Hydroponic Kits', 15),
('Smart Planters (IoT based)', 15),
('Vertical Garden Units', 15);

-- Irrigation Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Drip Irrigation Systems', 16),
('Sprinkler Systems', 16),
('Soaker Hoses', 16),
('Automatic Water Timers', 16),
('Rainwater Harvesting Systems', 16);

-- Industrial Horticulture Products Sub-Categories
INSERT INTO categories (name, parent_id) VALUES 
('Greenhouse Structures', 17),
('Shade Nets', 17),
('Mulching Sheets', 17),
('Grow Bags', 17),
('Hydroponic Systems', 17),
('Vertical Farming Units', 17);
