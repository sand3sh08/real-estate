CREATE DATABASE IF NOT EXISTS real_estate;
USE real_estate;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','seller','buyer','broker') NOT NULL,
    status ENUM('active','pending','rejected','blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    area DECIMAL(10,2),
    location VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    property_type VARCHAR(50),
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    parking INT DEFAULT 0,
    status ENUM('Pending','Approved','Rejected','Sold') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    buyer_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE favourites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (buyer_id, property_id),
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE brokers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    experience VARCHAR(100),
    commission VARCHAR(50),
    company VARCHAR(150),
    contact_number VARCHAR(20),
    email VARCHAR(150),
    office_address TEXT,
    about TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default admin: admin@realestate.com / admin123 (change in production)
INSERT INTO users (name, email, phone, password, role, status)
VALUES ('Admin', 'admin@realestate.com', '9999999999',
        '$2y$10$Va5/M9x3WlCpeLLaS5HrsedZb0fhVU3YrgTxns40HvdjKiel4KL9q', 'admin', 'active');
