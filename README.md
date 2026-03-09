# TodoApp  Setup
 [link-video](https://streamable.com/ld5gfs)
## Estructura de solucion
![alt text](image.png)
## Services
- **Web**: PHP 8.3 with Apache (Port 8080)
- **Database**: MySQL 8.0 (Port 3306)
- **phpMyAdmin**: Database management (Port 8081)
- **App Mobile** : Ionic
## Ejecutar 

1. Build and start all services:
```bash
docker-compose up --build -d
```

2. View running containers:
```bash
docker-compose ps
```

3. View logs:
```bash
docker-compose logs web
docker-compose logs db
```

## Access Points
- **Main App**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

## Database Credentials
- **Host**: db
- **Database**: todoapp
- **Username**: todouser
- **Password**: todopass
- **Root Password**: rootpassword

