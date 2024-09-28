DROP DATABASE bank;
CREATE DATABASE IF NOT EXISTS bank;
USE bank;

CREATE TABLE IF NOT EXISTS balance (
    email VARCHAR(100),
    bal FLOAT DEFAULT 0
);

INSERT INTO balance VALUES ("danny@gmail.com", 10); 
INSERT INTO balance (email) VALUES ("bob@default.com");   