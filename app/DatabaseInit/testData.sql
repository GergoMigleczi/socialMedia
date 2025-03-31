DELETE FROM USERS;
ALTER TABLE USERS AUTO_INCREMENT = 1;
-- Insert into USERS
INSERT INTO USERS (email, password, is_admin) VALUES
('randall@example.com', '$2y$10$I15bifkEKmHeRqqnxE/GWuWBC9w/BSmERBD.DgcBBH/0IS/j4Pe8G', FALSE), 
('mike@example.com', '$2y$10$I15bifkEKmHeRqqnxE/GWuWBC9w/BSmERBD.DgcBBH/0IS/j4Pe8G', FALSE), 
('sully@example.com', '$2y$10$I15bifkEKmHeRqqnxE/GWuWBC9w/BSmERBD.DgcBBH/0IS/j4Pe8G', FALSE), 
('boo@example.com', '$2y$10$I15bifkEKmHeRqqnxE/GWuWBC9w/BSmERBD.DgcBBH/0IS/j4Pe8G', FALSE),
('celia@example.com', '$2y$10$I15bifkEKmHeRqqnxE/GWuWBC9w/BSmERBD.DgcBBH/0IS/j4Pe8G', FALSE), 
('roz@example.com', '$2y$10$I15bifkEKmHeRqqnxE/GWuWBC9w/BSmERBD.DgcBBH/0IS/j4Pe8G', FALSE),
('admin@example.com', '$2y$10$I15bifkEKmHeRqqnxE/GWuWBC9w/BSmERBD.DgcBBH/0IS/j4Pe8G', TRUE);

DELETE FROM PROFILES;
ALTER TABLE PROFILES AUTO_INCREMENT = 1;
-- Insert into PROFILES
INSERT INTO PROFILES (user_id, full_name, profile_picture) VALUES
(1, 'Randall', 'randall.png'),
(2, 'Mike Wazowski', 'mike.png'),
(3, 'Sully', 'sully.png'),
(4, 'Boo', 'boo.png'),
(5, 'Celia Mae', 'profile_picture_placeholder.png'),
(6, 'Roz', 'roz.png'),
(7, 'Admin', 'profile_picture_placeholder.png');

DELETE FROM FRIENDSHIPS;
ALTER TABLE FRIENDSHIPS AUTO_INCREMENT = 1;
-- Insert into FRIENDSHIPS
INSERT INTO FRIENDSHIPS (profile_id_1, profile_id_2) VALUES
(1, 2), -- Randall and Mike
(1, 3), -- Randall and Sully
(2, 3), -- Mike and Sully
(2, 4), -- Mike and Boo
(3, 4), -- Sully and Boo
(4, 5), -- Boo and Celia
(5, 6), -- Celia and Roz
(1, 6), -- Randall and Roz
(2, 5); -- Mike and Celia

DELETE FROM CHATS;
ALTER TABLE CHATS AUTO_INCREMENT = 1;
-- Insert into CHATS (group and private chats)
INSERT INTO CHATS (name, is_group_chat) VALUES
('Randall and Mike', FALSE), -- Private chat between Randall and Mike
('Mike and Sully', FALSE); -- Private chat between Mike and Sully

DELETE FROM PROFILES_IN_CHAT;
ALTER TABLE PROFILES_IN_CHAT AUTO_INCREMENT = 1;
-- Insert into PROFILES_IN_CHAT (adding profiles to chats)
INSERT INTO PROFILES_IN_CHAT (chat_id, profile_id, role) VALUES
(1, 1, 'member'), -- Randall in Randall and Mike Chat
(1, 2, 'member'), -- Mike in Randall and Mike Chat

(2, 2, 'member'), -- Mike in Mike and Sully Group Chat
(2, 3, 'member'); -- Sully in Mike and Sully Group Chat

DELETE FROM MESSAGES;
ALTER TABLE MESSAGES AUTO_INCREMENT = 1;
-- Insert into MESSAGES (random messages in the chats)
INSERT INTO MESSAGES (chat_id, sender_profile_id, content, message_type, created_at) VALUES
-- **Chat 1: Randall & Mike spanning 7 days**
-- Day 1
(1, 1, 'Hey Mike, how’s it going?', 'text', '2025-03-16 10:05:00'),
(1, 2, 'All good, Randall! What’s up?', 'text', '2025-03-16 10:07:00'),

-- Day 2
(1, 1, 'Just making sure you’re not slacking off. Heard you and Sully got promoted.', 'text', '2025-03-17 09:30:00'),
(1, 2, 'Pfft, we EARNED that promotion. Hard work, charisma, and not being sneaky.', 'text', '2025-03-17 09:35:00'),

-- Day 3
(1, 1, 'I call it being strategic. You call it sneaky.', 'text', '2025-03-18 14:20:00'),
(1, 2, 'Tomato, tomahto. Anyway, you still bitter about the whole “banished to the human world” thing?', 'text', '2025-03-18 14:25:00'),

-- Day 4
(1, 1, 'Let’s just say, being trapped in a trailer with a crazy old lady and attack dogs changes a monster.', 'text', '2025-03-19 11:00:00'),
(1, 2, 'LOL, classic.', 'text', '2025-03-19 11:05:00'),

-- Day 5
(1, 1, 'Anyway, you guys hiring? Asking for a friend.', 'text', '2025-03-20 08:30:00'),
(1, 2, 'HA! Randall, I’d rather let Roz give me a performance review on repeat.', 'text', '2025-03-20 08:35:00'),

-- Day 6
(1, 1, 'You wound me, Wazowski.', 'text', '2025-03-21 12:15:00'),
(1, 2, 'Good. Now go slither back to your corner, I’ve got work to do.', 'text', '2025-03-21 12:20:00'),

-- Day 7
(1, 1, 'Fine. But one day, I’ll make a comeback.', 'text', '2025-03-22 15:10:00'),
(1, 2, 'Sure, buddy. Right after the Yeti opens a snow cone franchise in Monstropolis.', 'text', '2025-03-22 15:15:00'),

-- **Chat 2: Mike & Sully spanning 7 days**
-- Day 1
(2, 2, 'Sully, we need to talk about work!', 'text', '2025-03-16 08:00:00'),
(2, 3, 'Sure, Mike. What’s the issue?', 'text', '2025-03-16 08:05:00'),

-- Day 2
(2, 2, 'Did you fill out the scare report for last night?', 'text', '2025-03-17 10:10:00'),
(2, 3, 'Uh… define “fill out.”', 'text', '2025-03-17 10:15:00'),

-- Day 3
(2, 2, 'SULLY!', 'text', '2025-03-18 12:30:00'),
(2, 3, 'Okay, okay! I might have… forgotten. But in my defense, Boo called and we ended up chatting for hours!', 'text', '2025-03-18 12:35:00'),

-- Day 4
(2, 2, 'That’s adorable, but also completely irrelevant to why Roz is glaring at me from across the office!', 'text', '2025-03-19 09:40:00'),
(2, 3, 'Oh, come on. Roz glares at everyone.', 'text', '2025-03-19 09:45:00'),

-- Day 5
(2, 2, 'SHE SAID, and I quote: “Wazowski, you didn’t file your paperwork... again.” WITH MENACE, SULLY!', 'text', '2025-03-20 14:20:00'),
(2, 3, 'I’ll handle it, don’t worry.', 'text', '2025-03-20 14:25:00'),

-- Day 6
(2, 2, 'How?!', 'text', '2025-03-21 16:27:00'),
(2, 3, 'I’ll charm her.', 'text', '2025-03-21 16:30:00'),

-- Day 7
(2, 2, 'Sully, the last time you “charmed” Roz, she gave us double the paperwork.', 'text', '2025-03-22 17:05:00'),
(2, 3, 'Right. So… plan B?', 'text', '2025-03-22 17:10:00'),
(2, 2, 'RUN.', 'text', '2025-03-22 17:15:00');

DELETE FROM POSTS;
ALTER TABLE POSTS AUTO_INCREMENT = 1;
-- Insert into POSTS (first post is the profile picture)
INSERT INTO POSTS (profile_id, content, visibility) VALUES
(1, 'This is my profile picture!', 'public'), -- Randall's profile picture post
(2, 'This is my profile picture!', 'public'), -- Mike's profile picture post
(3, 'This is my profile picture!', 'public'), -- Sully's profile picture post
(4, 'This is my profile picture!', 'public'), -- Boo's profile picture post
(5, 'This is my profile picture!', 'public'), -- Celia's profile picture post
(6, 'This is my profile picture!', 'public'), -- Roz's profile picture post
-- Insert into POSTS (other random posts)
/*7*/(1, 'I just finished my new monster design!', 'friends'), -- Randall's post
/*8*/(2, 'Had a great day at work with Sully!', 'friends'), -- Mike's post
/*9*/(3, 'Monsters Inc. is running smoothly today!', 'private'), -- Sully's post
/*10*/(4, "Boo's first day at school!", 'friends'), -- Boo's post
/*11*/(5, 'Just finished a big project with Mike!', 'friends'), -- Celia's post
/*12*/(6, 'Working late at the office again.', 'private'); -- Roz's post

DELETE FROM POST_MEDIA;
ALTER TABLE POST_MEDIA AUTO_INCREMENT = 1;
-- Insert into POST_MEDIA (profile picture as first post media)
INSERT INTO POST_MEDIA (post_id, media_type, media_url, alt_text) VALUES
(1, 'image', 'randall.png', "Randall's Profile Picture"), 
(2, 'image', 'mike.png', "Mike's Profile Picture"),
(3, 'image', 'sully.png', "Sully's Profile Picture"),
(4, 'image', 'boo.png', "Boo's Profile Picture"),
(6, 'image', 'roz.png', "Roz's Profile Picture"),
-- Insert into POST_MEDIA (random media for other posts)
(7, 'image', 'randall_toy.png', "Randall's toy"),
(7, 'image', 'randall_kick.png', "Randall kicking"),
(8, 'image', 'mike_glowup.png', 'Mike before and after'),
(9, 'image', 'sully_running.png', 'Sully at Monsters Inc.'),
(10, 'image', 'boo_first_day_school.png', "Boo's first day at school picture"),
(11, 'image', 'celia_and_mike_project.png', 'Celia and Mike project result'),
(12, 'image', 'roz_late_night_office.png', 'Roz working late at the office');

DELETE FROM LIKES;
ALTER TABLE LIKES AUTO_INCREMENT = 1;
-- Insert into LIKES
INSERT INTO LIKES (post_id, profile_id) VALUES
(1, 2), -- Mike likes Randall's profile picture post
(1, 3), -- Sully likes Randall's profile picture post
(1, 4), -- Boo likes Randall's profile picture post
(2, 1), -- Randall likes Mike's profile picture post
(2, 3), -- Sully likes Mike's profile picture post
(3, 1), -- Randall likes Sully's profile picture post
(3, 2), -- Mike likes Sully's profile picture post
(4, 2), -- Mike likes Boo's profile picture post
(5, 1), -- Randall likes Celia's profile picture post
(5, 6), -- Roz likes Celia's profile picture post
(6, 2), -- Mike likes Roz's profile picture post
(6, 3), -- Sully likes Roz's profile picture post
(7, 1), -- Randall likes Randall's post about new monster design
(7, 2), -- Mike likes Randall's post about new monster design
(8, 3), -- Sully likes Mike and Sully working together post
(8, 4), -- Boo likes Mike and Sully working together post
(9, 2), -- Mike likes Sully at Monsters Inc. post
(10, 5), -- Celia likes Boo's first day at school post
(11, 3), -- Sully likes Celia and Mike project result post
(12, 1); -- Randall likes Roz working late at the office post

DELETE FROM COMMENTS;
ALTER TABLE COMMENTS AUTO_INCREMENT = 1;
-- Insert into COMMENTS
INSERT INTO COMMENTS (post_id, profile_id, content) VALUES
(1, 2, 'Looking good, Randall!'), -- Mike comments on Randall's profile picture post
(1, 3, 'Nice picture!'), -- Sully comments on Randall's profile picture post
(2, 1, 'Thanks, Mike!'), -- Randall comments on Mike's profile picture post
(2, 3, 'Great photo!'), -- Sully comments on Mike's profile picture post
(3, 1, 'Looking great Sully!'), -- Randall comments on Sully's profile picture post
(4, 2, 'So cute Boo!'), -- Mike comments on Boo's profile picture post
(5, 1, 'Good job, Celia!'), -- Randall comments on Celia's post
(5, 6, 'Nice work!'), -- Roz comments on Celia's post
(6, 2, 'Roz, you look tired!'), -- Mike comments on Roz's post
(7, 1, 'Thanks for the feedback, Mike!'), -- Randall comments on his post about new monster design
(7, 2, 'Impressive, Randall!'), -- Mike comments on Randall's new monster design post
(8, 3, 'Great work today, Mike!'), -- Sully comments on Mike and Sully working together post
(9, 2, 'Thanks Sully! We did good today!'), -- Mike comments on Sully at Monsters Inc. post
(10, 5, 'Good luck Boo! You’ll do great!'), -- Celia comments on Boo's first day at school post
(11, 3, 'Nice project, guys! Looking forward to the next one!'), -- Sully comments on Celia and Mike project post
(12, 1, 'Roz, take a break! You’re working too hard!'); -- Randall comments on Roz working late at the office post
