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

- hr
- mentor
- intern

```bash
php artisan db:seed
