WINNERHC HR OPS - CPANEL + PHP + MYSQL

1. cPanel > MySQL Databases:
   Create a database and database user, then assign ALL PRIVILEGES.

2. Edit api/config.php and replace:
   YOUR_DATABASE_NAME
   YOUR_DATABASE_USER
   YOUR_DATABASE_PASSWORD

3. phpMyAdmin > select your database > Import > database.sql

4. Upload all files/folders to public_html/hr/
   If using Git deployment, edit .cpanel.yml and replace YOUR_CPANEL_USERNAME.

5. Open https://yourdomain.com/hr/setup_admin.php
   This creates:
   Username: admin
   Password: Admin@1234

6. DELETE setup_admin.php immediately after success.

7. Open https://yourdomain.com/hr/
   Login and change the Admin password in Settings > My Account.

IMPORTANT
- Supabase is removed.
- PHP sessions + MySQL are used.
- Passwords use password_hash/password_verify.
- Write requests use CSRF protection.
- api/config.php contains DB credentials. Do NOT commit real credentials to a public GitHub repository.
- Shared data refreshes every 5 seconds.
