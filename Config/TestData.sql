-- Utiliser ces tables pour tester les requêtes SQL sur la base de données crowdfundingfb

--Table users
INSERT INTO users (name, email, password) VALUES
('Alice', 'alice@example.com', 'password1'),
('Bob', 'bob@example.com', 'password2'),
('Charlie', 'charlie@example.com', 'password3'),
('David', 'david@example.com', 'password4'),
('Eve', 'eve@example.com', 'password5');

-- Table projects
INSERT INTO projects (title, description, goal_amount, user_id) VALUES
('Project A', 'Description for Project A', 1000.00, 1),
('Project B', 'Description for Project B', 2000.00, 2),
('Project C', 'Description for Project C', 1500.00, 3),
('Project D', 'Description for Project D', 2500.00, 4),
('Project E', 'Description for Project E', 3000.00, 5);

--Table donations
INSERT INTO donations (amount, project_id, user_id) VALUES
(100.00, 1, 2),
(200.00, 1, 3),
(150.00, 2, 1),
(250.00, 2, 4),
(300.00, 3, 5),
(400.00, 3, 1),
(500.00, 4, 2),
(600.00, 4, 3),
(700.00, 5, 4),
(800.00, 5, 5);