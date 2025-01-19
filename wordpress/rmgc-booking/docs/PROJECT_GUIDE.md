# RMGC Booking Plugin Development Guide

## Project Overview
WordPress plugin for Royal Melbourne Golf Club's booking system, allowing visitors to book tee times on specific days with time preferences.

## Repository Information
- **Repository**: wibo88/rmgc-booking-api
- **Branch**: version-2.0 (development), main (production)
- **Current Version**: 2.0.0

## Development Environment
```bash
# Repository Structure
~/rmgc-booking-api/
├── wordpress/
│   ├── rmgc-booking/
│   │   ├── includes/
│   │   │   ├── init.php          # Database and script initialization
│   │   │   ├── shortcodes.php    # Form display and HTML/CSS
│   │   │   ├── admin-menu.php    # WordPress admin interface
│   │   │   ├── settings.php      # Plugin settings page
│   │   │   ├── database.php      # Database operations
│   │   │   ├── email.php         # Email functionality
│   │   │   ├── api.php          # API endpoints
│   │   │   └── security.php      # Security functions
│   │   ├── js/
│   │   │   ├── booking.js        # Frontend form handling
│   │   │   └── admin.js         # Admin interface functionality
│   │   ├── templates/
│   │   │   ├── admin/
│   │   │   │   └── booking-list.php  # Admin booking list template
│   │   │   └── emails/
│   │   │       ├── admin-notification.php
│   │   │       ├── guest-confirmation.php
│   │   │       ├── booking-approved.php
│   │   │       └── booking-rejected.php
│   │   └── rmgc-booking.php      # Main plugin file
│   └── plugin-backups/           # Backup storage
```

## Development Workflow

### 1. Setup
```bash
# Clone repository
git clone https://github.com/wibo88/rmgc-booking-api.git
cd rmgc-booking-api

# Ensure on version-2.0 branch
git checkout version-2.0
```

### 2. Making Changes
```bash
# Before changes
cd ~/rmgc-booking-api
git checkout version-2.0
git pull origin version-2.0

# Backup current plugin
cd wordpress
mkdir -p plugin-backups
mv rmgc-booking.zip plugin-backups/rmgc-booking_$(date "+%Y%m%d_%H%M%S").zip

# After changes
cd wordpress
zip -r rmgc-booking.zip rmgc-booking/

# Update WordPress Plugin
1. WordPress admin → Plugins
2. Deactivate RMGC Booking
3. Delete it
4. Upload new zip
5. Activate plugin
```

## Key Features & Status

### Implemented (v2.0.0):
1. Basic Form Functionality
   - Personal Details
   - Golf Details
   - Date Selection
   - Time Preferences
   - Validation

2. Admin Interface
   - Booking Management
   - Status Updates
   - Notes System
   - Time Assignment

3. Email System
   - Admin Notifications
   - Guest Confirmations
   - Approval/Rejection Emails

4. Security
   - Rate Limiting
   - Data Sanitization
   - Input Validation
   - Error Logging

### Pending Development:
1. Email System Enhancements
   - Multiple admin notifications
   - Customizable templates
   - HTML email styling
   - Email preview in admin

2. Form Improvements
   - Loading states
   - Enhanced validation messages
   - Phone number formatting
   - Country selector
   - Club name autocomplete
   - Configurable reCAPTCHA

3. Calendar System
   - Available times tooltip
   - Configurable date restrictions
   - Block out dates
   - Visual time slot display

4. Admin Interface
   - Advanced filtering/sorting
   - CSV export
   - Booking status workflow
   - Search functionality
   - Date range filters

5. Technical Improvements
   - Database indexing
   - Query optimization
   - Caching system
   - Backup procedures
   - Maintenance mode

## Testing Procedures
1. Frontend Testing
   - Form submission
   - Validation
   - reCAPTCHA
   - Date selection
   - Mobile responsiveness

2. Admin Testing
   - Booking management
   - Email notifications
   - Status updates
   - Notes system

3. Email Testing
   - Admin notifications
   - Guest confirmations
   - Approval emails
   - Rejection emails

## Database Structure

### rmgc_bookings Table
```sql
id bigint(20) NOT NULL AUTO_INCREMENT
date date NOT NULL
first_name varchar(100) NOT NULL
last_name varchar(100) NOT NULL
email varchar(100) NOT NULL
phone varchar(50)
state varchar(100)
country varchar(100)
club_name varchar(100)
club_state varchar(100)
club_country varchar(100)
handicap int(3) NOT NULL
players int(1) NOT NULL
time_preferences text NOT NULL
status varchar(20) DEFAULT 'pending'
admin_notes text
assigned_time time DEFAULT NULL
last_modified datetime
created_at datetime
modified_by bigint(20)
```

### rmgc_booking_notes Table
```sql
id bigint(20) NOT NULL AUTO_INCREMENT
booking_id bigint(20) NOT NULL
note text NOT NULL
created_at datetime
created_by bigint(20) NOT NULL
```

## Plugin Settings
Configurable in WordPress admin under RMGC Booking → Settings:
- API Configuration
- Email Settings
- reCAPTCHA Setup
- Date Restrictions
- Time Slot Configuration

## Deployment Notes
1. Always test locally before deploying
2. Create backup of existing plugin
3. Log all changes in version control
4. Update version numbers appropriately
5. Test all functionality after deployment

## Support Contacts
- Pro Shop: (03) 9598 6755
- Email: proshop@royalmelbourne.com.au

## Documentation Updates
This guide should be updated with each significant change or feature addition to maintain accuracy and usefulness for the development team.