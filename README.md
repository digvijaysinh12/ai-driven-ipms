<h1 align="center">🤖 AI Driven Intern Performance Management System (IPMS)</h1>

<p align="center">
  <b>Role-Based Authentication System with HR Approval Workflow</b><br>
  Built with Laravel & Laravel Breeze
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel">
  <img src="https://img.shields.io/badge/PHP-8%2B-blue?style=for-the-badge&logo=php">
  <img src="https://img.shields.io/badge/Database-MySQL-orange?style=for-the-badge&logo=mysql">
  <img src="https://img.shields.io/badge/Authentication-Laravel%20Breeze-green?style=for-the-badge">
</p>

---

## 📌 Project Overview

The **AI Driven Intern Performance Management System (IPMS)** is a web-based application designed to manage intern performance using secure role-based authentication and HR approval workflow.

The system supports three user roles:

- 👨‍💼 **HR (Admin)**
- 👨‍🏫 **Mentor (Team Lead)**
- 👨‍🎓 **Intern**

> 🔐 Every new user must be approved by HR before gaining access to the system.

---

## 🔐 Authentication Module

Implemented using **Laravel Breeze** with Role-Based Access Control (RBAC).

### ✅ Key Features

- Role-based authentication
- HR approval system
- Status-based login restriction
- Middleware route protection
- Secure password hashing

---

## 🛠️ Technology Stack

| Technology | Usage |
|------------|--------|
| Laravel | Backend Framework |
| Laravel Breeze | Authentication |
| MySQL | Database |
| Blade | Frontend Templating |
| PHP 8+ | Programming Language |

---

## 📂 Database Structure

### 🗂 Roles Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary Key |
| name | string (unique) | hr / mentor / intern |
| timestamps | timestamp | Created & Updated time |

### Seeded Roles

---

## 👤 Users Table (Modified)

| Column  | Type        | Description |
|----------|------------|------------|
| role_id  | bigint (FK) | References roles table |
| status   | enum        | pending / approved / rejected |
| default  | pending     | HR approval required |

### 📌 Migration Command

```bash
php artisan make:migration add_role_id_and_status_to_users_table --table=users
php artisan migrate


- hr
- mentor
- intern

```bash
php artisan db:seed

# 🚀 Installation Guide

Follow the steps below to set up the project locally on your system.

---

## 📥 1️⃣ Clone Repository

```bash
git clone <repository-url>
cd project-folder

## 📦 2️⃣ Install Dependencies
Install PHP and Node dependencies:
```bash
composer install
npm install
npm run dev

## 🔐 3️⃣ Install Laravel Breeze (Authentication)
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate

## ⚙️ 4️⃣ Configure Environment
Create .env file if not exists:
cp .env.example .env
php artisan key:generate
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=

## 🗄 5️⃣ Run Database Migration
php artisan migrate
If roles seeder exists:
php artisan db:seed

## ▶️ 6️⃣ Run Development Server
php artisan serve

## 🌐 Access Application
Open your browser and visit:
http://127.0.0.1:8000

