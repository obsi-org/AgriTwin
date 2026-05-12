CREATE DATABASE IF NOT EXISTS agritwin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'ceres_db_user'@'%' IDENTIFIED BY 'agritwin_local_dev_password';
ALTER USER 'ceres_db_user'@'%' IDENTIFIED BY 'agritwin_local_dev_password';
GRANT ALL PRIVILEGES ON agritwin_db.* TO 'ceres_db_user'@'%';

CREATE USER IF NOT EXISTS 'ceres_db_user'@'localhost' IDENTIFIED BY 'agritwin_local_dev_password';
ALTER USER 'ceres_db_user'@'localhost' IDENTIFIED BY 'agritwin_local_dev_password';
GRANT ALL PRIVILEGES ON agritwin_db.* TO 'ceres_db_user'@'localhost';

FLUSH PRIVILEGES;
