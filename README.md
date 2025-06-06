# 🏥 Hospital Management System

A simple and functional **Hospital Management System** developed using **PHP** and **MySQL**. This system allows admins to manage patients, doctors, appointments, billing, and medical records efficiently.

---

## 🚀 Features

- 🩺 Add, Edit, Delete Patients
- 👨‍⚕️ Doctor Registration and Management
- 📅 Appointment Scheduling
- 💊 Medical History and Records
- 💵 Billing and Payment Management
- 🔐 Admin Authentication (Login)
- 📊 Dashboard Overview

---

## 🛠️ Technologies Used

- **Frontend**: HTML, CSS, Bootstrap (optional)
- **Backend**: PHP
- **Database**: MySQL
- **Others**: JavaScript, jQuery (optional)

---

## 📷 Screenshots

> Add screenshots of your UI here (Login Page, Dashboard, Patient List, etc.)

---

## 🔧 Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/hospital-management-system.git
Import the Database

Create a new database in phpMyAdmin

Import the provided .sql file from the project folder

Configure Database Connection

Open config.php or wherever your DB connection is defined

Set your DB credentials:

php
Copy
Edit
$conn = mysqli_connect("localhost", "root", "", "your_db_name");
Run the Project

Start Apache and MySQL using XAMPP or similar

Navigate to http://localhost/hospital-management-system/ in your browser

📁 Folder Structure
pgsql
Copy
Edit
hospital-management-system/
├── config.php
├── index.php
├── dashboard.php
├── patients/
│   ├── add.php
│   ├── edit.php
│   └── list.php
├── doctors/
├── appointments/
├── billing/
└── assets/
🙌 Contribution
Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

📄 License
This project is open-source and free to use under the MIT License.
