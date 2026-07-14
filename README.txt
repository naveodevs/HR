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

6. DELETE setup_admin.php immediately after success.

7. Open https://yourdomain.com/hr/
   Login and change the Admin password in Settings > My Account.
