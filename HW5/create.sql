DROP DATABASE fakebook;
CREATE DATABASE IF NOT EXISTS fakebook;
USE fakebook;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    displayname VARCHAR(100),
    username VARCHAR(100),
    hash CHAR(60)
);

CREATE TABLE IF NOT EXISTS posts (
    id INT,
    title VARCHAR(100),
    content VARCHAR(1000),
    FOREIGN KEY (id) REFERENCES users(id)
);
