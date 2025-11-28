# CRM Contacts – Laravel Application

This repository contains a Laravel-based Contacts CRM application with support for custom fields, AJAX operations, and file uploads.
Follow the steps below to clone, install, and run the project locally.

---

## 🛠️ Prerequisites

Ensure your system includes the following:

* PHP 8.x (version required by the project)
* Composer
* Node.js + npm/yarn
* Git
* MySQL/MariaDB (or another supported DB engine)
* Optional: GitHub CLI (`gh`)

**Ubuntu Quick Setup**

```bash
sudo apt update
sudo apt install -y git curl unzip mysql-server
sudo apt install -y php php-cli php-mysql php-xml php-mbstring php-zip php-gd php-curl php-intl
```

---

## 📥 Clone the Repository

Using **HTTPS** (your repo URL):

```bash
git clone https://github.com/SiddharthRathod/crm_contacts.git
cd crm_contacts
```

Using **SSH** (if you set up SSH keys):

```bash
git clone git@github.com:SiddharthRathod/crm_contacts.git
cd crm_contacts
```

---

## ⚙️ Install & Setup

### 1. Create your `.env` file

```bash
cp .env.example .env
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
# or
npm ci
```

### 4. Generate app key

```bash
php artisan key:generate
```

---

## 🗄️ Database Setup

### 1. Create a database (MySQL example):

```sql
CREATE DATABASE crm_contacts;
CREATE USER 'crmuser'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON crm_contacts.* TO 'crmuser'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_contacts
DB_USERNAME=crmuser
DB_PASSWORD=password
```

### 3. Run migrations:

```bash
php artisan migrate
```
---

## 📂 Storage Link & Permissions

### 1. Create storage symlink

```bash
php artisan storage:link
```

### 2. Set permissions (Linux)

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🚀 Run the Application

Start Laravel dev server:

```bash
php artisan serve
```

Visit the app:
👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🛠️ Troubleshooting

* **Migration errors**
  Verify DB credentials in `.env` and ensure the DB is running.

* **Permission issues**
  Ensure `storage/` and `bootstrap/cache/` are writable.

* **Composer errors**
  Make sure your PHP version and extensions match requirements.

* **Config not updating**

```bash
php artisan config:clear
php artisan cache:clear
```