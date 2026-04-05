# 🚀 FoodSaver - Quick Setup Guide

## Prerequisites
- XAMPP or WAMP with PHP 7.4+ and MySQL 5.7+
- Web browser

## Installation Steps

### Step 1: Download & Place Files
Copy the `food-saver-php` folder to:
```
C:\xampp\htdocs\food-saver-php\
```

### Step 2: Start XAMPP
- Start **Apache** service
- Start **MySQL** service

### Step 3: Run Setup Script
Open your browser and go to:
```
http://localhost/food-saver-php/setup.php
```

This will automatically:
- ✅ Create the `food_saver` database
- ✅ Create all required tables
- ✅ Insert demo data (restaurants, NGOs, users, donations, etc.)

### Step 4: Access the Application
```
http://localhost/food-saver-php/
```

---

## 🔐 Demo Login Credentials

| Role | Username | Password |
|------|----------|----------|
| **Admin** | `admin` | `admin123` |
| **Restaurant** | `restaurant1` | `test123` |
| **NGO** | `ngo1` | `test123` |
| **User/Donor** | `user1` | `test123` |

---

## 📊 Testing the Reports Feature

1. Login as **Admin** (`admin` / `admin123`)
2. Go to **Reports & Analytics** from the sidebar
3. Click **View PDF** or **Download Excel** for any report:
   - Donation Report
   - Request Fulfillment Report
   - Transaction Summary

---

## 📁 Project Structure

```
food-saver-php/
├── assets/           # CSS, JS, images
├── dashboards/       # Dashboard pages (admin, restaurant, ngo, user)
├── database/         # SQL files
│   ├── schema.sql    # Database structure
│   └── dummy_data.sql # Test data
├── includes/         # PHP config & libraries
├── pages/            # Public pages (login, register, etc.)
├── generate_report.php # Report generator
├── setup.php         # Database setup script
├── index.php         # Homepage
└── .env              # Configuration (auto-created)
```

---

## ⚙️ Configuration

Configuration is stored in `.env` file:

```env
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=food_saver
DEVELOPMENT_MODE=true
```

For localhost with XAMPP default settings, no changes needed!

---

## 🧪 Test Flows

### Flow 1: Restaurant Posts Food
1. Login as `restaurant1` / `test123`
2. Click "Post New Listing"
3. Fill food details and submit

### Flow 2: NGO Claims Food
1. Login as `ngo1` / `test123`
2. Browse "Available Food"
3. Click "Claim" on any listing

### Flow 3: User Makes Donation
1. Login as `user1` / `test123`
2. Click "Donate Now"
3. Select NGO and amount, submit

### Flow 4: Admin Approves/Generates Reports
1. Login as `admin` / `admin123`
2. Approve pending restaurants/NGOs
3. Go to Reports → Download PDF/Excel

---

## 🛠️ Troubleshooting

### "Database connection failed"
- Ensure MySQL is running in XAMPP
- Check `.env` file has correct credentials

### "Table doesn't exist"
- Run `setup.php` again
- Or manually import `database/schema.sql`

### "Login not working"
- Clear browser cookies
- Run `setup.php` to reset demo data

---

## 📝 Manual Database Setup (Alternative)

If `setup.php` doesn't work:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database named `food_saver`
3. Import `database/schema.sql`
4. Import `database/dummy_data.sql`

---

## 🔒 Security Note

After testing, delete `setup.php` for security!

---

Made with ❤️ for college projects
