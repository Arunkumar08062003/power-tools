** Power Tools E-Commerce Web Application (PHP)**
This project is a complete e-commerce platform developed using PHP and MySQL, designed for selling power tools online. It includes all essential e-commerce features such as user authentication, product catalog, shopping cart, order tracking, invoice generation, and more.

**🌟 Key Features**
User Registration and Login (Session-based)

Product Listing with images and categories

Add to Cart and Buy Now functionality

Checkout Page with payment simulation

Order Tracking and Shipping Details

Invoice Generation using jsPDF

Admin Panel for product and order management (if included)

**🛠️ Technologies Used**
PHP (Core PHP)

MySQL (Database)

HTML/CSS/JavaScript

Bootstrap (for responsive UI)

jsPDF (Invoice generation)

**📁 Folder Structure**

power-tools-php/
├── admin/              # Admin dashboard pages
├── cart/               # Cart functionality
├── config/             # Database connection settings
├── images/             # Product image files
├── includes/           # Common UI components (header, footer, etc.)
├── invoices/           # Generated invoices
├── js/                 # JavaScript files (e.g., jsPDF)
├── order/              # Order processing and tracking
├── pages/              # Product and detail pages
├── signup-login/       # User login and signup
├── index.php           # Main homepage
└── README.md           # Project documentation
**🖥️ How to Run the Project Locally**
Clone or Download the Project

Place the folder in your XAMPP htdocs directory (or equivalent for other servers).

Start XAMPP

Enable Apache and MySQL.

Create Database

Open phpMyAdmin and create a new database (e.g., power_tools_db).

Import the SQL file (if included) to set up tables.

Configure Database Connection

Go to config/db.php and update the database credentials:

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "power_tools_db";
Run the Application

**Open your browser and go to:**

http://localhost/power-tools-php/
🔐 Admin Login (if implemented)
URL: http://localhost/power-tools-php/admin/

Username: admin@example.com

Password: admin123

(Update credentials based on your actual database)

**📌 Future Enhancements**
Real payment integration (Stripe/PayPal)

Responsive design improvements

Product reviews and ratings

Email confirmations for orders

