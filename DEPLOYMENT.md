# Deployment Guide

This guide explains how to deploy Food Saver on GitHub Pages and a web server.

## Dual Deployment Architecture

```
                    GitHub Pages
                  (Static Landing)
                         ↑
                    docs/ folder
                         
                    Your Domain
                         ↑
                   backend/ folder
                  (PHP Backend Server)
```

## GitHub Pages Deployment

### Step 1: Enable GitHub Pages

1. Push your code to GitHub
2. Go to your repository → **Settings**
3. Scroll to **Pages** section
4. Under "Build and deployment":
   - **Source**: Deploy from a branch
   - **Branch**: `main` 
   - **Folder**: `/docs`
5. Click Save
6. Your landing page will be published at:
   ```
   https://your-username.github.io/food-saver-php/
   ```

### Step 2: Custom Domain (Optional)

To use a custom domain for your landing page:

1. In **Settings → Pages**, add your custom domain
2. Update your domain DNS records to point to GitHub Pages
3. GitHub will automatically set up HTTPS

## Backend Server Deployment

### Option 1: Traditional Web Hosting (cPanel, etc.)

1. **Access your hosting control panel**
2. **Upload files**:
   - Use FTP/SFTP to upload the entire `backend/` folder to your web hosting
   - Set the web root to point to the `backend/` directory

3. **Create database**:
   ```sql
   CREATE DATABASE food_saver;
   USE food_saver;
   -- Import schema
   SOURCE database/schema.sql;
   ```

4. **Update configuration**:
   - Edit `backend/includes/config.php`
   - Set production database credentials

5. **Set permissions**:
   ```bash
   chmod 755 backend/
   chmod 777 backend/assets/uploads/
   ```

### Option 2: Cloud Hosting (Heroku, AWS, Linode, DigitalOcean)

#### DigitalOcean App Platform
1. Connect your GitHub repository
2. Select `backend/` as source directory
3. Set environment variables:
   ```
   DB_HOST=your-db-host
   DB_USER=your-db-user
   DB_PASSWORD=your-password
   DB_NAME=food_saver
   ```
4. Deploy

#### AWS Elastic Beanstalk
1. Create `.ebextensions/php.config`:
   ```yaml
   option_settings:
     aws:elasticbeanstalk:application:environment:
       COMPOSER_HOME: /tmp
   ```
2. Deploy using EB CLI:
   ```bash
   eb create food-saver-env
   eb deploy
   ```

### Option 3: Docker (Advanced)

Create `Dockerfile` in `backend/`:
```dockerfile
FROM php:8.0-apache

WORKDIR /var/www/html

COPY . .

RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli

RUN a2enmod rewrite
```

Build and run:
```bash
docker build -t food-saver .
docker run -p 80:80 -p 443:443 food-saver
```

## Database Deployment

### Creating the Database

1. **Using MySQL CLI**:
   ```bash
   mysql -u root -p < backend/database/schema.sql
   ```

2. **Using phpMyAdmin**:
   - Login to phpMyAdmin
   - Create new database `food_saver`
   - Import `backend/database/schema.sql`

3. **Using GUI Tools** (MySQL Workbench, DBeaver):
   - Create new database
   - Run the schema SQL file

### Environment Variables

Store sensitive credentials in environment variables:

```php
// backend/includes/config.php
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('DB_NAME', getenv('DB_NAME'));
```

## Connecting Frontend & Backend

Update landing page links to point to your backend server:

In `docs/index.html`:
```html
<!-- Update these URLs to your backend server -->
<a href="https://your-backend-domain.com/backend/pages/login.php" class="btn">Login</a>
<a href="https://your-backend-domain.com/backend/pages/register.php" class="btn">Register</a>
```

## SSL/HTTPS Setup

### On GitHub Pages
- ✅ Automatically provided with HTTPS

### On Backend Server
- Use Let's Encrypt (free)
- Use your hosting provider's SSL certificate

**Example with Let's Encrypt (Certbot)**:
```bash
sudo certbot certonly --apache -d your-domain.com
```

## Email Configuration

Configure PHPMailer in `backend/includes/config.php`:

```php
// Gmail Example
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-16-char-app-password';
$mail->SMTPSecure = 'tls';
```

Generate an app-specific password at: https://myaccount.google.com/apppasswords

## Monitoring & Maintenance

### Logs
- PHP error logs: Check your hosting provider's logs
- Database logs: Located in MySQL data directory

### Updates
- Keep PHP updated
- Update dependencies: `composer update`
- Regular database backups

### Performance Optimization
1. Enable gzip compression in `.htaccess`
2. Set up CDN for static assets
3. Use database query caching
4. Implement lazy loading for images

## Troubleshooting

**Issue**: 404 errors on GitHub Pages  
**Solution**: Ensure `/docs/index.html` exists

**Issue**: Database connection error  
**Solution**: Check credentials in `config.php`, verify database is running

**Issue**: Email not sending  
**Solution**: Verify SMTP credentials, check firewall/port 587

**Issue**: File upload errors  
**Solution**: Check permissions on `backend/assets/uploads/`

---

For additional help, check the main [README.md](../README.md)
