USE cybersec_platform;

-- Insert CTF categories
INSERT INTO ctf_categories (name, description, icon) VALUES
('Web', 'Web application security challenges', 'web'),
('Forensics', 'Digital forensics and investigation', 'search'),
('Cryptography', 'Encryption and decryption challenges', 'lock'),
('Pwn', 'Binary exploitation and reverse engineering', 'terminal'),
('OSINT', 'Open source intelligence gathering', 'eye'),
('API', 'API security and testing challenges', 'code');

-- Insert sample courses
INSERT INTO courses (title, description, difficulty_level, duration_hours, is_published) VALUES
('Introduction to Cybersecurity', 'Learn the fundamentals of cybersecurity including common threats, defense strategies, and security principles.', 'beginner', 8, TRUE),
('Web Application Security', 'Deep dive into web application vulnerabilities like XSS, SQL injection, and CSRF attacks.', 'intermediate', 12, TRUE),
('Network Security Fundamentals', 'Understanding network protocols, firewalls, and intrusion detection systems.', 'intermediate', 10, TRUE),
('Ethical Hacking Basics', 'Learn penetration testing methodologies and ethical hacking techniques.', 'advanced', 15, TRUE);

-- Insert sample lessons for first course
INSERT INTO course_lessons (course_id, title, content, lesson_order, duration_minutes) VALUES
(1, 'What is Cybersecurity?', 'Introduction to the field of cybersecurity and its importance in today\'s digital world.', 1, 30),
(1, 'Common Cyber Threats', 'Overview of malware, phishing, social engineering, and other common threats.', 2, 45),
(1, 'Security Principles', 'Understanding CIA triad: Confidentiality, Integrity, and Availability.', 3, 40),
(1, 'Password Security', 'Best practices for creating and managing secure passwords.', 4, 25);

-- Insert sample CTF challenges
INSERT INTO ctf_challenges (title, description, category_id, difficulty, points, flag, hints) VALUES
('Basic SQL Injection', 'Find the hidden flag by exploiting a SQL injection vulnerability in this login form.', 1, 'easy', 100, 'CTF{sql_1nj3ct10n_b4s1cs}', 'Try using single quotes to break the SQL query'),
('Caesar Cipher', 'Decode this message encrypted with a Caesar cipher: FDHVDU_FLSKHU_LV_HDV', 3, 'easy', 50, 'CTF{caesar_cipher_is_easy}', 'The shift value is 3'),
('Hidden in Plain Sight', 'Examine this image file to find the hidden flag.', 2, 'medium', 150, 'CTF{st3g4n0gr4phy_fun}', 'Use a hex editor or strings command'),
('Buffer Overflow Basics', 'Exploit this simple buffer overflow to get the flag.', 4, 'hard', 300, 'CTF{buff3r_0v3rfl0w_pwn3d}', 'The buffer size is 64 bytes');

-- Insert admin user (password: admin123 - should be hashed in real implementation)
INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES
('admin', 'admin@cybersec.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin');

-- Insert sample testimonials
INSERT INTO testimonials (name, role, content, rating, is_featured, is_approved) VALUES
('Sarah Johnson', 'Security Analyst', 'This platform helped me transition into cybersecurity. The CTF challenges are engaging and the courses are well-structured.', 5, TRUE, TRUE),
('Mike Chen', 'Penetration Tester', 'Excellent hands-on learning experience. The practical challenges really prepare you for real-world scenarios.', 5, TRUE, TRUE),
('Alex Rodriguez', 'Student', 'Great beginner-friendly content with progressive difficulty. Highly recommend for anyone starting in cybersecurity.', 4, FALSE, TRUE);
