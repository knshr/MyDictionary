# MyDictionary

A modern Laravel application built with Vue.js, Inertia.js, and TailwindCSS for searching English words and managing personal favorites with automated maintenance and internationalization.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.3) with SOLID Design Principles
- **Frontend**: Vue.js 3 with Composition API
- **Styling**: TailwindCSS
- **Authentication**: Laravel Sanctum with OTP
- **Database**: MySQL 8.0
- **State Management**: Pinia
- **Internationalization**: Vue I18n (English, Tagalog, Japanese)
- **Code Formatting**: Prettier
- **Containerization**: Docker & Docker Compose
- **Task Scheduling**: Laravel Scheduler with Supervisor

## Features

### Core Features

- ✅ **SOLID Architecture**: Repository Pattern, Service Layer, Request Validation, Resource Transformers
- ✅ **Laravel 12** with latest features
- ✅ **Vue.js 3 SPA** with Inertia.js
- ✅ **TailwindCSS** for modern styling
- ✅ **Laravel Sanctum** authentication with OTP verification
- ✅ **Pinia** state management
- ✅ **Complete Internationalization** (English, Tagalog, Japanese)
- ✅ **Two Layouts**: Auth Layout & Dashboard Layout
- ✅ **MySQL database** support
- ✅ **Docker configuration** with Supervisor
- ✅ **User registration and login** with email verification
- ✅ **Responsive design**

### Advanced Features

- ✅ **Word Dictionary Search** with multiple APIs
- ✅ **Favorites System** with notes and CRUD operations
- ✅ **Automated Cleanup** - Scheduled task to remove old favorites
- ✅ **Settings Management** - Configurable cleanup intervals
- ✅ **Code Quality** - Prettier auto-formatting on build
- ✅ **Comprehensive Logging** - All operations logged
- ✅ **Error Handling** - Graceful error management
- ✅ **Toast Notifications** - Global notification system

## SOLID Architecture Implementation

### Backend Structure:

```
app/
├── Console/
│   └── Commands/        # Artisan Commands (CleanupOldFavorites)
├── Http/
│   ├── Controllers/     # Controllers (thin layer)
│   │   └── Api/         # API Controllers
│   ├── Requests/        # Form Request Validation
│   ├── Resources/       # API Resource Transformers
│   └── Middleware/      # Custom Middleware
├── Services/            # Business Logic Layer
├── Repositories/        # Data Access Layer
├── Models/              # Eloquent Models
└── Observers/           # Model Observers
```

### Frontend Structure:

```
resources/js/
├── Pages/
│   ├── Auth/           # Authentication pages (AuthLayout)
│   ├── Dashboard/      # Dashboard pages (DashboardLayout)
│   └── Settings.vue    # Settings page
├── stores/             # Pinia stores
├── locales/            # i18n translations (en, tl, ja)
├── components/         # Reusable components
└── app.js             # Main application entry
```

## Prerequisites

- Docker and Docker Compose
- Node.js (for local development)
- Composer (for local development)

## Quick Start with Docker

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd laravel-vue-app
   ```

2. **Configure environment**

   ```bash
   # Copy the environment file
   cp .env.example .env

   # Update the .env file with your database credentials
   # Make sure these match the docker-compose.yml values:
   APP_NAME="MyDictionary"
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=laravel_user
   DB_PASSWORD=your_mysql_password
   ```

3. **Start the application**

   ```bash
   docker-compose up -d
   ```

4. **Install dependencies and setup the application**

   ```bash
   # Install PHP dependencies
   docker-compose exec app composer install

   # Generate application key
   docker-compose exec app php artisan key:generate

   # Run database migrations
   docker-compose exec app php artisan migrate

   # Seed default settings
   docker-compose exec app php artisan db:seed --class=SettingsSeeder

   # Install Node.js dependencies
   docker-compose exec app npm install

   # Build assets (includes Prettier formatting)
   docker-compose exec app npm run build
   ```

5. **Access the application**
   - Frontend: http://localhost:8000
   - Database: localhost:3306

## Local Development Setup

1. **Install PHP dependencies**

   ```bash
   composer install
   ```

2. **Install Node.js dependencies**

   ```bash
   npm install
   ```

3. **Configure environment**

   ```bash
   cp .env.example .env
   # Update database settings for local MySQL
   ```

4. **Generate application key**

   ```bash
   php artisan key:generate
   ```

5. **Run database migrations and seeders**

   ```bash
   php artisan migrate
   php artisan db:seed --class=SettingsSeeder
   ```

6. **Start development servers**

   ```bash
   # Terminal 1: Start Laravel development server
   php artisan serve

   # Terminal 2: Start Vite development server
   npm run dev
   ```

## Available Routes

### Web Routes

- `GET /` - Home page (Dashboard)
- `GET /login` - Login page (Auth)
- `POST /login` - Login action
- `GET /register` - Register page (Auth)
- `POST /register` - Register action
- `POST /logout` - Logout action
- `GET /dictionary` - Dictionary search page
- `GET /favorites` - Favorites management page
- `GET /settings` - Settings configuration page

### API Routes

- `GET /api/auth/me` - Get current user
- `POST /api/auth/logout` - Logout user
- `GET /api/dictionary/search/{word}` - Search word definitions
- `GET /api/favorites` - Get user favorites
- `POST /api/favorites` - Add new favorite
- `PUT /api/favorites/{id}` - Update favorite
- `DELETE /api/favorites/{id}` - Delete favorite
- `GET /api/settings/cleanup` - Get cleanup settings
- `PUT /api/settings/cleanup` - Update cleanup settings

## Layouts

### AuthLayout

- Used for: Login, Register pages
- Features: Language switcher, centered design, consistent styling
- Languages: English, Tagalog, Japanese

### DashboardLayout

- Used for: All authenticated pages
- Features: Navigation menu, user menu, language switcher, toast notifications
- Navigation: Dashboard, Dictionary, Favorites, Settings

## Internationalization

The application supports three languages with complete coverage:

- **English (en)** - Default language
- **Tagalog (tl)** - Filipino language
- **Japanese (ja)** - Japanese language

### i18n Coverage

- ✅ All user-facing text uses `$t()` function
- ✅ Dashboard content (welcome messages, quick actions, system status)
- ✅ Navigation items and app branding
- ✅ Authentication forms and messages
- ✅ Dictionary search and results
- ✅ Favorites management (notes, actions, sorting)
- ✅ Settings page (cleanup configuration)
- ✅ Error messages and loading states
- ✅ Toast notifications

Language switching is available in both layouts and persists in localStorage.

## Automated Cleanup System

### Features

- **Scheduled Task**: Runs daily via Laravel Scheduler
- **Configurable Retention**: Adjustable cleanup interval (1-365 days)
- **Enable/Disable**: Toggle cleanup functionality
- **Comprehensive Logging**: All operations logged with details
- **Error Handling**: Graceful error management with fallbacks

### Configuration

- **Default**: 30 days retention
- **Settings Page**: Web interface to adjust settings
- **API Endpoints**: Programmatic access to settings
- **Command Line**: Manual execution with override options

### Usage

```bash
# Run cleanup with default settings
php artisan favorites:cleanup

# Run cleanup with custom retention period
php artisan favorites:cleanup --days=7

# Check cleanup status in logs
tail -f storage/logs/laravel.log
```

## Settings Management

### Cleanup Settings

- **Retention Period**: Configure how long to keep favorites (days)
- **Enable/Disable**: Toggle automatic cleanup
- **Real-time Updates**: Changes applied immediately
- **Validation**: Input validation with min/max constraints

### Settings Storage

- **Database-driven**: Settings stored in `settings` table
- **Key-value pairs**: Flexible configuration system
- **Type casting**: Automatic type conversion (string, integer, boolean, json)
- **Default values**: Fallback values for missing settings

## Code Quality

### Prettier Integration

- **Auto-formatting**: Runs on `npm run build`
- **Manual formatting**: `npm run format`
- **Format checking**: `npm run format:check`
- **Configuration**: `.prettierrc` with Vue.js optimized settings
- **Ignored files**: `.prettierignore` excludes build artifacts

### Code Standards

- **Consistent formatting**: All JS/Vue files follow Prettier standards
- **Single quotes**: Used throughout the codebase
- **Trailing commas**: ES5 compatible trailing commas
- **Line length**: 80 character limit
- **Vue formatting**: Special handling for Vue template indentation

## SOLID Principles Implementation

### Single Responsibility Principle (SRP)

- Controllers handle HTTP requests only
- Services contain business logic
- Repositories manage data access
- Models represent data structure

### Open/Closed Principle (OCP)

- Extensible service layer
- Plugin-based architecture
- Configurable settings system

### Liskov Substitution Principle (LSP)

- Interface-based repositories
- Consistent API contracts
- Polymorphic service implementations

### Interface Segregation Principle (ISP)

- Focused service interfaces
- Specific repository contracts
- Minimal API dependencies

### Dependency Inversion Principle (DIP)

- Dependency injection
- Interface-based dependencies
- Inversion of control

## Database Schema

### Core Tables

- `users` - User accounts and authentication
- `favorites` - User's favorite words with notes
- `settings` - Application configuration
- `auth_logs` - Authentication activity tracking
- `otp_codes` - One-time password management

### Relationships

- Users have many favorites
- Settings are key-value pairs
- Auth logs track user activity
- OTP codes are temporary

## Docker Configuration

### Services

- **app**: Laravel application with PHP 8.3
- **db**: MySQL 8.0 database
- **nginx**: Web server with optimized configuration
- **supervisor**: Process management for queues and scheduler

### Supervisor Processes

- **laravel-worker**: Queue processing (2 instances)
- **laravel-scheduler**: Scheduled task execution

### Volumes

- Application code mounted for development
- Database persistence
- Log file storage

## Development Commands

### Laravel Commands

```bash
# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run cleanup manually
php artisan favorites:cleanup --days=30

# Check scheduled tasks
php artisan schedule:list
```

### Node.js Commands

```bash
# Install dependencies
npm install

# Development server
npm run dev

# Build for production
npm run build

# Format code
npm run format

# Check formatting
npm run format:check
```

### Docker Commands

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f app

# Execute commands
docker-compose exec app php artisan migrate
docker-compose exec app npm run build

# Stop services
docker-compose down
```

## Testing

### Manual Testing

- Authentication flow (login/register/logout)
- Dictionary search functionality
- Favorites management (add/remove/edit notes)
- Settings configuration
- Language switching
- Responsive design

### Automated Testing

- Laravel feature tests for API endpoints
- Vue component testing (recommended)
- Database seeding for test data

## Deployment

### Production Considerations

- Set `APP_ENV=production`
- Configure proper database credentials
- Set up SSL certificates
- Configure queue workers and scheduler
- Set up monitoring and logging
- Optimize asset compilation

### Environment Variables

```bash
APP_NAME="MyDictionary"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run `npm run format` to format code
5. Test your changes
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support and questions:

- Create an issue on GitHub
- Check the documentation
- Review the code comments
