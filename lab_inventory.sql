CREATE DATABASE lab_inventory;
USE lab_inventory;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,          
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,             
    serial_number VARCHAR(50) UNIQUE,        
    category ENUM('Computer','Monitor','Keyboard','Mouse','Printer','Other') DEFAULT 'Other',
    quantity INT DEFAULT 1,
    status ENUM('Working','Damaged','Disposed','In Repair') DEFAULT 'Working',
    description TEXT,
    location VARCHAR(100) DEFAULT 'Lab 101',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


ALTER TABLE items
ADD COLUMN image_filename VARCHAR(255) DEFAULT NULL AFTER description;

CREATE TABLE borrows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    borrower_name VARCHAR(100) NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date TIMESTAMP NULL,
    status ENUM('Borrowed','Returned') DEFAULT 'Borrowed',
    FOREIGN KEY (item_id) REFERENCES items(id)
);