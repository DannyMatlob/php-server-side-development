DROP DATABASE fakereddit;
CREATE DATABASE IF NOT EXISTS fakereddit;
USE fakereddit;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    hash CHAR(60)
);

CREATE TABLE IF NOT EXISTS threads (
    id INT,
    title VARCHAR(100),
    content VARCHAR(1000),
    FOREIGN KEY (id) REFERENCES users(id)
);
