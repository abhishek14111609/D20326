# DUOS API Database Setup Guide

## Prerequisites
- XAMPP with MySQL running
- Laravel project already created

## Step 1: Configure Environment
Update your `.env` file with the following MySQL configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=duos_db
DB_USERNAME=root
DB_PASSWORD=
```

## Step 2: Create MySQL Database
1. Start XAMPP MySQL service
2. Open phpMyAdmin or MySQL command line
3. Run the SQL script: `setup_database.sql`

Or use command line:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS duos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Step 3: Run Migrations
Execute the following command to create all tables:
```bash
php artisan migrate
```

## Database Schema Overview

### Tables Created:
1. **users** - User profiles and authentication
2. **tokens** - Authentication tokens and device management
3. **settings** - Application settings
4. **dashboard_items** - Dashboard content items
5. **swipes** - User swipe interactions
6. **chats** - Chat messages between users
7. **gifts** - Virtual gifts sent between users
8. **plans** - Subscription plans
9. **memberships** - User subscriptions
10. **leaderboards** - User rankings
11. **competitions** - Competition details
12. **competition_participants** - Competition participants

### Key Features:
- Soft deletes for users
- Foreign key constraints
- Proper indexing
- JSON fields for flexible data
- Enum fields for controlled values

## Next Steps
1. Create models for each table
2. Set up API routes
3. Implement authentication
4. Create controllers for each entity

## Troubleshooting
- Ensure MySQL is running in XAMPP
- Check database credentials in `.env`
- Verify database exists before running migrations
