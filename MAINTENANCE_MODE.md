# 🔧 Maintenance Mode Documentation

## Overview
The maintenance mode feature allows administrators to temporarily disable user access to the application while performing system maintenance, updates, or repairs. When enabled, all users will see a professional maintenance page instead of the normal application interface.

## Features

### ✨ **Professional Maintenance Page**
- Modern, responsive design with animations
- Real-time progress indicators
- Estimated completion time
- Contact information
- Auto-refresh every 30 seconds
- SEO-friendly with proper HTTP status codes (503 Service Unavailable)

### 🎛️ **Multiple Control Methods**
1. **Command Line Interface (CLI)**
2. **Web Interface (Superadmin only)**
3. **Configuration File (Manual)**

### 🔒 **Security Features**
- Only superadmin users can toggle maintenance mode via web
- Proper HTTP headers to prevent caching
- Graceful fallback if maintenance controller fails

## Usage Methods

### 1. Command Line Interface (Recommended)

**Check Current Status:**
```bash
php maintenance.php
```

**Enable Maintenance Mode:**
```bash
php maintenance.php on
```

**Disable Maintenance Mode:**
```bash
php maintenance.php off
```

**Alternative Commands:**
```bash
# Enable
php maintenance.php enable
php maintenance.php true

# Disable  
php maintenance.php disable
php maintenance.php false
```

### 2. Web Interface (Superadmin Only)

1. Login as superadmin user
2. Navigate to **Database Diagnostic** page (`/database-diagnostic`)
3. Find the **Maintenance Mode Control** section
4. Click the appropriate button:
   - **Enable Maintenance** (red button) - Activates maintenance mode
   - **Enable Application** (green button) - Disables maintenance mode
5. Confirm the action when prompted

### 3. Manual Configuration

Edit the `config/config.php` file:

```php
'app' => [
    'name' => 'Sistem Manajemen Kos',
    'version' => '2.3.0',
    'url' => 'http://localhost/app_kost',
    'maintenance' => true  // Set to true to enable, false to disable
],
```

## Technical Implementation

### Configuration
Maintenance mode is controlled by the `app.maintenance` setting in `config/config.php`:

```php
'maintenance' => false // false = normal operation, true = maintenance mode
```

### Application Flow
1. **Request Received** → `Application::run()`
2. **Maintenance Check** → `Config::isMaintenanceMode()`
3. **If Enabled** → `Application::handleMaintenanceMode()`
4. **Display Page** → `Maintenance::index()` or fallback

### HTTP Headers
When maintenance mode is active, the application sends proper HTTP headers:
- `HTTP 503 Service Unavailable`
- `Cache-Control: no-cache, no-store, must-revalidate`
- `Pragma: no-cache`
- `Expires: 0`
- `Retry-After: 3600` (suggests retry in 1 hour)

### Fallback Mechanism
If the maintenance controller fails, the application displays a basic fallback maintenance page to ensure users are always informed.

## Files Structure

```
app_kost/
├── maintenance.php                           # CLI utility
├── config/config.php                         # Configuration file
├── app/
│   ├── controllers/Maintenance.php           # Maintenance controller
│   ├── controllers/DatabaseDiagnostic.php    # Web toggle functionality
│   ├── views/maintenance/index.php           # Professional maintenance page
│   └── core/
│       ├── Application.php                   # Main maintenance logic
│       └── Config.php                        # Configuration methods
└── MAINTENANCE_MODE.md                       # This documentation
```

## Customization

### Maintenance Page Content
Edit `app/views/maintenance/index.php` to customize:
- Messages and descriptions
- Estimated completion time
- Contact information
- Social media links
- Progress indicators
- Styling and animations

### CLI Utility
Modify `maintenance.php` to add:
- Additional commands
- Custom validation
- Integration with deployment scripts
- Logging functionality

## Best Practices

### ✅ **Do:**
- Always notify users before enabling maintenance mode
- Provide accurate estimated completion times
- Test maintenance mode in staging environment first
- Use CLI method for automated deployments
- Monitor application logs during maintenance

### ❌ **Don't:**
- Enable maintenance mode without notice during business hours
- Leave maintenance mode active longer than necessary
- Modify the config file directly in production without backup
- Forget to disable maintenance mode after completion

## Troubleshooting

### **Maintenance Mode Stuck Enabled**
If you can't disable maintenance mode via web interface:

1. **Use CLI method:**
   ```bash
   php maintenance.php off
   ```

2. **Manual config edit:**
   Set `'maintenance' => false` in `config/config.php`

3. **Check file permissions:**
   ```bash
   chmod 644 config/config.php
   ```

### **CLI Script Not Working**
```bash
# Check PHP CLI availability
php -v

# Check file permissions
chmod +x maintenance.php

# Run with full path
/usr/bin/php /path/to/app_kost/maintenance.php
```

### **Maintenance Page Not Showing**
1. Check config file syntax: `php -l config/config.php`
2. Verify maintenance view exists: `app/views/maintenance/index.php`
3. Check web server error logs
4. Ensure Application.php has maintenance logic

## Integration with Deployment

### **Example Deploy Script:**
```bash
#!/bin/bash
echo "🔧 Enabling maintenance mode..."
php maintenance.php on

echo "📦 Deploying application..."
# Your deployment commands here
git pull origin main
composer install --no-dev
php install/run

echo "✅ Disabling maintenance mode..."
php maintenance.php off

echo "🚀 Deployment complete!"
```

### **Automated Monitoring:**
```bash
# Check maintenance status in cron job
*/5 * * * * cd /path/to/app_kost && php maintenance.php | grep "ENABLED" && echo "Maintenance active" | mail admin@domain.com
```

## Security Considerations

- Maintenance mode configuration requires file write permissions
- Only superadmin users can toggle via web interface
- CLI access requires server shell access
- No sensitive information is displayed on maintenance page
- Proper HTTP status codes help with SEO and monitoring

## Version History

- **v2.3.0** - Initial maintenance mode implementation
- Added CLI utility and web interface
- Professional maintenance page with animations
- Comprehensive error handling and fallback mechanisms