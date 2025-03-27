# Reminder Service

A simple, user-friendly service that helps businesses keep track of expiring orders and notify customers before and after expiration dates.

## What This Service Does

This service automatically sends friendly reminders to customers when their orders are about to expire or have already expired. It helps businesses maintain customer relationships and encourage renewals.

### Key Features

- **Smart Reminders**: Sends reminders at sensible intervals (1 week, 3 days, and 1 day before expiration)
- **After-Expiration Notices**: Follows up with customers even after orders expire
- **Multi-Language Support**: Sends emails in the customer's preferred language (English, Spanish, French, German)
- **Customizable Templates**: Businesses can customize what the reminder emails say
- **Flexible Configuration**: Easy to change when reminders are sent

## Getting Started

### Prerequisites

- Docker and Docker Compose
- Basic knowledge of command line

### Quick Start

1. Clone this repository
2. Run `docker-compose -f docker-compose.simple.yml up -d`
3. Access the application at http://localhost:8000
4. API endpoints are available at http://localhost:8000/api/

The setup includes:
- PHP-FPM application (running on port 9000)
- Nginx web server (mapped to port 8000)
- MariaDB database (with port 33306 exposed)

### API Endpoints

The service provides several RESTful API endpoints:

- `/api/status` - Check API status
- `/api/reminder-intervals` - Manage reminder interval configurations
- `/api/reminder-configurations` - Manage customer-specific reminder settings
- `/api/orders` - Manage customer orders
- `/api/reminders` - Process and manage individual reminders
- `/api/email-templates` - Manage email templates

## For Developers

### Understanding the Code

This service is built on Laravel and uses a straightforward approach:

1. **Orders**: Represent customer purchases with expiration dates
2. **Reminders**: Scheduled notifications about expiring orders
3. **Configurations**: Control when and how reminders are sent
4. **Templates**: Define what reminder emails look like

### Docker Environment

The Docker setup consists of three containers:
- **app**: PHP-FPM application container
- **webserver**: Nginx web server
- **mariadb**: MariaDB database

You can customize the Docker configuration in `docker-compose.simple.yml`.

### Database Migrations

The database schema includes tables for:
- Reminder configurations
- Reminder intervals
- Orders
- Reminders
- Email templates

To run migrations manually:
```bash
docker exec reminder-service_app_1 php artisan migrate
```

To seed the database with default values:
```bash
docker exec reminder-service_app_1 php artisan db:seed
```

### Reminder Logic

- **Type X orders**: Expire 1 year after application date
- **Type Y orders**: Expire on December 31st of the current year
- When a customer renews early, reminders for the old order stop automatically

### Testing the Service

```bash
# Run all tests
docker exec reminder-service_app_1 php artisan test

# Test just the API endpoints
docker exec reminder-service_app_1 php artisan test --filter=ApiTest

# Test reminder interval configuration
docker exec reminder-service_app_1 php artisan test --filter=ReminderIntervalConfigTest
```

## How to Customize

### Adding New Reminder Intervals

You can add new reminder intervals (like "2 weeks before") through the API:

```bash
curl -X POST http://localhost:8000/api/reminder-intervals \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Two Weeks Before",
    "reminder_type": "pre_expiration",
    "days": 14,
    "is_active": true
  }'
```

### Adding New Languages

The system currently supports:
- English (en)
- Spanish (es)
- French (fr)
- German (de)

To add a new language, you'll need to:
1. Add language code to `config/app.php`
2. Create email templates for the new language

## Troubleshooting

If you encounter issues:

1. Check container status with `docker ps`
2. View logs with `docker logs reminder-service_app_1`
3. Verify database connection in `.env` file
4. Ensure the webserver is properly configured to forward requests to PHP-FPM

## Need Help?

If you have any questions about how to use this service, please contact support@reminderservice.com
