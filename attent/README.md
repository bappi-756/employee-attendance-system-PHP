# Employee Attendance System for SSS IT WORLD

A simple web-based attendance system that allows employees to check in and out twice a day, and administrators to manage employees and view attendance records.

## Features

### Admin Panel
- Admin login system
- Create, edit, and delete employees
- View and filter attendance logs
- Dashboard with attendance statistics

### Employee Panel
- Login with ID and password
- Dashboard with profile and attendance summary
- Check-in and check-out functionality (morning and afternoon)
- View personal attendance history

## Technology Stack
- Frontend: HTML5, CSS3, JavaScript, Bootstrap 5
- Backend: PHP (Procedural)
- Database: MySQL

## Installation

1. **Clone the repository or extract the files to your web server directory**

2. **Create the database**
   - Import the `database.sql` file into your MySQL database
   - This will create the required tables and a default admin user

3. **Configure the database connection**
   - Open `config/db_connect.php`
   - Update the database credentials if needed:
     ```php
     $host = "localhost";
     $username = "root";
     $password = "";
     $database = "attendance_system";
     ```

4. **Create uploads directory**
   - Make sure the `uploads` directory exists and is writable
   - This is where employee profile photos will be stored

5. **Access the system**
   - Open your web browser and navigate to the project URL
   - Example: `http://localhost/attendance-system/`

## Default Login Credentials

### Admin
- Username: admin
- Password: admin123

## Directory Structure

```
attendance-system/
├── admin/                  # Admin panel files
│   ├── dashboard.php       # Admin dashboard
│   ├── employees.php       # Employee management
│   ├── add_employee.php    # Add new employee
│   ├── edit_employee.php   # Edit employee
│   ├── attendance.php      # View attendance records
│   └── logout.php          # Admin logout
├── employee/               # Employee panel files
│   ├── dashboard.php       # Employee dashboard
│   ├── attendance.php      # View personal attendance
│   └── logout.php          # Employee logout
├── includes/               # Shared files
│   ├── header.php          # Header template
│   ├── footer.php          # Footer template
│   └── functions.php       # Helper functions
├── config/                 # Configuration files
│   └── db_connect.php      # Database connection
├── uploads/                # Employee photos
├── index.php               # Home page
├── admin_login.php         # Admin login page
├── employee_login.php      # Employee login page
├── database.sql            # Database schema
└── README.md               # This file
```

## Usage

### Admin
1. Login with admin credentials
2. Add employees with their details
3. View and filter attendance records
4. Monitor attendance statistics

### Employee
1. Login with ID and password provided by admin
2. Check-in and check-out for morning and afternoon sessions
3. View personal attendance history

## Security Features
- Input sanitization
- Prepared statements for database queries
- Session-based authentication
- Plain text password storage for easy access

## License
This project is open-source and available for use by SSS IT WORLD. 