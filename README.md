![Homepage Screenshot](images/logo.png)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-blue?logo=php)](https://www.php.net/)  [![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)  ![Repo Size](https://img.shields.io/github/repo-size/Cik0Xploit/waaweewoo-bookstore.svg)  ![Last Commit](https://img.shields.io/github/last-commit/Cik0Xploit/waaweewoo-bookstore.svg)  

A fully functional **PHP/MySQL bookstore** built for learning and portfolio purposes — includes browsing, cart, checkout, and an admin dashboard for book and user management.

---

## 🚀 Overview
**WaaWeeWoo Bookstore** allows users to browse books, manage carts, and place orders.  
Admins can manage inventory, users, and categories securely from a dashboard.  
Developed using **PHP**, **MySQL**, and **Bootstrap 5** on an **Apache** stack.

---

## 🧩 Features

### 👥 User
- Register, login, and manage profiles  
- Browse and search books by keyword or category  
- Add to cart, checkout, and track orders  

### 🛠️ Admin
- Manage books (CRUD + cover uploads)  
- Manage categories, users, and orders  
- View login logs and system activities  

---

## ⚙️ Tech Stack
| Layer | Technology |
|-------|-------------|
| Backend | PHP (mysqli, sessions) |
| Database | MySQL |
| Frontend | HTML5, Bootstrap 5, Font Awesome |
| Server | Apache (XAMPP / LAMP / WAMP) |
| File Uploads | Stored under `/images/` |

---

## 🗂️ Project Structure

      waaweewoo-bookstore/
      ├── index.php
      ├── home.php / books.php / book_details.php / cart.php / checkout.php
      ├── login.php / signup.php / logout.php
      ├── profile.php / search.php / contact.php / aboutUs.php
      ├── css/
      ├── images/
      ├── function/
      │ ├── connectdb.php
      │ └── authenticate.php
      └── admin/
      ├── dashboard.php
      ├── manage_books.php / add_book.php / update_book.php / delete_book.php
      ├── manage_category.php / add_category.php / edit_category.php / delete_category.php
      ├── manage_orders.php / update_order_status.php
      ├── manage_user.php / user_log.php



---

## 🧰 Installation Guide
1. **Clone this repository**
   ```bash
   git clone https://github.com/Cik0Xploit/waaweewoo-bookstore.git
2. **Move to Apache root directory**
   ```swift
   htdocs/WaaWeeWooBookstore/
3. **Create a database**
   ```sql
   CREATE DATABASE book_store;
4. **Import the database schema (below or from docs/schema.sql)**
5. **Configure DB connection**
   ```php
   // /function/connectdb.php
    $conn = mysqli_connect("localhost", "root", "", "book_store");
6. **Run the project**
   ```bash
   http://localhost/WaaWeeWooBookstore/

---

## 🔑 Default Admin Account
**⚠️ Replace MD5 with password_hash() for real deployments.**

    ```sql
    INSERT INTO users (full_name, email, password, role)
    VALUES ('Admin', 'admin@store.test', MD5('admin123'), 'admin');

---
##🛡 Security Recommendations
- Use password_hash() and password_verify() instead of MD5
- Implement prepared statements (avoid inline SQL)
- Validate and sanitize all inputs
- Add CSRF tokens on all POST forms
- Restrict file types and size for uploads

---
##⚠ Known Issues
- viewCart.php reference mismatch — should use cart.php
- Mixed session key usage (fullname vs full_name)
- Add schema.sql for faster DB setup (included above)
- Consider upgrading password handling for production

---
## 🪪 License
This project is licensed under the *MIT License*.

© 2025 Muhammad Irfan (Cik0Xploit)
---
