USE project_middleware;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    number VARCHAR(20),
    email VARCHAR(100)
);