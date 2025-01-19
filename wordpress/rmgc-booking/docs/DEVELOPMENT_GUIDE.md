# RMGC Booking Plugin - Development Guide

## Quick Start
```bash
# Clone repository
git clone https://github.com/wibo88/rmgc-booking-api.git
cd rmgc-booking-api

# Switch to development branch
git checkout version-2.0

# Build plugin
cd wordpress
zip -r rmgc-booking.zip rmgc-booking/
```

## Development Environment Specifications

### Requirements
- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx with mod_rewrite enabled

### Required PHP Extensions
- curl
- json
- mbstring
- mysqli
- xml
- zip

### WordPress Plugin Dependencies
- Advanced Custom Fields Pro (5.0+) for custom fields
- Classic Editor (maintains form compatibility)

### Local Development Setup
1. Install Local WordPress Environment:
   ```bash
   # Using XAMPP/MAMP
   DocumentRoot: /path/to/wordpress
   PHP Version: 7.4+
   MySQL Version: 5.7+
   
   # Using Docker (recommended)
   docker-compose up -d
   ```

2. Configure WordPress:
   ```bash
   # wp-config.php settings
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

3. Install Dependencies:
   ```bash
   # Via WordPress Admin
   Plugins → Add New → Upload Plugin
   
   # Required Plugins
   - Advanced Custom Fields Pro
   - Classic Editor
   ```

## Database Schema

### Core Tables
1. `wp_rmgc_bookings`
   ```sql
   CREATE TABLE wp_rmgc_bookings (
       id bigint(20) NOT NULL AUTO_INCREMENT,
       date date NOT NULL,
       first_name varchar(100) NOT NULL,
       last_name varchar(100) NOT NULL,
       email varchar(100) NOT NULL,
       phone varchar(50),
       state varchar(100),
       country varchar(100),
       club_name varchar(100),
       club_state varchar(100),
       club_country varchar(100),
       handicap int(3) NOT NULL,
       players int(1) NOT NULL,
       time_preferences text NOT NULL,
       status varchar(20) DEFAULT 'pending',
       admin_notes text,
       assigned_time time DEFAULT NULL,
       last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       created_at datetime DEFAULT CURRENT_TIMESTAMP,
       modified_by bigint(20),
       PRIMARY KEY (id),
       KEY idx_status (status),
       KEY idx_date (date),
       KEY idx_email (email)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

2. `wp_rmgc_booking_notes`
   ```sql
   CREATE TABLE wp_rmgc_booking_notes (
       id bigint(20) NOT NULL AUTO_INCREMENT,
       booking_id bigint(20) NOT NULL,
       note text NOT NULL,
       created_at datetime DEFAULT CURRENT_TIMESTAMP,
       created_by bigint(20) NOT NULL,
       PRIMARY KEY (id),
       KEY booking_id (booking_id),
       FOREIGN KEY (booking_id) REFERENCES wp_rmgc_bookings(id) ON DELETE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

### WordPress Options
```sql
-- Plugin Settings
rmgc_api_url
rmgc_api_key
rmgc_notification_email
rmgc_admin_email_subject
rmgc_guest_email_subject
rmgc_email_from_name
rmgc_email_from_address
rmgc_recaptcha_site_key
rmgc_recaptcha_secret_key
rmgc_version

-- Rate Limiting
rmgc_rate_limit_{md5(email)}
```

## API Documentation

### Endpoints (api.php)

1. Booking Creation
   ```javascript
   POST /wp-admin/admin-ajax.php
   action: rmgc_create_booking
   
   // Request Body
   {
     nonce: string,
     booking: {
       date: "YYYY-MM-DD",
       firstName: string,
       lastName: string,
       email: string,
       phone: string,
       state: string,
       country: string,
       clubName: string,
       clubState: string,
       clubCountry: string,
       handicap: number,
       players: number,
       timePreferences: string[],
       recaptchaResponse: string
     }
   }
   
   // Response
   {
     success: boolean,
     data: {
       message: string,
       booking_id?: number
     }
   }
   ```

2. Status Update
   ```javascript
   POST /wp-admin/admin-ajax.php
   action: rmgc_update_booking_status
   
   // Request Body
   {
     nonce: string,
     booking_id: number,
     status: "approved" | "rejected",
     assigned_time?: string,
     note?: string
   }
   ```

### Authentication
- Frontend: WordPress nonce (`rmgc_booking_nonce`)
- Admin: WordPress nonce (`rmgc_admin_nonce`) + `manage_options` capability
- API Keys: Required for external API access

### Rate Limiting
- 10 attempts per hour per email address
- Stored in WordPress transients
- Reset on plugin deactivation

## Testing Environment

### Local Testing Setup
1. PHPUnit Configuration
   ```xml
   <!-- phpunit.xml -->
   <testsuites>
     <testsuite name="RMGC Booking Tests">
       <directory>./tests</directory>
     </testsuite>
   </testsuites>
   ```

2. Test Data
   ```sql
   -- Available in /tests/fixtures/
   test_bookings.sql
   test_notes.sql
   ```

### Browser Compatibility
- Chrome 88+
- Firefox 85+
- Safari 14+
- Edge 88+
- iOS Safari 14+
- Chrome for Android 88+

### Test Coverage
- Unit Tests: 65%
- Integration Tests: 40%
- End-to-End Tests: Manual

## Third-party Integrations

### reCAPTCHA Implementation
```php
// Configuration
define('RMGC_RECAPTCHA_SITE_KEY', get_option('rmgc_recaptcha_site_key'));
define('RMGC_RECAPTCHA_SECRET_KEY', get_option('rmgc_recaptcha_secret_key'));

// Verification URL
const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
```

### Email Service
- WordPress mail function (`wp_mail`)
- SMTP configuration via plugin (recommended: WP Mail SMTP)
- HTML email templates in `/templates/emails/`

### External Services
1. Google reCAPTCHA v2
   - Site Key: Configure in admin
   - Secret Key: Configure in admin
   - Verification endpoint: Google API

2. Email Service Provider
   - SMTP configuration required
   - Supports: SendGrid, Amazon SES, etc.

## Development Tools

### Required
- Git
- Composer
- Node.js & npm
- PHP_CodeSniffer
- WordPress Coding Standards

### Recommended
- Visual Studio Code
  - PHP Intelephense
  - WordPress Snippets
- PHP Debug Bar
- Query Monitor Plugin

## Code Standards
- Follow WordPress Coding Standards
- PHP_CodeSniffer configuration provided
- ESLint configuration for JavaScript
- Stylelint for CSS/SCSS

## Error Handling
- Debug logging enabled in development
- Custom error logging in `/wp-content/rmgc-logs/`
- Stack traces in development only
- User-friendly error messages in production

## Documentation Updates
This guide should be updated with each significant change or feature addition. Commit messages should reference any documentation updates.