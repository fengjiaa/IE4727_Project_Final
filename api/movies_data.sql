-- Populate MiraMoo Movies Table

USE `miramoo`;

INSERT INTO `movies` 
(`movie_id`, `movie_name`, `description`, `genre`, `duration_mins`, `poster_url`)
VALUES
(1, 'Moana', 
 'An adventurous teenager sets sail on a daring mission to save her people. Along the way, Moana meets the demigod Maui and together they explore the open ocean on an action-packed voyage.', 
 'Animation, Adventure, Family', 
 107, 
 'https://image.tmdb.org/t/p/original/9tzN8sPbyod2dsa0lwuvrwBDWra.jpg'),

(2, 'Ratatouille', 
 'A rat named Remy dreams of becoming a great French chef despite his family’s wishes. When fate places him in Paris, he finds himself ideally situated beneath a restaurant made famous by his culinary hero.', 
 'Animation, Comedy, Family', 
 111, 
 'https://image.tmdb.org/t/p/original/xKLM5b6WzfFM3kwDZ7AvOVtoGQN.jpg'),

(3, 'Inside Out 2', 
 'Teenager Riley faces a new challenge as her mind’s headquarters is being torn down to make space for new emotions. Riley must navigate this emotional upheaval while growing up and learning to embrace change.', 
 'Animation, Comedy, Adventure', 
 96, 
 'https://image.tmdb.org/t/p/original/vpnVM9B6NMmQpWeZvzLvDESb2QY.jpg'),

(4, 'Tangled', 
 'The magically long-haired Rapunzel has spent her entire life in a tower, but now that a runaway thief has stumbled upon her, she is about to discover the world for the first time—and who she really is.', 
 'Animation, Musical, Adventure', 
 100, 
 'https://image.tmdb.org/t/p/original/ym7Kst6a4uodryxqbGOxmewF235.jpg'),

(5, 'Big Hero 6', 
 'A special bond develops between a plus-sized inflatable robot named Baymax and prodigy Hiro Hamada, who team up with a group of friends to form a band of high-tech heroes.', 
 'Animation, Action, Comedy', 
 102, 
 'https://image.tmdb.org/t/p/original/q6WZxPlic8hpKzCxnzWOFCCLQfo.jpg'),

(6, 'Madagascar', 
 'Spoiled by their easy lives in the Central Park Zoo, four animals escape into the wild — only to find themselves shipped to Madagascar, where they must learn what it really means to be in the wild.', 
 'Animation, Comedy, Adventure', 
 86, 
 'https://image.tmdb.org/t/p/original/zMpJY5CJKUufG9OTw0In4eAFqPX.jpg');

