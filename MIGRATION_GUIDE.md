# Food-Saver: MySQL to Supabase PostgreSQL Migration Guide

## Overview
This guide will help you migrate your Food-Saver application from MySQL to Supabase PostgreSQL database.

## Prerequisites
- Completed `.env` setup with Supabase connection string
- Access to both your old MySQL database and Supabase account
- PHP CLI access (for running migration scripts)

## Migration Process

### Step 1: Create PostgreSQL Schema in Supabase

1. **Log in to Supabase Dashboard** at https://app.supabase.com
2. Navigate to your project
3. Go to **SQL Editor** section
4. Click **New Query**
5. Copy the entire contents of `database/schema-postgresql.sql`
6. Paste into the SQL Editor
7. Click **Run**

**Expected Result:** All tables and triggers will be created. You should see success messages for each statement.

### Step 2: Update .env File with Actual Password

1. Open `.env` file in your project root
2. Replace `[YOUR-PASSWORD]` in DATABASE_URL with your actual Supabase password

**Example:**
```
DATABASE_URL=postgresql://postgres.zwivsdstcxxebzortafg:actualPasswordHere@aws-1-ap-northeast-2.pooler.supabase.com:6543/postgres
```

**Where to find password:**
- Supabase Dashboard → Project Settings → Database → Connection string (reveal the password)

### Step 3: Test Supabase Connection

Run the connection test script:

```bash
php database/test-connection.php
```

**Expected Output:**
```
✓ Successfully connected to Supabase PostgreSQL!
✓ Found X tables:
  • admins
  • restaurants
  • ngos
  • users
  • ... (other tables)
✓ All tests passed!
```

**Troubleshooting:**
- If connection fails, verify DATABASE_URL credentials are correct
- Check that Supabase firewall allows your IP address
- For pooler connections, ensure you're using port 6543

### Step 4: Migrate Data from MySQL to PostgreSQL

Run the migration script:

```bash
php database/migrate-mysql-to-postgresql.php
```

**What it does:**
1. Connects to your old MySQL database
2. Connects to Supabase PostgreSQL
3. Transfers all data table by table
4. Verifies data integrity
5. Provides a summary report

**Expected Output:**
```
=== Food-Saver Database Migration: MySQL to PostgreSQL ===

Connecting to old MySQL database...
✓ Connected to MySQL

Connecting to Supabase PostgreSQL database...
✓ Connected to PostgreSQL

Migrating table: admins
  └─ Migrated X records

Migrating table: restaurants
  └─ Migrated X records

... (continues for all tables)

✓ admins: X records
✓ restaurants: X records
... (verification results)
```

**Troubleshooting:**
- **"MySQL connection failed"** → Verify old database credentials in the script
- **"PostgreSQL connection failed"** → Verify DATABASE_URL is correct
- **"MISMATCH"** → Some records failed to migrate. Check the error logs above the verification section.

### Step 5: Verify Data Integrity

After migration, verify your data:

1. **Check record counts** - Compare MySQL and PostgreSQL row counts
2. **Test critical functions** - Log in to your app, check food listings, etc.
3. **Review application logs** - Check for any SQL errors

Quick SQL checks in Supabase SQL Editor:

```sql
-- Check table sizes
SELECT table_name, COUNT(*) as row_count 
FROM information_schema.tables t
JOIN (SELECT table_name FROM information_schema.tables WHERE table_schema = 'public') st ON t.table_name = st.table_name
GROUP BY table_name;

-- Verify admin user exists
SELECT * FROM admins LIMIT 5;

-- Count total records per table
SELECT 'admins' as table_name, COUNT(*) as count FROM admins
UNION ALL
SELECT 'restaurants', COUNT(*) FROM restaurants
UNION ALL
SELECT 'ngos', COUNT(*) FROM ngos
UNION ALL
SELECT 'users', COUNT(*) FROM users;
```

## Key Differences: MySQL → PostgreSQL

### Syntax Changes Already Handled

| Feature | MySQL | PostgreSQL | Status |
|---------|-------|-----------|--------|
| Auto-increment | `AUTO_INCREMENT` | `SERIAL` | ✓ Converted |
| Enums | `ENUM('a','b')` | `CREATE TYPE`, then use in table | ✓ Converted |
| Timestamps | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` | `TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP` | ✓ Converted |
| Auto-update | `ON UPDATE CURRENT_TIMESTAMP` | PostgreSQL triggers | ✓ Converted |
| Boolean | `BOOLEAN` | `BOOLEAN` | ✓ Same |

### PHP Code Adjustments

**Your config.php has been updated to support both:**

```php
if ($driver === 'postgresql' || $driver === 'pgsql') {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
} else {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
}
```

This means your code works with both databases!

## Potential Issues & Solutions

### Issue: PDO PostgreSQL Driver Not Installed

**Error:** `could not find driver`

**Solution:**
1. On Windows with XAMPP:
   - Edit `php.ini`
   - Uncomment: `extension=pdo_pgsql`
   - Restart Apache

2. On Linux/Mac:
   - Install: `sudo apt-get install php-pgsql` (Ubuntu/Debian)
   - Or: `brew install php@8.0 --with-pgsql` (Mac)

### Issue: Connection Timeout

**Error:** `could not translate host name`

**Solution:**
- Check Supabase firewall: Supabase Dashboard → Project Settings → Network
- Add your IP to allowed connections
- Or allow all IPs: `0.0.0.0/0` (less secure)

### Issue: SSL Connection Required

**Error:** `SSL operation failed`

**Solution:**
Add SSL mode to DSN:
```php
$dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
```

### Issue: Data Not Migrated - Foreign Key Violations

**Cause:** Records with foreign key constraints might fail if referenced records don't exist

**Solution:**
- Check the migration script output for errors
- Manually insert missing parent records first
- Re-run the migration for child tables

### Issue: ENUM Values Not Matching

**Cause:** MySQL ENUM values don't match PostgreSQL enum types

**Solution:**
- Check `database/migrate-mysql-to-postgresql.php` for type conversion logic
- Add custom conversion if needed in the migration script

## Rollback Plan

If something goes wrong:

1. **Revert to MySQL** (temporary):
   - Update `.env` to point to old MySQL database
   - Flip `DB_DRIVER` or remove the PostgreSQL type checking in `config.php`

2. **Restore Supabase from Backup**:
   - Supabase Dashboard → Backups → Restore Point
   - Or recreate tables using `schema-postgresql.sql`

3. **Re-run Migration**:
   - Fix the issue
   - Run migration script again

## Performance Tips

### Optimize PostgreSQL Connection Pool

In Supabase Dashboard → Project Settings:
- Connection pooling: Enable
- Pool mode: Transaction (for web apps)
- Pool size: 10-20

### Add Indexes for Common Queries

Already included in `schema-postgresql.sql`:
```sql
CREATE INDEX idx_food_status ON food_listings(status);
CREATE INDEX idx_food_restaurant ON food_listings(restaurant_id);
CREATE INDEX idx_donations_user ON donations(user_id);
```

### Monitor Performance

In Supabase Dashboard → Stats:
- Monitor query performance
- Check connection pool usage
- Review slow queries

## Next Steps

1. ✓ Schema created
2. ✓ Data migrated
3. ✓ Connection tested
4. Deploy updated code to production
5. Monitor logs for any SQL errors
6. Decommission old MySQL database (after 30 days of testing)

## Support Resources

- **Supabase Docs**: https://supabase.com/docs
- **PostgreSQL to MySQL Migration**: https://www.postgresql.org/docs/current/
- **Connection String Format**: https://www.postgresql.org/docs/current/libpq-connect.html
- **Project README**: See project root `README.md`

## Important Security Notes

⚠️ **After Migration**:
1. Rotate your Supabase password
2. Update any other services using the old MySQL credentials
3. Set up automated backups in Supabase
4. Enable row-level security (RLS) policies for sensitive data
5. Monitor access logs

For more information on database security, see: https://supabase.com/docs/guides/auth
