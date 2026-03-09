##  Installation & Setup

### 1. Using Docker (Recommended)
```bash
# Start all services
docker-compose up --build -d

# Verify containers
docker-compose ps
```

### 2. Manual Installation
```bash
cd api
composer install
```

## 📋 API Endpoints

All endpoints return JSON and are prefixed with `/api`:

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/todos` | Get all todos |
| `GET` | `/api/todos/{id}` | Get specific todo |
| `POST` | `/api/todos` | Create new todo |
| `PUT` | `/api/todos/{id}` | Update todo |
| `DELETE` | `/api/todos/{id}` | Delete todo |


## 📝 Environment Configuration

```env
# Database Configuration
DB_HOST=db
DB_NAME=todoapp
DB_USER=todouser
DB_PASS=todopass
DB_PORT=3306

# Application Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080/api

# API Configuration
API_VERSION=v1
API_PREFIX=api

# CORS Configuration
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With

JWT_SECRET=e6e7aef5-550d-4793-be6d-aa98d3d68aba
JWT_TTL=3600
```

