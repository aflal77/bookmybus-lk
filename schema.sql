-- ==========================================================
-- BookMyBus LK – Sri Lankan Bus Reservation & Transport Management System
-- Enhanced Database Schema & Sample Seed Data
-- ==========================================================

CREATE DATABASE IF NOT EXISTS quickseat;
USE quickseat;

-- Disable foreign key checks for clean teardown
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS maintenance;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS seats;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS buses;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Roles Table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB;

-- 2. Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    nic VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Buses Table
CREATE TABLE buses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_number VARCHAR(20) NOT NULL UNIQUE,
    bus_name VARCHAR(100) NOT NULL,
    total_seats INT NOT NULL DEFAULT 30,
    bus_type ENUM('Normal', 'Semi Luxury', 'Luxury', 'Super Luxury') DEFAULT 'Luxury',
    operator VARCHAR(100) DEFAULT 'SLTB Express',
    ac_type ENUM('AC', 'Non-AC') DEFAULT 'AC',
    wifi TINYINT(1) DEFAULT 1,
    usb_charging TINYINT(1) DEFAULT 1,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    last_maintenance_date DATE DEFAULT NULL,
    next_maintenance_date DATE DEFAULT NULL
) ENGINE=InnoDB;

-- 4. Routes Table
CREATE TABLE routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_id INT NOT NULL,
    route_number VARCHAR(20) DEFAULT 'EX-01',
    origin VARCHAR(50) NOT NULL,
    destination VARCHAR(50) NOT NULL,
    departure_time VARCHAR(20) NOT NULL,
    fare DECIMAL(10, 2) NOT NULL,
    distance_km INT DEFAULT 115,
    estimated_duration VARCHAR(20) DEFAULT '3h 15m',
    intermediate_stops TEXT,
    start_location VARCHAR(120) DEFAULT 'Colombo Bastian Mawatha',
    end_location VARCHAR(120) DEFAULT 'Kandy Goods Shed',
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Seats Table
CREATE TABLE seats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_id INT NOT NULL,
    seat_number INT NOT NULL,
    status ENUM('available', 'booked') DEFAULT 'available',
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bus_seat (bus_id, seat_number)
) ENGINE=InnoDB;

-- 6. Schedules Table
CREATE TABLE schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_id INT NOT NULL,
    route_id INT NOT NULL,
    travel_date DATE NOT NULL,
    departure_time VARCHAR(20) NOT NULL,
    arrival_time VARCHAR(20) NOT NULL,
    fare DECIMAL(10, 2) NOT NULL,
    status ENUM('Scheduled', 'Boarding', 'Departed', 'On Route', 'Arrived', 'Cancelled') DEFAULT 'Scheduled',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Drivers Table
CREATE TABLE drivers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    license_number VARCHAR(50) NOT NULL,
    nic VARCHAR(20) NOT NULL,
    assigned_bus_id INT DEFAULT NULL,
    experience_years INT DEFAULT 8,
    status ENUM('active', 'on_leave', 'inactive') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_bus_id) REFERENCES buses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 8. Bookings Table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_ref VARCHAR(30) NOT NULL UNIQUE,
    user_id INT DEFAULT NULL,
    route_id INT NOT NULL,
    schedule_id INT DEFAULT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_phone VARCHAR(20) NOT NULL,
    passenger_nic VARCHAR(20) DEFAULT '',
    seat_number INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 2500.00,
    payment_method ENUM('Card (Demo)', 'Cash at Counter', 'eZ Cash / mCash') DEFAULT 'Card (Demo)',
    payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'paid',
    booking_status ENUM('confirmed', 'completed', 'cancelled') DEFAULT 'confirmed',
    qr_token VARCHAR(64) NOT NULL,
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 9. Payments Table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_ref VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('paid', 'pending', 'failed', 'refunded') DEFAULT 'paid',
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Maintenance Table
CREATE TABLE maintenance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_id INT NOT NULL,
    service_date DATE NOT NULL,
    maintenance_type ENUM('Routine Service', 'Engine Overhaul', 'Tires & Brakes', 'AC Repair', 'Electrical') DEFAULT 'Routine Service',
    description TEXT,
    cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    next_service_date DATE NOT NULL,
    status ENUM('completed', 'scheduled', 'in_progress') DEFAULT 'completed',
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Reviews Table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    route_id INT NOT NULL,
    bus_id INT NOT NULL,
    booking_id INT DEFAULT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    driver_rating INT DEFAULT 5,
    comfort_rating INT DEFAULT 5,
    cleanliness_rating INT DEFAULT 5,
    punctuality_rating INT DEFAULT 5,
    comment TEXT,
    status ENUM('approved', 'pending', 'rejected') DEFAULT 'approved',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 12. Notifications Table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking', 'payment', 'trip_reminder', 'cancellation', 'schedule_change') DEFAULT 'booking',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 13. Contact Messages Table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==========================================================
-- SEED DATA INSERTION
-- ==========================================================

-- Roles
INSERT INTO roles (id, name, description) VALUES
(1, 'admin', 'Full platform administrator with management, analytics, and reporting capabilities'),
(2, 'staff', 'Station/counter staff who manage passenger boarding and ticket validation'),
(3, 'driver', 'Assigned fleet driver who oversees trip logs, vehicle health, and passenger manifest'),
(4, 'customer', 'Registered passenger with booking, ticket management, and review privileges');

-- Demo Users
-- Passwords:
-- Admin: Admin@123
-- Staff: Staff@123
-- Driver: Driver@123
-- Customer: Customer@123
INSERT INTO users (id, role_id, name, email, phone, nic, password, status) VALUES
(1, 1, 'Supun Wijesinghe', 'admin@bookmybus.lk', '0771230001', '198512345678', '$2y$10$o2COkbQw.uFhNAIgaZhNfO4Yos7vtywaTj4DgrWbLlk4iiZ/s9HG.', 'active'),
(2, 2, 'Chaminda Silva', 'staff@bookmybus.lk', '0771230002', '199023456789', '$2y$10$rvasDg1iSiUm9agGC6mH9O3.xHE7qfUhS7KlvoH6U0h3IFyDoHt4.', 'active'),
(3, 3, 'Bandula Rajapakse', 'driver@bookmybus.lk', '0771230003', '198834567890', '$2y$10$s65EyZO5OSQeDF.VLDc49eKJZ7vgsSVtQ.gxYMFQp8O3Nbtjh7dqC', 'active'),
(4, 4, 'Kasun Perera', 'customer@bookmybus.lk', '0771234567', '199845678901', '$2y$10$JUtOtlY0dZqlhuudMHlsAuJcUnrupfI2e/MVHFLxpzUMrvPj.CV7.', 'active'),
(5, 4, 'Nimali Fernando', 'nimali@gmail.com', '0769876543', '199556789012', '$2y$10$JUtOtlY0dZqlhuudMHlsAuJcUnrupfI2e/MVHFLxpzUMrvPj.CV7.', 'active');

-- Buses (Fleet with realistic operators, types, amenities)
INSERT INTO buses (id, bus_number, bus_name, total_seats, bus_type, operator, ac_type, wifi, usb_charging, status, last_maintenance_date, next_maintenance_date) VALUES
(1, 'NB-1234', 'Kandy Royal Express', 30, 'Super Luxury', 'SLTB Superline', 'AC', 1, 1, 'active', '2026-08-15', '2026-10-15'),
(2, 'WP-5678', 'Southern Coastal Star', 30, 'Luxury', 'Southern Express Co.', 'AC', 1, 1, 'active', '2026-08-20', '2026-10-20'),
(3, 'NC-9012', 'Hill Country Cruiser', 30, 'Luxury', 'Central Way Travels', 'AC', 1, 1, 'active', '2026-07-10', '2026-09-25'),
(4, 'ND-3456', 'Northern Pioneer Superline', 30, 'Super Luxury', 'Jaffna Express Lines', 'AC', 1, 1, 'active', '2026-08-01', '2026-11-01'),
(5, 'SP-7890', 'Ruhunu Highway Voyager', 30, 'Semi Luxury', 'Ruhuna Rapid Transit', 'AC', 1, 0, 'active', '2026-08-28', '2026-10-28'),
(6, 'EP-4321', 'Eastern Sunrise Metro', 30, 'Luxury', 'Batticaloa Link', 'AC', 1, 1, 'active', '2026-07-25', '2026-09-30');

-- Drivers
INSERT INTO drivers (id, user_id, license_number, nic, assigned_bus_id, experience_years, status) VALUES
(1, 3, 'B-893472-WP', '198834567890', 1, 12, 'active');

-- Routes (Realistic Sri Lankan Routes with stops and waypoints)
INSERT INTO routes (id, bus_id, route_number, origin, destination, departure_time, fare, distance_km, estimated_duration, intermediate_stops, start_location, end_location) VALUES
(1, 1, 'EX-01', 'Kandy', 'Colombo', '08:30 AM', 2500.00, 115, '3h 15m', 'Peradeniya, Mawanella, Kegalle, Warakapola, Nittambuwa, Kadawatha', 'Kandy Goods Shed Bus Stand', 'Colombo Bastian Mawatha Private Bus Stand'),
(2, 3, 'EX-01', 'Kandy', 'Colombo', '10:00 AM', 2800.00, 115, '3h 00m', 'Peradeniya, Kegalle, Warakapola, Kadawatha', 'Kandy Goods Shed Bus Stand', 'Colombo Bastian Mawatha Private Bus Stand'),
(3, 1, 'EX-01', 'Colombo', 'Kandy', '02:00 PM', 2400.00, 115, '3h 15m', 'Kadawatha, Nittambuwa, Warakapola, Kegalle, Peradeniya', 'Colombo Bastian Mawatha Private Bus Stand', 'Kandy Goods Shed Bus Stand'),
(4, 2, 'EX-02', 'Colombo', 'Galle', '07:15 AM', 1800.00, 126, '1h 45m', 'Makumbura Multimodal Center, Pinnaduwa Interchange', 'Makumbura Multimodal Center (Kottawa)', 'Galle Central Bus Terminal'),
(5, 2, 'EX-02', 'Galle', 'Colombo', '01:30 PM', 1800.00, 126, '1h 45m', 'Pinnaduwa Interchange, Makumbura Multimodal Center', 'Galle Central Bus Terminal', 'Makumbura Multimodal Center (Kottawa)'),
(6, 4, 'EX-07', 'Colombo', 'Jaffna', '09:00 PM', 3500.00, 396, '7h 30m', 'Kurunegala, Dambulla, Anuradhapura, Vavuniya, Kilinochchi', 'Colombo Bastian Mawatha Private Bus Stand', 'Jaffna Central Bus Stand'),
(7, 5, 'EX-05', 'Negombo', 'Colombo', '06:45 AM', 1200.00, 38, '0h 45m', 'Katunayake Expressway Interchange, Peliyagoda', 'Negombo Bus Stand', 'Colombo Fort Terminal'),
(8, 3, 'EX-10', 'Colombo', 'Nuwara Eliya', '07:00 AM', 3000.00, 168, '5h 15m', 'Avissawella, Yatiyantota, Ginigathena, Hatton, Talawakelle', 'Colombo Bastian Mawatha Private Bus Stand', 'Nuwara Eliya Central Bus Stand'),
(9, 5, 'EX-02', 'Colombo', 'Matara', '03:30 PM', 2100.00, 160, '2h 15m', 'Makumbura, Dodangoda, Pinnaduwa, Godagama', 'Makumbura Multimodal Center (Kottawa)', 'Matara Nilwala Bus Terminal'),
(10, 4, 'EX-04', 'Kurunegala', 'Colombo', '07:30 AM', 1500.00, 94, '2h 00m', 'Mirigama Central Expressway, Kadawatha', 'Kurunegala Central Bus Stand', 'Colombo Bastian Mawatha Private Bus Stand'),
(11, 1, 'EX-09', 'Kandy', 'Galle', '06:00 AM', 3200.00, 225, '4h 45m', 'Peradeniya, Kegalle, Kaduwela Interchange, Pinnaduwa', 'Kandy Goods Shed Bus Stand', 'Galle Central Bus Terminal'),
(12, 3, 'EX-14', 'Kandy', 'Ella', '08:00 AM', 2900.00, 138, '4h 30m', 'Gampola, Nuwara Eliya, Welimada, Bandarawela', 'Kandy Goods Shed Bus Stand', 'Ella Main Street Terminal');

-- Seats: Generate 30 seats for each bus (1 to 6)
-- Bus 1
INSERT INTO seats (bus_id, seat_number, status) VALUES
(1, 1, 'booked'), (1, 2, 'available'), (1, 3, 'available'), (1, 4, 'available'),
(1, 5, 'booked'), (1, 6, 'available'), (1, 7, 'available'), (1, 8, 'booked'),
(1, 9, 'available'), (1, 10, 'available'), (1, 11, 'available'), (1, 12, 'booked'),
(1, 13, 'available'), (1, 14, 'available'), (1, 15, 'available'), (1, 16, 'available'),
(1, 17, 'available'), (1, 18, 'available'), (1, 19, 'available'), (1, 20, 'available'),
(1, 21, 'available'), (1, 22, 'available'), (1, 23, 'available'), (1, 24, 'available'),
(1, 25, 'available'), (1, 26, 'available'), (1, 27, 'available'), (1, 28, 'available'),
(1, 29, 'available'), (1, 30, 'available');

-- Bus 2
INSERT INTO seats (bus_id, seat_number, status) VALUES
(2, 1, 'available'), (2, 2, 'available'), (2, 3, 'booked'), (2, 4, 'available'),
(2, 5, 'available'), (2, 6, 'available'), (2, 7, 'booked'), (2, 8, 'available'),
(2, 9, 'available'), (2, 10, 'available'), (2, 11, 'available'), (2, 12, 'available'),
(2, 13, 'available'), (2, 14, 'available'), (2, 15, 'booked'), (2, 16, 'available'),
(2, 17, 'available'), (2, 18, 'available'), (2, 19, 'available'), (2, 20, 'available'),
(2, 21, 'available'), (2, 22, 'available'), (2, 23, 'available'), (2, 24, 'available'),
(2, 25, 'available'), (2, 26, 'available'), (2, 27, 'available'), (2, 28, 'available'),
(2, 29, 'available'), (2, 30, 'available');

-- Bus 3
INSERT INTO seats (bus_id, seat_number, status) VALUES
(3, 1, 'available'), (3, 2, 'booked'), (3, 3, 'available'), (3, 4, 'booked'),
(3, 5, 'available'), (3, 6, 'available'), (3, 7, 'available'), (3, 8, 'available'),
(3, 9, 'available'), (3, 10, 'booked'), (3, 11, 'available'), (3, 12, 'available'),
(3, 13, 'available'), (3, 14, 'available'), (3, 15, 'available'), (3, 16, 'available'),
(3, 17, 'available'), (3, 18, 'booked'), (3, 19, 'available'), (3, 20, 'available'),
(3, 21, 'available'), (3, 22, 'available'), (3, 23, 'available'), (3, 24, 'available'),
(3, 25, 'available'), (3, 26, 'available'), (3, 27, 'available'), (3, 28, 'available'),
(3, 29, 'available'), (3, 30, 'available');

-- Bus 4
INSERT INTO seats (bus_id, seat_number, status) VALUES
(4, 1, 'available'), (4, 2, 'available'), (4, 3, 'available'), (4, 4, 'available'),
(4, 5, 'available'), (4, 6, 'booked'), (4, 7, 'available'), (4, 8, 'available'),
(4, 9, 'available'), (4, 10, 'available'), (4, 11, 'booked'), (4, 12, 'available'),
(4, 13, 'available'), (4, 14, 'available'), (4, 15, 'available'), (4, 16, 'available'),
(4, 17, 'available'), (4, 18, 'available'), (4, 19, 'available'), (4, 20, 'booked'),
(4, 21, 'available'), (4, 22, 'available'), (4, 23, 'available'), (4, 24, 'available'),
(4, 25, 'available'), (4, 26, 'available'), (4, 27, 'available'), (4, 28, 'available'),
(4, 29, 'available'), (4, 30, 'available');

-- Bus 5
INSERT INTO seats (bus_id, seat_number, status) VALUES
(5, 1, 'available'), (5, 2, 'available'), (5, 3, 'available'), (5, 4, 'available'),
(5, 5, 'booked'), (5, 6, 'available'), (5, 7, 'available'), (5, 8, 'available'),
(5, 9, 'booked'), (5, 10, 'available'), (5, 11, 'available'), (5, 12, 'available'),
(5, 13, 'available'), (5, 14, 'booked'), (5, 15, 'available'), (5, 16, 'available'),
(5, 17, 'available'), (5, 18, 'available'), (5, 19, 'available'), (5, 20, 'available'),
(5, 21, 'available'), (5, 22, 'available'), (5, 23, 'available'), (5, 24, 'available'),
(5, 25, 'available'), (5, 26, 'available'), (5, 27, 'available'), (5, 28, 'available'),
(5, 29, 'available'), (5, 30, 'available');

-- Bus 6
INSERT INTO seats (bus_id, seat_number, status) VALUES
(6, 1, 'available'), (6, 2, 'available'), (6, 3, 'available'), (6, 4, 'available'),
(6, 5, 'available'), (6, 6, 'available'), (6, 7, 'available'), (6, 8, 'available'),
(6, 9, 'available'), (6, 10, 'available'), (6, 11, 'available'), (6, 12, 'available'),
(6, 13, 'available'), (6, 14, 'available'), (6, 15, 'available'), (6, 16, 'available'),
(6, 17, 'available'), (6, 18, 'available'), (6, 19, 'available'), (6, 20, 'available'),
(6, 21, 'available'), (6, 22, 'available'), (6, 23, 'available'), (6, 24, 'available'),
(6, 25, 'available'), (6, 26, 'available'), (6, 27, 'available'), (6, 28, 'available'),
(6, 29, 'available'), (6, 30, 'available');

-- Schedules for Today and Upcoming Days
INSERT INTO schedules (id, bus_id, route_id, travel_date, departure_time, arrival_time, fare, status) VALUES
(1, 1, 1, CURDATE(), '08:30 AM', '11:45 AM', 2500.00, 'Boarding'),
(2, 3, 2, CURDATE(), '10:00 AM', '01:00 PM', 2800.00, 'Scheduled'),
(3, 2, 4, CURDATE(), '07:15 AM', '09:00 AM', 1800.00, 'On Route'),
(4, 4, 6, CURDATE(), '09:00 PM', '04:30 AM', 3500.00, 'Scheduled'),
(5, 5, 9, CURDATE(), '03:30 PM', '05:45 PM', 2100.00, 'Scheduled'),
(6, 1, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '02:00 PM', '05:15 PM', 2400.00, 'Scheduled'),
(7, 2, 5, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '01:30 PM', '03:15 PM', 1800.00, 'Scheduled'),
(8, 3, 8, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '07:00 AM', '12:15 PM', 3000.00, 'Scheduled'),
(9, 1, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:30 AM', '11:45 AM', 2500.00, 'Scheduled');

-- Sample Bookings
INSERT INTO bookings (id, booking_ref, user_id, route_id, schedule_id, passenger_name, passenger_phone, passenger_nic, seat_number, total_amount, payment_method, payment_status, booking_status, qr_token, booking_date) VALUES
(1, 'RL-2026-0001', 4, 1, 1, 'Kasun Perera', '0771234567', '199845678901', 1, 2500.00, 'Card (Demo)', 'paid', 'confirmed', SHA2('RL-2026-0001', 256), '2026-09-10 09:15:00'),
(2, 'RL-2026-0002', 5, 1, 1, 'Kamal Silva', '0714567890', '199212345678', 5, 2500.00, 'eZ Cash / mCash', 'paid', 'confirmed', SHA2('RL-2026-0002', 256), '2026-09-10 11:30:00'),
(3, 'RL-2026-0003', 5, 1, 1, 'Nimali Fernando', '0769876543', '199556789012', 8, 2500.00, 'Card (Demo)', 'paid', 'confirmed', SHA2('RL-2026-0003', 256), '2026-09-10 14:20:00'),
(4, 'RL-2026-0004', NULL, 1, 1, 'Ruwan Jayasinghe', '0782345678', '198734567891', 12, 2500.00, 'Cash at Counter', 'paid', 'confirmed', SHA2('RL-2026-0004', 256), '2026-09-11 08:00:00'),
(5, 'RL-2026-0005', 4, 4, 3, 'Kasun Perera', '0771234567', '199845678901', 3, 1800.00, 'Card (Demo)', 'paid', 'completed', SHA2('RL-2026-0005', 256), '2026-09-08 16:45:00');

-- Payments
INSERT INTO payments (id, booking_id, amount, payment_method, transaction_ref, status, payment_date) VALUES
(1, 1, 2500.00, 'Card (Demo)', 'TXN-RL-781921', 'paid', '2026-09-10 09:15:20'),
(2, 2, 2500.00, 'eZ Cash / mCash', 'TXN-RL-781922', 'paid', '2026-09-10 11:30:15'),
(3, 3, 2500.00, 'Card (Demo)', 'TXN-RL-781923', 'paid', '2026-09-10 14:20:45'),
(4, 4, 2500.00, 'Cash at Counter', 'TXN-RL-781924', 'paid', '2026-09-11 08:00:10'),
(5, 5, 1800.00, 'Card (Demo)', 'TXN-RL-781925', 'paid', '2026-09-08 16:45:30');

-- Maintenance Records
INSERT INTO maintenance (id, bus_id, service_date, maintenance_type, description, cost, next_service_date, status) VALUES
(1, 1, '2026-08-15', 'Routine Service', 'Oil change, air filter replacement, and suspension check', 48500.00, '2026-10-15', 'completed'),
(2, 2, '2026-08-20', 'Tires & Brakes', 'Front brake pads replaced and 4 rear expressway tires aligned', 92000.00, '2026-10-20', 'completed'),
(3, 3, '2026-07-10', 'AC Repair', 'Compressor gas refill and climate control thermostat servicing', 35000.00, '2026-09-25', 'completed'),
(4, 4, '2026-09-15', 'Routine Service', 'Scheduled 50,000 km general overhaul', 55000.00, '2026-11-15', 'scheduled');

-- Customer Reviews
INSERT INTO reviews (id, user_id, route_id, bus_id, booking_id, rating, driver_rating, comfort_rating, cleanliness_rating, punctuality_rating, comment, status, created_at) VALUES
(1, 4, 1, 1, 1, 5, 5, 5, 5, 5, 'Outstanding service! Kandy Express left right on time and the AC was ice cold. Reclining seats and Wi-Fi worked great.', 'approved', '2026-09-11 10:00:00'),
(2, 5, 4, 2, 5, 5, 5, 4, 5, 5, 'Southern expressway bus was very fast and smooth. Got to Galle in under 2 hours without any hassle.', 'approved', '2026-09-09 11:30:00'),
(3, 4, 4, 2, NULL, 4, 4, 5, 4, 5, 'Very safe driving and polite conductor. Will definitely book through BookMyBus LK again.', 'approved', '2026-09-05 14:15:00');

-- Notifications
INSERT INTO notifications (id, user_id, title, message, type, is_read, created_at) VALUES
(1, 4, 'Booking Confirmed (RL-2026-0001)', 'Your booking for Kandy to Colombo (Seat #1) has been confirmed. Have a safe journey!', 'booking', 1, '2026-09-10 09:15:05'),
(2, 4, 'Upcoming Trip Reminder', 'Your bus departs today at 08:30 AM from Kandy Goods Shed. Please arrive 15 minutes early.', 'trip_reminder', 0, '2026-09-11 06:30:00'),
(3, 1, 'Maintenance Reminder', 'Hill Country Cruiser (NC-9012) is due for servicing on 25 September 2026.', 'schedule_change', 0, '2026-09-11 08:00:00');

-- Contact Messages
INSERT INTO contact_messages (id, name, email, subject, message, status, created_at) VALUES
(1, 'Damith Weerasinghe', 'damith@gmail.com', 'Inquiry about Colombo to Jaffna night bus', 'Could you please let me know if baggage storage is included in the fare for the night superline?', 'unread', '2026-09-11 14:30:00');
