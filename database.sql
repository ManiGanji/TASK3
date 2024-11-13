CREATE DATABASE IF NOT EXISTS image_annotation;
USE image_annotation;

CREATE TABLE IF NOT EXISTS images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS annotations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    image_id INT,
    annotation_type VARCHAR(50) NOT NULL,
    x_coord FLOAT NOT NULL,
    y_coord FLOAT NOT NULL,
    width FLOAT NOT NULL,
    height FLOAT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE
);
