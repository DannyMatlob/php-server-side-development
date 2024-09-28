-- Inserting data into the posts table
INSERT INTO posts (id, title, content) VALUES
(1, 'First Post', 'This is the content of the first post.'),
(1, 'Second Post', 'This is the content of the second post.'),

SELECT displayname, title, content FROM posts, users WHERE posts.id = 1;