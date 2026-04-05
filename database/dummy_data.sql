-- Food-Saver Dummy Data
-- Realistic test data for demo/testing purposes
-- Run this after schema.sql

-- =====================================================
-- DEMO CREDENTIALS:
-- Admin:      admin / admin123
-- Restaurant: restaurant1 / test123
-- NGO:        ngo1 / test123
-- User:       user1 / test123
-- =====================================================

-- Password hashes (bcrypt):
-- 'admin123' = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- 'test123'  = $2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK

-- =====================================================
-- ADMINS (Demo login: admin / admin123)
-- =====================================================
UPDATE admins SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';

-- =====================================================
-- RESTAURANTS (15 records)
-- Demo login: restaurant1 / test123
-- =====================================================
INSERT INTO restaurants (username, email, password, restaurant_name, owner_name, phone, address, city, state, pincode, cuisine_type, license_number, description, status) VALUES
('restaurant1', 'restaurant1@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Green Leaf Bistro', 'Rajesh Kumar', '9876543210', '123 MG Road, Koramangala', 'Bangalore', 'Karnataka', '560034', 'Multi-cuisine', 'FSSAI2024001', 'A family restaurant serving fresh, organic meals', 'approved'),
('restaurant2', 'restaurant2@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Spice Garden', 'Priya Sharma', '9876543211', '45 Indiranagar Main Road', 'Bangalore', 'Karnataka', '560038', 'Indian', 'FSSAI2024002', 'Authentic Indian cuisine with a modern twist', 'approved'),
('restaurant3', 'restaurant3@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Pizza Palace', 'Amit Patel', '9876543212', '78 Commercial Street', 'Bangalore', 'Karnataka', '560001', 'Italian', 'FSSAI2024003', 'Best wood-fired pizzas in town', 'approved'),
('restaurant4', 'restaurant4@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Tandoori Nights', 'Vikram Singh', '9876543213', '22 Whitefield Main Road', 'Bangalore', 'Karnataka', '560066', 'North Indian', 'FSSAI2024004', 'Traditional tandoor specialties', 'approved'),
('restaurant5', 'restaurant5@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Veggie Delight', 'Anita Joshi', '9876543214', '56 JP Nagar 2nd Phase', 'Bangalore', 'Karnataka', '560078', 'Vegetarian', 'FSSAI2024005', 'Pure vegetarian restaurant with organic ingredients', 'approved'),
('restaurant6', 'restaurant6@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Dragon Wok', 'Chen Wei', '9876543215', '89 Marathahalli Bridge', 'Bangalore', 'Karnataka', '560037', 'Chinese', 'FSSAI2024006', 'Authentic Chinese cuisine', 'approved'),
('restaurant7', 'restaurant7@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'South Spice', 'Lakshmi Iyer', '9876543216', '34 Jayanagar 4th Block', 'Bangalore', 'Karnataka', '560041', 'South Indian', 'FSSAI2024007', 'Authentic South Indian delicacies', 'approved'),
('restaurant8', 'restaurant8@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Biryani House', 'Mohammed Iqbal', '9876543217', '67 RT Nagar Main Road', 'Bangalore', 'Karnataka', '560032', 'Hyderabadi', 'FSSAI2024008', 'Famous for authentic dum biryani', 'approved'),
('restaurant9', 'restaurant9@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'The Breakfast Club', 'Sarah Thomas', '9876543218', '12 HSR Layout Sector 1', 'Bangalore', 'Karnataka', '560102', 'Continental', 'FSSAI2024009', 'All-day breakfast and brunch', 'approved'),
('restaurant10', 'restaurant10@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Curry Leaves', 'Ramesh Menon', '9876543219', '90 Electronic City Phase 1', 'Bangalore', 'Karnataka', '560100', 'Kerala', 'FSSAI2024010', 'Traditional Kerala cuisine', 'approved'),
('restaurant11', 'restaurant11@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Urban Cafe', 'Neha Gupta', '9876543220', '45 Cunningham Road', 'Bangalore', 'Karnataka', '560052', 'Cafe', 'FSSAI2024011', 'Modern cafe with healthy options', 'approved'),
('restaurant12', 'restaurant12@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Momo Junction', 'Tenzin Dorje', '9876543221', '23 Residency Road', 'Bangalore', 'Karnataka', '560025', 'Tibetan', 'FSSAI2024012', 'Authentic Tibetan momos and thukpa', 'approved'),
('restaurant13', 'restaurant13@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Grill Master', 'David Fernandes', '9876543222', '78 Brigade Road', 'Bangalore', 'Karnataka', '560001', 'BBQ', 'FSSAI2024013', 'Best grills and steaks in the city', 'approved'),
('restaurant14', 'restaurant14@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Thai Orchid', 'Suporn Chai', '9876543223', '56 Lavelle Road', 'Bangalore', 'Karnataka', '560001', 'Thai', 'FSSAI2024014', 'Authentic Thai flavors', 'pending'),
('restaurant15', 'restaurant15@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Dosa Corner', 'Venkat Rao', '9876543224', '34 Basavanagudi', 'Bangalore', 'Karnataka', '560004', 'South Indian', 'FSSAI2024015', 'Crispy dosas since 1985', 'pending');

-- =====================================================
-- NGOs (12 records)
-- Demo login: ngo1 / test123
-- =====================================================
INSERT INTO ngos (username, email, password, ngo_name, registration_number, contact_person, phone, email_contact, address, city, state, pincode, website, description, service_areas, status) VALUES
('ngo1', 'ngo1@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Feeding India Foundation', 'NGO2024001', 'Ankit Verma', '9988776655', 'contact@feedingindia.org', '45 Richmond Road', 'Bangalore', 'Karnataka', '560025', 'www.feedingindia.org', 'Working to eliminate hunger and food waste across India', 'Bangalore, Mysore, Mangalore', 'approved'),
('ngo2', 'ngo2@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Robin Hood Army', 'NGO2024002', 'Neel Ghose', '9988776656', 'bangalore@robinhoodarmy.com', '78 Koramangala 5th Block', 'Bangalore', 'Karnataka', '560034', 'www.robinhoodarmy.com', 'Volunteer organization serving food to the less fortunate', 'All Bangalore areas', 'approved'),
('ngo3', 'ngo3@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Akshaya Patra', 'NGO2024003', 'Mohan Das', '9988776657', 'info@akshayapatra.org', '90 HRBR Layout', 'Bangalore', 'Karnataka', '560043', 'www.akshayapatra.org', 'Mid-day meal program for school children', 'Schools across Karnataka', 'approved'),
('ngo4', 'ngo4@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'No Food Waste', 'NGO2024004', 'Shruti Reddy', '9988776658', 'hello@nofoodwaste.in', '23 Indiranagar', 'Bangalore', 'Karnataka', '560038', 'www.nofoodwaste.in', 'Collecting and redistributing surplus food', 'Central and East Bangalore', 'approved'),
('ngo5', 'ngo5@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Smile Foundation', 'NGO2024005', 'Kavita Menon', '9988776659', 'bangalore@smilefoundationindia.org', '56 Jayanagar', 'Bangalore', 'Karnataka', '560041', 'www.smilefoundationindia.org', 'Education and nutrition for underprivileged children', 'South Bangalore', 'approved'),
('ngo6', 'ngo6@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Goonj', 'NGO2024006', 'Anshu Gupta', '9988776660', 'karnataka@goonj.org', '12 Malleshwaram', 'Bangalore', 'Karnataka', '560003', 'www.goonj.org', 'Disaster relief and rural development', 'Pan Karnataka', 'approved'),
('ngo7', 'ngo7@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Seva Cafe', 'NGO2024007', 'Jayesh Patel', '9988776661', 'info@sevacafe.org', '34 MG Road', 'Bangalore', 'Karnataka', '560001', 'www.sevacafe.org', 'Gift economy restaurant and food distribution', 'Central Bangalore', 'approved'),
('ngo8', 'ngo8@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Child Rights Foundation', 'NGO2024008', 'Ritu Sharma', '9988776662', 'blr@childrightsandyou.com', '67 Whitefield', 'Bangalore', 'Karnataka', '560066', 'www.childrightsandyou.com', 'Ensuring rights and nutrition for children', 'East Bangalore', 'approved'),
('ngo9', 'ngo9@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Annamrita', 'NGO2024009', 'Krishna Das', '9988776663', 'bangalore@annamrita.org', '89 BTM Layout', 'Bangalore', 'Karnataka', '560076', 'www.annamrita.org', 'Providing nutritious meals to school children', 'South and Central Bangalore', 'approved'),
('ngo10', 'ngo10@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'HelpAge India', 'NGO2024010', 'Mathew Thomas', '9988776664', 'karnataka@helpageindia.org', '45 Rajajinagar', 'Bangalore', 'Karnataka', '560010', 'www.helpageindia.org', 'Supporting elderly with food and healthcare', 'North Bangalore', 'approved'),
('ngo11', 'ngo11@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Waste Warriors', 'NGO2024011', 'Varun Sharma', '9988776665', 'info@wastewarriors.org', '23 Yelahanka', 'Bangalore', 'Karnataka', '560064', 'www.wastewarriors.org', 'Zero waste and food rescue initiative', 'North Bangalore', 'pending'),
('ngo12', 'ngo12@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Food Bank India', 'NGO2024012', 'Preeti Nair', '9988776666', 'karnataka@foodbankindia.org', '78 Electronic City', 'Bangalore', 'Karnataka', '560100', 'www.foodbankindia.org', 'Food banking network for hunger relief', 'South Bangalore', 'pending');

-- =====================================================
-- USERS/DONORS (15 records)
-- Demo login: user1 / test123
-- =====================================================
INSERT INTO users (username, email, password, full_name, phone, address, city, state, status) VALUES
('user1', 'user1@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Rahul Sharma', '9111222333', '123 HSR Layout', 'Bangalore', 'Karnataka', 'active'),
('user2', 'user2@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Priya Verma', '9111222334', '45 Koramangala', 'Bangalore', 'Karnataka', 'active'),
('user3', 'user3@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Amit Kumar', '9111222335', '78 Whitefield', 'Bangalore', 'Karnataka', 'active'),
('user4', 'user4@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Sneha Reddy', '9111222336', '90 Indiranagar', 'Bangalore', 'Karnataka', 'active'),
('user5', 'user5@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Vikram Joshi', '9111222337', '12 Jayanagar', 'Bangalore', 'Karnataka', 'active'),
('user6', 'user6@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Anjali Patel', '9111222338', '34 JP Nagar', 'Bangalore', 'Karnataka', 'active'),
('user7', 'user7@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Rohan Singh', '9111222339', '56 BTM Layout', 'Bangalore', 'Karnataka', 'active'),
('user8', 'user8@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Kavitha Nair', '9111222340', '23 Marathahalli', 'Bangalore', 'Karnataka', 'active'),
('user9', 'user9@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Suresh Iyer', '9111222341', '67 Electronic City', 'Bangalore', 'Karnataka', 'active'),
('user10', 'user10@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Meera Gupta', '9111222342', '89 Yelahanka', 'Bangalore', 'Karnataka', 'active'),
('user11', 'user11@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Arjun Das', '9111222343', '45 Malleshwaram', 'Bangalore', 'Karnataka', 'active'),
('user12', 'user12@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Deepa Menon', '9111222344', '12 Rajajinagar', 'Bangalore', 'Karnataka', 'active'),
('user13', 'user13@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Karthik Rao', '9111222345', '78 Basavanagudi', 'Bangalore', 'Karnataka', 'active'),
('user14', 'user14@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Lakshmi Prasad', '9111222346', '34 RT Nagar', 'Bangalore', 'Karnataka', 'active'),
('user15', 'user15@foodsaver.demo', '$2y$10$HfzIhGCY8LqXXGGG8XCZQ.ZFoG6d.JLt.NBGnI8WIrH7RBxKGGfMK', 'Nithya Subramanian', '9111222347', '56 Hebbal', 'Bangalore', 'Karnataka', 'active');

-- =====================================================
-- FOOD LISTINGS (20 records for Jan/Feb/Mar 2026)
-- =====================================================
INSERT INTO food_listings (restaurant_id, food_name, food_type, category, quantity, servings, expiry_date, pickup_time_from, pickup_time_to, description, pickup_address, status, claimed_by, claimed_at, picked_up_at, delivered_at, people_served, created_at) VALUES
-- January 2026 (7 records)
(1, 'Mixed Vegetable Curry', 'veg', 'Main Course', '5 kg', 25, '2026-01-10', '18:00:00', '21:00:00', 'Fresh mixed vegetable curry with rice', '123 MG Road, Koramangala', 'delivered', 1, '2026-01-08 10:00:00', '2026-01-08 14:00:00', '2026-01-08 16:00:00', 24, '2026-01-08 09:00:00'),
(2, 'Paneer Butter Masala', 'veg', 'Main Course', '3 kg', 15, '2026-01-15', '19:00:00', '22:00:00', 'Rich and creamy paneer dish', '45 Indiranagar Main Road', 'delivered', 2, '2026-01-12 11:00:00', '2026-01-12 15:00:00', '2026-01-12 17:00:00', 14, '2026-01-12 08:00:00'),
(3, 'Margherita Pizza', 'veg', 'Fast Food', '10 pieces', 10, '2026-01-18', '20:00:00', '23:00:00', 'Classic wood-fired pizzas', '78 Commercial Street', 'delivered', 3, '2026-01-17 12:00:00', '2026-01-17 16:00:00', '2026-01-17 18:00:00', 10, '2026-01-17 10:00:00'),
(4, 'Chicken Biryani', 'non-veg', 'Main Course', '8 kg', 40, '2026-01-22', '12:00:00', '15:00:00', 'Hyderabadi style dum biryani', '22 Whitefield Main Road', 'delivered', 4, '2026-01-20 09:00:00', '2026-01-20 13:00:00', '2026-01-20 15:00:00', 38, '2026-01-20 08:00:00'),
(5, 'Masala Dosa', 'veg', 'Breakfast', '30 pieces', 30, '2026-01-25', '08:00:00', '11:00:00', 'Crispy dosas with sambar and chutney', '56 JP Nagar 2nd Phase', 'delivered', 5, '2026-01-24 07:00:00', '2026-01-24 09:00:00', '2026-01-24 11:00:00', 28, '2026-01-24 06:00:00'),
(6, 'Fried Rice', 'veg', 'Main Course', '6 kg', 30, '2026-01-28', '13:00:00', '16:00:00', 'Vegetable fried rice', '89 Marathahalli Bridge', 'delivered', 6, '2026-01-27 11:00:00', '2026-01-27 14:00:00', '2026-01-27 16:00:00', 29, '2026-01-27 10:00:00'),
(7, 'Idli Sambar', 'veg', 'Breakfast', '50 pieces', 50, '2026-01-30', '07:00:00', '10:00:00', 'Soft idlis with sambar and chutneys', '34 Jayanagar 4th Block', 'claimed', 7, '2026-01-29 06:30:00', NULL, NULL, 0, '2026-01-29 06:00:00'),

-- February 2026 (7 records)
(8, 'Mutton Biryani', 'non-veg', 'Main Course', '5 kg', 25, '2026-02-05', '12:00:00', '15:00:00', 'Authentic Hyderabadi mutton biryani', '67 RT Nagar Main Road', 'delivered', 8, '2026-02-04 10:00:00', '2026-02-04 13:00:00', '2026-02-04 15:00:00', 24, '2026-02-04 09:00:00'),
(9, 'Pancakes', 'veg', 'Breakfast', '20 pieces', 20, '2026-02-08', '09:00:00', '12:00:00', 'Fluffy pancakes with maple syrup', '12 HSR Layout Sector 1', 'delivered', 9, '2026-02-07 08:00:00', '2026-02-07 10:00:00', '2026-02-07 12:00:00', 18, '2026-02-07 07:00:00'),
(10, 'Fish Curry', 'non-veg', 'Main Course', '4 kg', 20, '2026-02-12', '13:00:00', '16:00:00', 'Kerala style fish curry', '90 Electronic City Phase 1', 'delivered', 10, '2026-02-11 11:00:00', '2026-02-11 14:00:00', '2026-02-11 16:00:00', 19, '2026-02-11 10:00:00'),
(11, 'Sandwiches', 'veg', 'Snacks', '25 pieces', 25, '2026-02-15', '14:00:00', '17:00:00', 'Assorted vegetable sandwiches', '45 Cunningham Road', 'delivered', 1, '2026-02-14 12:00:00', '2026-02-14 15:00:00', '2026-02-14 17:00:00', 23, '2026-02-14 11:00:00'),
(12, 'Momos', 'veg', 'Snacks', '100 pieces', 50, '2026-02-18', '17:00:00', '20:00:00', 'Steamed vegetable momos', '23 Residency Road', 'delivered', 2, '2026-02-17 15:00:00', '2026-02-17 18:00:00', '2026-02-17 20:00:00', 48, '2026-02-17 14:00:00'),
(13, 'Grilled Chicken', 'non-veg', 'Main Course', '3 kg', 15, '2026-02-22', '19:00:00', '22:00:00', 'Herb marinated grilled chicken', '78 Brigade Road', 'delivered', 3, '2026-02-21 17:00:00', '2026-02-21 20:00:00', '2026-02-21 22:00:00', 14, '2026-02-21 16:00:00'),
(1, 'Dal Tadka', 'veg', 'Main Course', '4 kg', 20, '2026-02-26', '12:00:00', '15:00:00', 'Yellow dal with tempering', '123 MG Road, Koramangala', 'claimed', 4, '2026-02-25 10:00:00', NULL, NULL, 0, '2026-02-25 09:00:00'),

-- March 2026 (6 records)
(2, 'Chole Bhature', 'veg', 'Main Course', '20 servings', 20, '2026-03-05', '11:00:00', '14:00:00', 'Spicy chickpea curry with fried bread', '45 Indiranagar Main Road', 'delivered', 5, '2026-03-04 09:00:00', '2026-03-04 12:00:00', '2026-03-04 14:00:00', 19, '2026-03-04 08:00:00'),
(3, 'Pasta Alfredo', 'veg', 'Main Course', '3 kg', 15, '2026-03-10', '18:00:00', '21:00:00', 'Creamy white sauce pasta', '78 Commercial Street', 'delivered', 6, '2026-03-09 16:00:00', '2026-03-09 19:00:00', '2026-03-09 21:00:00', 14, '2026-03-09 15:00:00'),
(4, 'Butter Naan', 'veg', 'Bread', '40 pieces', 40, '2026-03-15', '19:00:00', '22:00:00', 'Fresh tandoor baked naan bread', '22 Whitefield Main Road', 'available', NULL, NULL, NULL, NULL, 0, '2026-03-14 18:00:00'),
(5, 'Fruit Salad', 'vegan', 'Dessert', '3 kg', 15, '2026-03-18', '10:00:00', '13:00:00', 'Fresh seasonal fruit salad', '56 JP Nagar 2nd Phase', 'available', NULL, NULL, NULL, NULL, 0, '2026-03-17 09:00:00'),
(6, 'Hakka Noodles', 'veg', 'Main Course', '5 kg', 25, '2026-03-22', '12:00:00', '15:00:00', 'Stir-fried vegetable noodles', '89 Marathahalli Bridge', 'claimed', 7, '2026-03-21 10:00:00', NULL, NULL, 0, '2026-03-21 09:00:00'),
(7, 'Vada Pav', 'veg', 'Snacks', '30 pieces', 30, '2026-03-25', '16:00:00', '19:00:00', 'Mumbai style potato fritters in bread', '34 Jayanagar 4th Block', 'available', NULL, NULL, NULL, NULL, 0, '2026-03-24 15:00:00');

-- =====================================================
-- DONATIONS (20 records for Jan/Feb/Mar 2026)
-- =====================================================
INSERT INTO donations (user_id, ngo_id, amount, currency, payment_method, transaction_id, status, message, anonymous, created_at) VALUES
-- January 2026 donations
(1, 1, 5000.00, 'INR', 'upi', 'TXN2026001001', 'completed', 'Happy to support the cause!', FALSE, '2026-01-05 10:30:00'),
(2, 2, 2500.00, 'INR', 'credit_card', 'TXN2026001002', 'completed', 'Keep up the great work!', FALSE, '2026-01-10 14:20:00'),
(3, 3, 10000.00, 'INR', 'net_banking', 'TXN2026001003', 'completed', 'For the children', TRUE, '2026-01-15 09:15:00'),
(4, 1, 1500.00, 'INR', 'upi', 'TXN2026001004', 'completed', NULL, FALSE, '2026-01-18 16:45:00'),
(5, 4, 3000.00, 'INR', 'debit_card', 'TXN2026001005', 'completed', 'Small contribution', FALSE, '2026-01-22 11:30:00'),
(6, 5, 7500.00, 'INR', 'credit_card', 'TXN2026001006', 'completed', 'Monthly donation', FALSE, '2026-01-28 13:00:00'),
(7, 2, 500.00, 'INR', 'upi', 'TXN2026001007', 'completed', NULL, TRUE, '2026-01-30 17:20:00'),

-- February 2026 donations
(8, 6, 2000.00, 'INR', 'online', 'TXN2026002008', 'completed', 'Thank you for feeding the hungry', FALSE, '2026-02-03 10:00:00'),
(9, 3, 4500.00, 'INR', 'upi', 'TXN2026002009', 'completed', NULL, FALSE, '2026-02-08 12:30:00'),
(10, 7, 1000.00, 'INR', 'debit_card', 'TXN2026002010', 'completed', 'First time donor', FALSE, '2026-02-12 15:45:00'),
(11, 1, 6000.00, 'INR', 'net_banking', 'TXN2026002011', 'completed', 'Anniversary donation', FALSE, '2026-02-15 09:00:00'),
(12, 8, 3500.00, 'INR', 'credit_card', 'TXN2026002012', 'completed', NULL, TRUE, '2026-02-20 14:20:00'),
(13, 4, 800.00, 'INR', 'upi', 'TXN2026002013', 'completed', 'Every bit helps', FALSE, '2026-02-25 16:30:00'),

-- March 2026 donations
(14, 9, 15000.00, 'INR', 'net_banking', 'TXN2026003014', 'completed', 'Corporate matching gift', FALSE, '2026-03-02 11:00:00'),
(15, 5, 2200.00, 'INR', 'upi', 'TXN2026003015', 'completed', NULL, FALSE, '2026-03-08 13:45:00'),
(1, 10, 1800.00, 'INR', 'online', 'TXN2026003016', 'completed', 'Monthly contribution', FALSE, '2026-03-12 10:20:00'),
(2, 6, 4000.00, 'INR', 'credit_card', 'TXN2026003017', 'completed', 'Weekend donation', FALSE, '2026-03-16 15:00:00'),
(3, 1, 500.00, 'INR', 'upi', 'TXN2026003018', 'pending', NULL, FALSE, '2026-03-20 09:30:00'),
(4, 2, 1200.00, 'INR', 'debit_card', 'TXN2026003019', 'completed', 'For a good cause', FALSE, '2026-03-24 12:15:00'),
(5, 3, 950.00, 'INR', 'upi', 'TXN2026003020', 'completed', NULL, TRUE, '2026-03-28 14:00:00');

-- =====================================================
-- FEEDBACK (10 records)
-- =====================================================
INSERT INTO feedback (user_id, user_type, name, email, subject, message, rating, status, created_at) VALUES
(1, 'user', 'Rahul Sharma', 'user1@foodsaver.demo', 'Great Initiative!', 'This platform is doing amazing work. Keep it up!', 5, 'read', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(2, 'user', 'Priya Verma', 'user2@foodsaver.demo', 'Easy to Use', 'The donation process was very smooth and easy.', 4, 'new', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(3, 'user', 'Amit Kumar', 'user3@foodsaver.demo', 'Suggestion for Improvement', 'Would be great to have a mobile app as well.', 4, 'responded', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(4, 'user', 'Sneha Reddy', 'user4@foodsaver.demo', 'Thank You', 'Happy to be part of this movement against food waste.', 5, 'read', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(5, 'user', 'Vikram Joshi', 'user5@foodsaver.demo', 'Question about Donations', 'Can I get a tax receipt for my donations?', 3, 'new', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(NULL, 'user', 'Guest User', 'guest@example.com', 'Partnership Inquiry', 'Our company would like to partner with FoodSaver.', 5, 'new', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 'user', 'Anjali Patel', 'user6@foodsaver.demo', 'Excellent Service', 'The transparency in showing impact is commendable.', 5, 'read', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 'user', 'Rohan Singh', 'user7@foodsaver.demo', 'Feature Request', 'Please add recurring donation option.', 4, 'new', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 'user', 'Kavitha Nair', 'user8@foodsaver.demo', 'Website Feedback', 'The website is very user-friendly and informative.', 5, 'new', NOW()),
(9, 'user', 'Suresh Iyer', 'user9@foodsaver.demo', 'Volunteer Inquiry', 'How can I volunteer for food distribution?', 4, 'new', NOW());

-- =====================================================
-- CONTACT MESSAGES (8 records)
-- =====================================================
INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES
('Arun Mehta', 'arun.mehta@email.com', '9876500001', 'Restaurant Partnership', 'Our restaurant chain would like to join FoodSaver. Please contact us.', 'read', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('Sunita Krishnan', 'sunita.k@email.com', '9876500002', 'NGO Registration Help', 'I need help with the NGO registration process. Can someone guide me?', 'responded', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('Corporate Solutions', 'csr@corpltd.com', '9876500003', 'CSR Partnership', 'We are interested in CSR partnership for food donation drive.', 'new', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('Media House', 'editor@newstoday.com', '9876500004', 'Media Coverage', 'We would like to feature FoodSaver in our sustainability column.', 'read', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Tech Volunteer', 'dev@techie.com', '9876500005', 'Technical Volunteering', 'I am a developer and would like to contribute to the platform.', 'new', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('School Principal', 'principal@dpsschool.edu', '9876500006', 'School Meal Program', 'Can FoodSaver help with our school meal program?', 'new', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Event Manager', 'events@celebrations.com', '9876500007', 'Event Food Donation', 'We have surplus food from events. How can we donate regularly?', 'new', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Student Project', 'student@college.edu', '9876500008', 'Research Interview', 'Conducting research on food waste. Would love to interview your team.', 'new', NOW());

-- =====================================================
-- NOTIFICATIONS (Sample notifications)
-- =====================================================
INSERT INTO notifications (recipient_id, recipient_type, title, message, type, is_read, related_id, related_type, created_at) VALUES
(1, 'admin', 'New Restaurant Registration', 'Thai Orchid has registered and is pending approval.', 'info', FALSE, 14, 'restaurant', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'admin', 'New NGO Registration', 'Waste Warriors has registered and is pending approval.', 'info', FALSE, 11, 'ngo', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'restaurant', 'Food Claimed', 'Robin Hood Army has claimed your Margherita Pizza listing.', 'success', FALSE, 3, 'food_listing', NOW()),
(2, 'restaurant', 'Food Claimed', 'Akshaya Patra has claimed your Chicken Biryani listing.', 'success', TRUE, 4, 'food_listing', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'ngo', 'New Food Available', 'Green Leaf Bistro has posted new food: Mixed Vegetable Curry', 'info', FALSE, 1, 'food_listing', NOW()),
(2, 'ngo', 'New Food Available', 'Spice Garden has posted new food: Paneer Butter Masala', 'info', FALSE, 2, 'food_listing', NOW()),
(1, 'user', 'Donation Successful', 'Thank you! Your donation of ₹5,000 to Feeding India Foundation was successful.', 'success', TRUE, 1, 'donation', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, 'user', 'Donation Successful', 'Thank you! Your donation of ₹2,500 to Robin Hood Army was successful.', 'success', TRUE, 2, 'donation', DATE_SUB(NOW(), INTERVAL 28 DAY));

-- =====================================================
-- ACTIVITY LOGS (Sample entries)
-- =====================================================
INSERT INTO activity_logs (user_id, user_type, action, description, ip_address, created_at) VALUES
(1, 'admin', 'login', 'Admin logged in successfully', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'admin', 'approve_restaurant', 'Approved restaurant: Green Leaf Bistro', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'admin', 'approve_ngo', 'Approved NGO: Feeding India Foundation', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'restaurant', 'login', 'Restaurant logged in successfully', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(1, 'restaurant', 'create_listing', 'Created food listing: Mixed Vegetable Curry', '127.0.0.1', NOW()),
(1, 'ngo', 'login', 'NGO logged in successfully', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(1, 'ngo', 'claim_food', 'Claimed food listing: Margherita Pizza', '127.0.0.1', NOW()),
(1, 'user', 'login', 'User logged in successfully', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'user', 'donation', 'Made donation of ₹5,000 to Feeding India Foundation', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, 'user', 'donation', 'Made donation of ₹2,500 to Robin Hood Army', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 28 DAY));

-- =====================================================
-- Update site statistics
-- =====================================================
UPDATE site_settings SET setting_value = '1270' WHERE setting_key = 'meals_saved';
UPDATE site_settings SET setting_value = '13' WHERE setting_key = 'restaurants_count';
UPDATE site_settings SET setting_value = '10' WHERE setting_key = 'ngos_count';
UPDATE site_settings SET setting_value = '20' WHERE setting_key = 'donations_count';
