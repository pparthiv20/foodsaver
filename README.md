# 🍽️ Food Saver - Food Waste Reduction Platform

A modern, responsive, full-stack PHP web application that helps reduce food waste by connecting **Restaurants**, **NGOs**, and **Donors** on a single digital platform.

## ✨ Key Features

### 🏪 Restaurant Dashboard
- Post surplus food listings
- Track food status (Available, Claimed, Picked Up, Delivered)
- View NGO details who claimed food
- Update delivery status
- Profile management

### 🤝 NGO Dashboard
- Browse available food from restaurants
- Filter by location, food type, expiry time
- Claim food donations
- Update collection and delivery status
- Track people served

### 👤 User/Donor Dashboard
- Make financial donations
- Browse partner NGOs
- View donation history
- Track impact statistics
- Submit feedback

### 👨‍💼 Admin Dashboard
- Dashboard statistics and charts
- Manage Restaurants (approve, reject, suspend)
- Manage NGOs (approve, reject, block)
- View all food listings
- Track donations and generate reports
- Manage user feedback

### 🔐 Authentication
- Multi-role login (Admin, Restaurant, NGO, User)
- Registration with OTP verification
- Password reset functionality
- CSRF protection
- Secure session management

## 🛠️ Technology Stack

| Category | Technology |
|----------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript |
| **Backend** | PHP 7.4+ |
| **Database** | MySQL 5.7+ |
| **Styling** | Custom CSS with CSS Variables |
| **Icons** | Font Awesome 6 |
| **UI Elements** | Responsive Design |

## 🚀 Installation & Deployment

### Local Development

1. **Clone the repository**
   \\\ash
   git clone https://github.com/pparthiv20/foodsaver.git
   cd foodsaver
   \\\

2. **Create a MySQL database**
   \\\ash
   mysql -u root -p < database/schema.sql
   \\\

3. **Configure the application**
   - Edit \includes/config.php\ with your database credentials

### Database Configuration

Edit \includes/config.php\:

\\\php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'food_saver');
\\\

### Production Deployment

Deploy to your web hosting:
- Upload files to your hosting provider
- Create the MySQL database and import \database/schema.sql\
- Update \includes/config.php\ with production credentials
- Set appropriate file permissions on asset directories
- Configure your domain DNS to point to the web server

### Default Admin Credentials

| Field | Value |
|-------|-------|
| **Username** | admin |
| **Email** | admin@foodsaver.org |
| **Password** | password |

> ⚠️ **Important**: Change the default password immediately after installation!

## 📁 Directory Structure

\\\
foodsaver/
├── assets/                    # Frontend assets
│   ├── css/                   # Stylesheets
│   │   ├── style.css          # Main stylesheet
│   │   ├── dashboards.css     # Dashboard styling
│   │   └── mobile-responsive.css
│   ├── js/                    # JavaScript files
│   │   └── main.js
│   └── uploads/               # File uploads directory
├── dashboards/                # User dashboards
│   ├── admin.php              # Admin dashboard
│   ├── restaurant.php         # Restaurant dashboard
│   ├── ngo.php                # NGO dashboard
│   ├── user.php               # User dashboard
│   ├── admin/                 # Admin sub-pages
│   ├── ngo/                   # NGO sub-pages
│   └── restaurant/            # Restaurant sub-pages
├── database/
│   └── schema.sql             # Database schema
├── docs/                      # Documentation
│   ├── README.md
│   └── assets/
├── includes/                  # Configuration & core files
│   ├── config.php             # Database configuration
│   ├── oauth_handler.php
│   └── PHPMailer-master/      # Email library
├── pages/                     # Page handlers
│   ├── login.php
│   ├── register.php
│   ├── donate.php
│   ├── contact.php
│   └── ...
├── foodsaver/                 # Git submodule
├── index.php                  # Main entry point
├── LICENSE
└── README.md
\\\

## 🔒 Security Features

✅ Password hashing with bcrypt
✅ CSRF token protection
✅ SQL injection prevention with prepared statements
✅ XSS protection with output escaping
✅ Session security
✅ Input validation and sanitization
✅ Role-based access control

## 🌐 Browser Support

| Browser | Version |
|---------|---------|
| Chrome | 80+ |
| Firefox | 75+ |
| Safari | 13+ |
| Edge | 80+ |
| Opera | 67+ |

## 📄 License

This project is open source and available under the **MIT License**.

## 🙏 Credits

- **Icons**: [Font Awesome](https://fontawesome.com)
- **Fonts**: [Google Fonts](https://fonts.google.com)
- **Images**: [Unsplash](https://unsplash.com)

## 📞 Support & Contact

For support, questions, or feedback:
- **Email**: support@foodsaver.org
- **GitHub Issues**: [Report a bug](https://github.com/pparthiv20/foodsaver/issues)
- **Contact Form**: Available on the website

---

Made with ❤️ to reduce food waste and help communities
