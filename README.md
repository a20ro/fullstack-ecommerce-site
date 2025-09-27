# TechStore Pro - Commercial Technology Store

A modern, full-featured e-commerce website for selling technology devices with both user and admin interfaces. Built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## 🏪 About TechStore Pro

TechStore Pro is a comprehensive commercial website designed for selling technology devices including smartphones, laptops, tablets, and other electronic gadgets. The platform features a modern, responsive design with separate interfaces for customers and administrators.

## ✨ Key Features

### 🛒 User Interface
- **Modern Homepage**: Responsive product grid with 3-column layout
- **Product Catalog**: Browse and search technology devices
- **Shopping Cart**: Add/remove items with quantity controls
- **User Authentication**: Secure login and registration system
- **Order Management**: View order history and track purchases
- **User Profile**: Manage personal information and preferences
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices

### 👨‍💼 Admin Interface
- **Dashboard**: Overview of sales, orders, and inventory
- **Product Management**: Add, edit, and delete products
- **Order Management**: Process and track customer orders
- **User Management**: Manage customer accounts
- **Inventory Control**: Monitor stock levels and product availability
- **Secure Access**: Protected admin area with authentication

### 🛠️ Technical Features
- **Database Integration**: MySQL database for data persistence
- **Session Management**: Secure user sessions
- **Form Validation**: Client and server-side validation
- **Responsive Grid Layout**: CSS Grid for optimal product display
- **Modern UI/UX**: Clean, professional design with smooth animations
- **Cross-browser Compatibility**: Works on all modern browsers

## 📁 Project Structure

```
commercial_website/
├── index.php                    # Main entry point
├── login.php                    # User authentication page
├── config/
│   └── database.php            # Database configuration
├── assets/                      # Static assets
│   ├── css/                    # Stylesheets
│   │   ├── main.css           # Main application styles
│   │   ├── sidebar.css        # Sidebar navigation styles
│   │   ├── footer.css         # Footer styles
│   │   ├── home.css           # Homepage specific styles
│   │   ├── item.css           # Product detail page styles
│   │   ├── cart.css           # Shopping cart styles
│   │   └── profile.css        # User profile styles
│   ├── js/                     # JavaScript files
│   │   ├── framework.js       # Main application logic
│   │   ├── sidebar.js         # Sidebar functionality
│   │   ├── footer.js          # Footer functionality
│   │   └── home.js            # Homepage interactions
│   └── images/                 # Product and UI images
│       ├── iphone15pro.jpg
│       ├── galaxys24.jpg
│       ├── dellxps13.jpg
│       ├── macbook.jpg
│       ├── ipad.png
│       └── profile-logo.svg
├── pages/                       # User interface pages
│   ├── home.php               # Homepage with product grid
│   ├── products.php           # Product catalog
│   ├── item.php               # Individual product details
│   ├── cart.php               # Shopping cart
│   ├── checkout.php           # Checkout process
│   ├── profile.php            # User profile management
│   ├── order_history.php      # Order tracking
│   ├── about.php              # About page
│   ├── contact.php            # Contact information
│   ├── auth_handler.php       # Authentication logic
│   ├── cart_handler.php       # Cart management
│   ├── checkout_handler.php   # Order processing
│   ├── order_details_handler.php # Order details
│   └── reorder_handler.php    # Reorder functionality
├── admin/                       # Admin interface
│   ├── index.php              # Admin login
│   ├── dashboard.php          # Admin dashboard
│   ├── products.php           # Product management
│   ├── orders.php             # Order management
│   ├── users.php              # User management
│   ├── logout.php             # Admin logout
│   ├── includes/
│   │   └── admin_auth.php     # Admin authentication
│   └── assets/
│       ├── css/
│       │   └── admin.css      # Admin interface styles
│       └── js/
│           └── admin.js       # Admin functionality
├── sidebar/                     # Navigation component
│   └── sidebar.html           # Sidebar template
├── footer/                      # Footer component
│   └── footer.html            # Footer template
└── README.md                   # This file
```

## 🚀 Getting Started

### Prerequisites

- **XAMPP** (Apache, MySQL, PHP) or similar local server environment
- **Modern Web Browser** (Chrome, Firefox, Safari, Edge)
- **Text Editor** (VS Code, Sublime Text, etc.)

### Installation

1. **Download and Install XAMPP**
   - Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Setup the Project**
   ```bash
   # Copy the project to XAMPP htdocs directory
   cp -r commercial_website /Applications/XAMPP/xamppfiles/htdocs/
   # or on Windows:
   # xcopy commercial_website C:\xampp\htdocs\commercial_website /E /I
   ```

3. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `commercial_website`
   - Import the database schema (if available) or create tables manually

4. **Configure Database Connection**
   - Edit `config/database.php` with your database credentials:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "commercial_website";
   ```

5. **Start the Application**
   - Start Apache and MySQL in XAMPP Control Panel
   - Navigate to `http://localhost/commercial_website/`

## 🗄️ Database Schema

The application uses the following main tables:

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    location VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Products Table
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Orders Table
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 🎨 Design Features

### User Interface
- **Responsive Grid Layout**: Products displayed in 3-column grid on desktop, 2-column on tablet, 1-column on mobile
- **Modern Color Scheme**: Professional blue and gray color palette
- **Smooth Animations**: Hover effects and transitions for better user experience
- **Intuitive Navigation**: Sidebar navigation with clear categories
- **Mobile-First Design**: Optimized for all device sizes

### Admin Interface
- **Clean Dashboard**: Overview of key metrics and recent activity
- **Easy Product Management**: Add, edit, and delete products with image upload
- **Order Processing**: Track and update order status
- **User Management**: View and manage customer accounts
- **Responsive Admin Panel**: Works on desktop and tablet devices

## 🔧 Configuration

### Environment Setup
- **PHP Version**: 7.4 or higher recommended
- **MySQL Version**: 5.7 or higher
- **Apache**: Latest version with mod_rewrite enabled

### File Permissions
Ensure proper file permissions for uploads and logs:
```bash
chmod 755 assets/images/
chmod 644 config/database.php
```

## 🛡️ Security Features

- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Session Management**: Secure session handling with proper validation
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **Admin Authentication**: Separate admin login system
- **CSRF Protection**: Form tokens for sensitive operations

## 📱 Responsive Design

The website is fully responsive with breakpoints for:
- **Desktop**: >1024px (3-column product grid)
- **Tablet**: 768px-1024px (2-column product grid)
- **Mobile**: <768px (2-column or 1-column layout)
- **Small Mobile**: <480px (1-column layout)

## 🚀 Deployment

### Production Deployment

1. **Server Requirements**
   - PHP 7.4+
   - MySQL 5.7+
   - Apache/Nginx with mod_rewrite
   - SSL Certificate (recommended)

2. **Deployment Steps**
   ```bash
   # Upload files to server
   rsync -av commercial_website/ user@server:/var/www/html/
   
   # Set proper permissions
   chown -R www-data:www-data /var/www/html/commercial_website
   chmod -R 755 /var/www/html/commercial_website
   ```

3. **Database Migration**
   - Export local database
   - Import to production server
   - Update database credentials in `config/database.php`

4. **Apache Configuration**
   Add to `.htaccess` for clean URLs:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

## 🧪 Testing

### Manual Testing Checklist
- [ ] User registration and login
- [ ] Product browsing and search
- [ ] Shopping cart functionality
- [ ] Checkout process
- [ ] Order history
- [ ] Admin product management
- [ ] Admin order processing
- [ ] Responsive design on all devices
- [ ] Cross-browser compatibility

## 🔄 Future Enhancements

- **Payment Integration**: PayPal, Stripe, or other payment gateways
- **Email Notifications**: Order confirmations and status updates
- **Product Reviews**: Customer review system
- **Wishlist**: Save favorite products
- **Advanced Search**: Filter by price, brand, specifications
- **Inventory Management**: Low stock alerts and automatic reordering
- **Analytics**: Sales reports and customer insights
- **Multi-language Support**: Internationalization
- **API Integration**: RESTful API for mobile apps

## 📞 Support

For technical support or questions:
- **Email**: support@techstorepro.com
- **Documentation**: Check this README and inline code comments
- **Issues**: Report bugs or feature requests through the issue tracker

## 📄 License

This project is proprietary software developed for commercial use. All rights reserved.

## 🤝 Contributing

This is a commercial project. For internal development:
1. Create a feature branch
2. Make your changes
3. Test thoroughly
4. Submit for code review
5. Deploy to staging environment

---

**TechStore Pro** - Your premier destination for technology devices 🚀

*Built with modern web technologies and best practices for optimal performance and user experience.*