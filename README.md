# Aetherium Hardware - Next-Generation E-commerce Platform

A professional, futuristic e-commerce website for a hardware shop, built with HTML5, CSS3, JavaScript (Swiper.js), and PHP backend.

## 🚀 Features

- **Futuristic Design**: Dark theme with neon accents and smooth animations
- **Hero Section**: Swiper.js carousel with auto-play and navigation controls
- **Responsive Layout**: Mobile-first design that works on all devices
- **Product Management**: Full product catalog with search and filtering
- **User Authentication**: Secure login and registration system
- **User Profiles**: Complete profile management with order history
- **Shopping Cart**: Client-side cart management with localStorage
- **Secure Backend**: PHP backend with password hashing and JWT tokens
- **Database Integration**: MySQL/MariaDB for persistent data storage

## 📋 Project Structure

```
aetherium_hardware_shop/
├── html/                    # HTML pages
│   ├── index.html          # Homepage with hero section
│   ├── shop.html           # Product listing page
│   ├── login.html          # User login page
│   ├── register.html       # User registration page
│   └── profile.html        # User profile page
├── css/
│   └── style.css           # Main stylesheet with futuristic design
├── js/
│   ├── main.js             # Main JavaScript functionality
│   └── swiper-init.js      # Swiper.js initialization
├── php/
│   ├── config.php          # Configuration and helper functions
│   ├── database.php        # Database connection and queries
│   ├── auth.php            # Authentication API endpoints
│   ├── products.php        # Products API endpoints
│   └── schema.sql          # Database schema
├── assets/
│   ├── images/             # Product images
│   └── icons/              # Icon files
└── README.md               # This file
```

## 🛠️ Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3+
- Web server (Apache, Nginx, or PHP built-in server)
- Modern web browser

### Step 1: Database Setup

1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Import the database schema:
   ```sql
   SOURCE php/schema.sql;
   ```
3. Or manually create the database and tables:
   ```bash
   mysql -u root -p < php/schema.sql
   ```

### Step 2: Configure PHP Backend

1. Edit `php/config.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'aetherium_hardware');
   ```

2. Update security settings (especially in production):
   ```php
   define('JWT_SECRET', 'your-secure-secret-key');
   define('APP_ENV', 'production');
   define('APP_DEBUG', false);
   ```

### Step 3: Create Required Directories

```bash
mkdir -p logs assets/uploads
chmod 755 logs assets/uploads
```

### Step 4: Start the Web Server

**Using PHP Built-in Server:**
```bash
php -S localhost:8000
```

**Using Apache:**
- Copy files to your Apache document root (usually `/var/www/html`)
- Ensure `.htaccess` is enabled for URL rewriting

**Using Nginx:**
- Configure Nginx to serve the files
- Point to the project root directory

### Step 5: Access the Application

Open your browser and navigate to:
```
http://localhost:8000/html/index.html
```

## 📖 Usage Guide

### User Registration

1. Navigate to the **Register** page
2. Enter your full name, email, and password
3. Password must be at least 8 characters
4. Click "Create Account"
5. You'll be redirected to login

### User Login

1. Go to the **Login** page
2. Enter your email and password
3. Click "Sign In"
4. You'll be logged in and can access your profile

### Shopping

1. Browse products on the **Shop** page
2. Use filters to narrow down by category or price
3. Click "Add to Cart" to add items
4. Cart count updates in the header
5. View cart items in localStorage (client-side)

### User Profile

1. Click the user icon in the header
2. View and edit your profile information
3. Check your order history
4. Manage saved addresses
5. Update password and notification preferences

## 🔐 Security Features

- **Password Hashing**: Bcrypt algorithm with cost factor 12
- **JWT Tokens**: Secure token-based authentication
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Prepared statements with parameterized queries
- **CORS Protection**: Configurable allowed origins
- **Session Management**: Automatic session timeout
- **Rate Limiting**: Login attempt throttling with lockout

## 🎨 Customization

### Change Theme Colors

Edit CSS variables in `css/style.css`:
```css
:root {
    --primary-color: #00d4ff;      /* Cyan */
    --secondary-color: #ff006e;    /* Pink */
    --accent-color: #00ff88;       /* Green */
    --dark-bg: #0a0e27;            /* Dark background */
    /* ... more variables */
}
```

### Modify Product Data

1. Use the Products API to add/edit products:
   ```php
   POST /php/products.php?action=create
   ```

2. Or directly insert into the database:
   ```sql
   INSERT INTO products (name, description, price, category, stock_quantity)
   VALUES ('Product Name', 'Description', 999.99, 'Category', 50);
   ```

### Add More Pages

1. Create new HTML file in `html/` directory
2. Include the navigation header
3. Link from existing pages
4. Add styling to `css/style.css`

## 📡 API Endpoints

### Authentication Endpoints

```
POST /php/auth.php?action=register
POST /php/auth.php?action=login
POST /php/auth.php?action=logout
GET  /php/auth.php?action=me
POST /php/auth.php?action=update-profile
POST /php/auth.php?action=change-password
```

### Products Endpoints

```
GET  /php/products.php?action=list&page=1&limit=12
GET  /php/products.php?action=get&id=1
GET  /php/products.php?action=search&q=processor
GET  /php/products.php?action=category&name=Processors
GET  /php/products.php?action=featured&limit=4
GET  /php/products.php?action=categories
POST /php/products.php?action=create
PUT  /php/products.php?action=update&id=1
DELETE /php/products.php?action=delete&id=1
```

## 🧪 Testing

### Test User Credentials

```
Email: john@example.com
Password: password (default from schema)
```

### Test Admin Credentials

```
Email: admin@aetherium.com
Password: password (default from schema)
```

## 📱 Browser Compatibility

- Chrome/Chromium 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🚀 Performance Optimization

- **Lazy Loading**: Images load on demand
- **CSS Optimization**: Minified and organized
- **JavaScript**: Vanilla JS (no heavy dependencies except Swiper.js)
- **Caching**: Browser caching for static assets
- **Database Indexes**: Optimized queries with proper indexing

## 🐛 Troubleshooting

### Database Connection Error

- Check database credentials in `config.php`
- Ensure MySQL is running
- Verify database exists: `SHOW DATABASES;`

### Login Not Working

- Check password hashing in database
- Verify email exists in users table
- Check browser console for errors

### Swiper.js Not Loading

- Verify CDN link is accessible
- Check browser console for 404 errors
- Ensure `swiper-init.js` is loaded after Swiper library

### Styling Issues

- Clear browser cache (Ctrl+Shift+Delete)
- Check CSS file path is correct
- Verify all CSS variables are defined

## 📝 Development Notes

### Adding New Features

1. Update database schema in `schema.sql`
2. Create query methods in `database.php`
3. Create API endpoints in appropriate PHP file
4. Add frontend HTML/CSS/JS
5. Test thoroughly

### Code Style

- PHP: PSR-12 coding standard
- JavaScript: ES6+ with comments
- CSS: BEM methodology for class names
- HTML: Semantic HTML5

## 📄 License

This project is provided as-is for educational and commercial use.

## 👨‍💻 Support & Contribution

For issues, questions, or improvements:
1. Check existing documentation
2. Review error logs in `logs/` directory
3. Test with sample data provided in schema

## 🎯 Future Enhancements

- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Email notifications
- [ ] Product reviews and ratings
- [ ] Wishlist functionality
- [ ] Order tracking
- [ ] Admin dashboard
- [ ] Analytics and reporting
- [ ] Multi-language support
- [ ] Dark/Light theme toggle
- [ ] Mobile app

## 📞 Contact

For support or inquiries about Aetherium Hardware:
- Email: support@aetherium-hardware.com
- Website: www.aetherium-hardware.com

---

**Version**: 1.0.0  
**Last Updated**: November 2024  
**Built with**: HTML5, CSS3, JavaScript, PHP, MySQL
