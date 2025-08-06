# Spond Manager - Attendance Management System

**Created by Belli Dev**

A comprehensive PHP application for managing Spond events, attendance tracking, and member management with synchronization capabilities.

## Features

✅ **User Authentication & Dashboard**
- Secure login system with Spond credentials integration
- Interactive dashboard with key statistics
- User settings and profile management

✅ **Spond Integration**
- Sync events from Spond API
- Automatic member and attendance synchronization
- Real-time status updates

✅ **Attendance Management**
- Manual attendance tracking and check-in system
- Bulk operations for efficient management
- Status management (Present, Absent, Accepted, Declined)
- Notes and comments for each attendance record

✅ **Comprehensive Overview**
- Member attendance statistics and performance tracking
- Filterable reports by date range and member
- Visual progress indicators and performance badges

✅ **Excel Export**
- Monthly attendance reports export
- Customizable date ranges for reports
- CSV format for easy data analysis

✅ **Modern UI/UX**
- Responsive Bootstrap 5 design
- Dark mode support
- Interactive tooltips and notifications
- Real-time updates and loading states

## Installation Requirements

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2+
- Apache/Nginx web server
- Modern web browser (Chrome, Firefox, Safari, Edge)

### PHP Extensions Required
- PDO MySQL
- JSON
- Session support
- cURL (for Spond API integration)

## Quick Installation Guide

### Step 1: Download and Extract
```bash
# Clone or download the files to your web server directory
# For example: /var/www/html/spond-manager/
```

### Step 2: Database Setup
1. Create a new MySQL database:
```sql
CREATE DATABASE spond_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Create a database user:
```sql
CREATE USER 'spond_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON spond_manager.* TO 'spond_user'@'localhost';
FLUSH PRIVILEGES;
```

3. Update database configuration in `config/database.php`:
```php
$host = 'localhost';
$dbname = 'spond_manager';
$username = 'spond_user';
$password = 'your_secure_password';
```

### Step 3: File Permissions
```bash
# Make sure web server can write to certain directories
chmod 755 -R /path/to/spond-manager/
chmod 777 /path/to/spond-manager/uploads/ (if exists)
```

### Step 4: Access Application
1. Open your web browser
2. Navigate to: `http://your-domain.com/spond-manager/`
3. You'll be redirected to the login page

### Step 5: First Login
**Default Admin Account:**
- Username: `admin`
- Password: `admin123`

**Important:** Change the default password immediately after first login!

## Directory Structure

```
spond-manager/
├── api/
│   └── sync.php              # API endpoints for synchronization
├── assets/
│   ├── css/
│   │   └── style.css         # Custom styles
│   └── js/
│       ├── main.js           # Core JavaScript functionality
│       └── attendance.js     # Attendance management scripts
├── config/
│   └── database.php          # Database configuration and setup
├── includes/
│   ├── functions.php         # Core PHP functions
│   ├── header.php           # Page header template
│   └── sidebar.php          # Navigation sidebar
├── index.php                # Main dashboard
├── login.php                # Login page
├── logout.php               # Logout script
├── attendance.php           # Attendance management
├── overview.php             # Attendance overview and reports
├── events.php               # Event management
└── README.md               # This file
```

## Configuration

### Spond API Integration
1. Log in to the application
2. Go to Settings or use the login form
3. Enter your Spond credentials:
   - Spond username/email
   - Spond password
4. Test the connection using the sync button

### Settings Configuration
- **Auto-sync interval**: Configure automatic synchronization
- **Default status**: Set default attendance status
- **Notification preferences**: Configure email/system notifications
- **Export format**: Choose default export format

## Usage Guide

### Syncing Events from Spond
1. Navigate to Dashboard or Events page
2. Click "Sync Events" button
3. System will fetch latest events and member responses
4. Check sync logs for details

### Managing Attendance
1. Go to Events page
2. Click "Manage Attendance" on any event
3. Use the interface to:
   - Check in members
   - Update attendance status
   - Add notes
   - Perform bulk operations

### Generating Reports
1. Navigate to Overview page
2. Apply filters (date range, member)
3. View statistics and performance metrics
4. Export to Excel using the export button

### Adding Manual Entries
1. In attendance management page
2. Click "Add Manual Entry"
3. Select member and set status
4. Add optional notes

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database user permissions

**Spond Sync Not Working**
- Verify Spond credentials are correct
- Check internet connectivity
- Review sync logs for error details

**Login Issues**
- Ensure sessions are working (check PHP session configuration)
- Clear browser cache and cookies
- Check file permissions

**Export Not Working**
- Ensure PHP has write permissions
- Check browser popup blockers
- Verify Excel/CSV export functionality

### Debug Mode
To enable detailed error reporting, add to the top of `index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Security Considerations

### Recommended Security Measures
1. **Change default password** immediately
2. **Use HTTPS** in production environment
3. **Regular backups** of database and files
4. **Keep PHP updated** to latest stable version
5. **Secure file permissions** - don't allow public write access
6. **Use strong passwords** for all accounts

### Data Privacy
- Spond credentials are encrypted (basic encryption)
- Session data is handled securely
- No sensitive data is logged in plain text

## Support & Development

### Created by Belli Dev
This application was developed with focus on:
- User-friendly interface
- Robust functionality
- Scalable architecture
- Modern web standards

### Customization
The application is built with modularity in mind:
- CSS can be customized in `assets/css/style.css`
- JavaScript functionality in `assets/js/`
- PHP functions in `includes/functions.php`
- Database schema is documented in `config/database.php`

### Contributing
To contribute to this project:
1. Follow existing code style and structure
2. Test thoroughly before submitting changes
3. Document any new features
4. Maintain the "Created by Belli Dev" attribution

## License & Attribution

**Created by Belli Dev**

This software is provided as-is. Please maintain attribution in all copies and derivative works.



## Changelog

### Version 1.0.0
- Initial release
- Complete attendance management system
- Spond API integration
- Excel export functionality
- Responsive web interface
- User authentication system

---

## Support My Work
If you find this application useful and would like to support my development efforts, consider making a donation. Your support helps me continue creating and improving open-source tools for the community.

**Donation Options :**

**Cryptocurrency Donations:**

USDT (TRC-20): **THbrJSW4keFSbWHJGfPataZ9G8sHNCNqPD**

Binance ID (BELLI TOOLS): **495208467**

**Why Donate?**

- Helps cover server and development costs
- Motivates me to create more free tools
- Supports ongoing maintenance and updates
- Funds new feature development
- Every contribution, no matter how small, is greatly appreciated!

---

**Need Help?** 
Check the troubleshooting section above or review the code comments for detailed implementation notes.

**Enjoy using Spond Manager!**

Spond Manager - Created by Belli Dev  
© 2025 Belli Dev. All Rights Reserved.

This software is provided as-is for non-commercial use.  
You are **not allowed** to:

- Remove the original author’s name  
- Redistribute this software commercially  
- Claim it as your own  
- Modify or reuse the code without proper attribution  
- Sell any part of this software without permission

Unauthorized commercial use, resale, or modification without written permission is strictly prohibited.  
Violators will be prosecuted under applicable intellectual property laws.



