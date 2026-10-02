CREATE DATABASE IF NOT EXISTS milk_dairy
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE milk_dairy;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin','staff','customer') NOT NULL DEFAULT 'staff',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE farmers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    customer_type ENUM('company','retail') NOT NULL DEFAULT 'company',
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE milk_collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    collection_date DATE NOT NULL,
    shift ENUM('morning','evening') NOT NULL,
    quantity_litres DECIMAL(10,2) NOT NULL,
    collection_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE RESTRICT
);

CREATE TABLE quality_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NULL,
    batch_id INT NULL,
    fat_percent DECIMAL(5,2),
    snf_percent DECIMAL(5,2),
    temperature_c DECIMAL(5,2),
    analyzer_result VARCHAR(100),
    quality_status ENUM('PASS','HOLD','REJECT') NOT NULL DEFAULT 'HOLD',
    tested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chilling_tanks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tank_code VARCHAR(30) NOT NULL UNIQUE,
    capacity_litres DECIMAL(10,2) NOT NULL,
    current_quantity_litres DECIMAL(10,2) NOT NULL DEFAULT 0,
    temperature_c DECIMAL(5,2),
    status ENUM('active','maintenance','inactive') DEFAULT 'active',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_number VARCHAR(50) NOT NULL UNIQUE,
    source_collection_id INT NULL,
    quantity_litres DECIMAL(10,2) NOT NULL,
    production_datetime DATETIME,
    holding_until DATETIME NULL,
    status ENUM('created','chilling','processing','stored','dispatched','hold','rejected') DEFAULT 'created',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE processing_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    pasteurization_status ENUM('pending','completed','failed') DEFAULT 'pending',
    processed_quantity_litres DECIMAL(10,2),
    processing_datetime DATETIME,
    operator_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_number VARCHAR(30) NOT NULL UNIQUE,
    vehicle_type VARCHAR(50),
    driver_name VARCHAR(120),
    driver_phone VARCHAR(20),
    status ENUM('available','in_transit','maintenance','inactive') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dispatches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    customer_id INT NOT NULL,
    vehicle_id INT NULL,
    quantity_litres DECIMAL(10,2) NOT NULL,
    seal_number VARCHAR(50),
    dispatch_datetime DATETIME,
    delivery_status ENUM('pending','in_transit','delivered','cancelled') DEFAULT 'pending',
    received_datetime DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

CREATE TABLE farmer_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    collection_id INT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_status ENUM('pending','paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE RESTRICT,
    FOREIGN KEY (collection_id) REFERENCES milk_collections(id) ON DELETE SET NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    unit VARCHAR(30) DEFAULT 'litre',
    quantity DECIMAL(12,2) DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE machines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    machine_name VARCHAR(150) NOT NULL,
    machine_code VARCHAR(50) UNIQUE,
    status ENUM('running','idle','maintenance','inactive') DEFAULT 'idle',
    last_maintenance DATE NULL,
    next_maintenance DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE traceability_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_reference VARCHAR(100),
    event_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role)
VALUES ('Admin', 'admin@dairy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCzZ6fJvJ4m7M6w5xS6u', 'admin')
ON DUPLICATE KEY UPDATE email=email;
