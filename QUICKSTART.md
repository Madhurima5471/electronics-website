# Aetherium Hardware - Quick Start Guide

Get up and running with Aetherium Hardware in 5 minutes!

## 🚀 Quick Setup

### 1. Database Setup (2 minutes)

```bash
# Connect to MySQL
mysql -u root -p

# Create database and import schema
mysql> SOURCE /path/to/php/schema.sql;

# Or use command line
mysql -u root -p < php/schema.sql
```

### 2. Configure PHP (1 minute)

Edit `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'aetherium_hardware');
```

### 3. Start Server (1 minute)

```bash
# Using PHP built-in server
php -S localhost:8000

# Or use Apache/Nginx
# Point to project root directory
```

### 4. Access Application (1 minute)

```
http://localhost:8000/html/index.html
```

## 🔑 Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@aetherium.com | password |
| User | john@example.com | password |

## 📁 Key Files to Know

| File | Purpose |
|------|---------|
| `html/index.html` | Homepage with Swiper hero |
| `css/style.css` | All styling (2000+ lines) |
| `js/main.js` | Cart & form handling |
| `js/swiper-init.js` | Hero carousel setup |
| `php/config.php` | Database & app config |
| `php/auth.php` | Login/register API |
| `php/products.php` | Product API |

## 🎨 Customize Theme

Change colors in `css/style.css` (lines 8-20):
```css
:root {
    --primary-color: #00d4ff;      /* Change cyan */
    --secondary-color: #ff006e;    /* Change pink */
    --accent-color: #00ff88;       /* Change green */
    /* ... */
}
```

## 📝 Common Tasks

### Add a Product

```php
// Using API
POST http://localhost:8000/php/products.php?action=create
Content-Type: application/json

{
    "name": "New Product",
    "description": "Product description",
    "price": 999.99,
    "category": "Processors",
    "stock": 50
}
```

### Test Login

1. Go to `http://localhost:8000/html/login.html`
2. Enter: `john@example.com` / `password`
3. Check browser console for token

### View Cart

Open browser console and run:
```javascript
console.log(JSON.parse(localStorage.getItem('cart')));
```

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection error | Check credentials in `config.php` |
| 404 on PHP files | Ensure server is running in project root |
| Swiper not working | Check CDN link in `html/index.html` |
| Styling looks wrong | Clear cache: Ctrl+Shift+Delete |
| Login fails | Check MySQL is running and schema imported |

## 📚 Next Steps

1. **Explore the code**: Check `html/index.html` to see structure
2. **Customize design**: Edit CSS variables in `style.css`
3. **Add products**: Use the Products API
4. **Create admin panel**: Build dashboard using existing PHP classes
5. **Deploy**: Use your hosting provider

## 🌐 File Structure

```
aetherium_hardware_shop/
├── html/           ← Start here (index.html)
├── css/            ← Styling (style.css)
├── js/             ← Functionality (main.js)
├── php/            ← Backend (config.php, auth.php)
├── assets/         ← Images & uploads
└── logs/           ← Error & activity logs
```

## 💡 Pro Tips

- **Swiper.js**: Already configured with auto-play, pagination, and navigation
- **Cart**: Uses localStorage, no backend required for basic functionality
- **Responsive**: Mobile-first design, works on all screen sizes
- **Security**: Passwords hashed with Bcrypt, JWT tokens for auth
- **Performance**: Lazy loading, optimized CSS, minimal dependencies

## 🎯 What's Included

✅ 5 HTML pages (index, shop, login, register, profile)  
✅ Professional CSS (2000+ lines with animations)  
✅ JavaScript cart system  
✅ Swiper.js hero carousel  
✅ PHP backend with auth  
✅ MySQL database schema  
✅ Product management API  
✅ User management system  
✅ Complete documentation  

## 📞 Need Help?

1. Check `README.md` for detailed documentation
2. Review error logs in `logs/` directory
3. Check browser console for JavaScript errors
4. Verify database connection in `config.php`

---

**Ready to go!** 🚀

Start with the homepage: `http://localhost:8000/html/index.html`
