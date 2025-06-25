# 🚌 Fleet Management System API

A comprehensive Laravel-based fleet management system with REST API for bus transportation, seat booking, and route management.

## 📋 Table of Contents

- [Features](#-features)
- [System Architecture](#-system-architecture)
- [API Documentation](#-api-documentation)
- [Postman Collection](#-postman-collection)
- [Database Schema](#-database-schema)
- [Installation](#-installation)
- [Testing](#-testing)
- [Admin Dashboard](#-admin-dashboard)

## ✨ Features

- **🔐 Authentication**: JWT-based user authentication with Sanctum
- **🚉 Station Management**: CRUD operations for bus stations
- **🚌 Trip Planning**: Multi-stop route planning with seat availability
- **🎫 Booking System**: Real-time seat booking with overlap detection
- **📊 Admin Dashboard**: Web interface for fleet management
- **🧪 Testing**: Comprehensive test suite with 24+ tests
- **📱 API-First**: Clean, consistent REST API design

## 🏗️ System Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend/     │    │   Laravel API   │    │   Database      │
│   Mobile App    │◄──►│   (Backend)     │◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │ Admin Dashboard │
                    │   (Web UI)      │
                    └─────────────────┘
```

### 🔄 Data Flow Diagram

```
User Request ──► Authentication ──► Route Resolution ──► Controller
                                                            │
Database ◄── Service Layer ◄── Business Logic ◄───────────┘
    │                                │
    └── Response ◄── API Resource ◄──┘
```

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api
```

### 🔐 Authentication Endpoints

#### Register User
```http
POST /auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_admin": false,
      "email_verified_at": null,
      "created_at": "2025-06-25 14:07:08"
    },
    "token": "9|CQCl6FmSRBAg8ghAFyNTwOI5IfVnSNX7EpXqBpn1393bcccc",
    "token_type": "Bearer"
  },
  "message": "User registered successfully."
}
```

#### Login User
```http
POST /auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Get Current User
```http
GET /auth/user
Authorization: Bearer {token}
```

#### Logout User
```http
POST /auth/logout
Authorization: Bearer {token}
```

### 🚉 Station Endpoints

#### Get All Stations
```http
GET /v1/stations
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Cairo",
      "city": null,
      "created_at": "2025-06-25 13:14:36",
      "updated_at": "2025-06-25 13:14:36"
    },
    {
      "id": 2,
      "name": "Giza",
      "city": null,
      "created_at": "2025-06-25 13:14:36",
      "updated_at": "2025-06-25 13:14:36"
    }
  ],
  "message": "Stations retrieved successfully."
}
```

#### Get Stations by City
```http
GET /v1/stations/by-city?city=Cairo
```

#### Get Station by ID
```http
GET /v1/stations/{id}
```

### 🚌 Trip & Availability Endpoints

#### Get Available Seats (Single Route)
```http
GET /v1/trips/available-seats?start_station_id=1&end_station_id=5&date=2025-06-26
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "scheduled_trip_id": 1,
      "trip": {
        "id": 1,
        "name": "Cairo-Asyut Express",
        "origin_station": {
          "id": 1,
          "name": "Cairo"
        },
        "destination_station": {
          "id": 5,
          "name": "Asyut"
        }
      },
      "segment": {
        "from_station": {
          "id": 1,
          "name": "Cairo",
          "city": null
        },
        "to_station": {
          "id": 5,
          "name": "Asyut",
          "city": null
        },
        "departure_time": "2025-06-26 08:00:00",
        "arrival_time": "2025-06-26 14:00:00"
      },
      "bus": {
        "id": 1,
        "name": "Bus A01",
        "license_plate": null,
        "capacity": 12
      },
      "trip_departure_time": "2025-06-26 08:00:00",
      "trip_arrival_time": "2025-06-26 14:00:00",
      "available_seats_count": 10,
      "available_seats": [
        {
          "seat_id": 3,
          "seat_number": "S3"
        },
        {
          "seat_id": 4,
          "seat_number": "S4"
        }
      ]
    }
  ],
  "message": "Available seats retrieved successfully."
}
```

#### Get Available Seats (Multiple Routes)
```http
POST /v1/trips/available-seats-multiple
Content-Type: application/json

{
  "segments": [
    {
      "start_station_id": 1,
      "end_station_id": 2
    },
    {
      "start_station_id": 4,
      "end_station_id": 5
    }
  ],
  "date": "2025-06-26"
}
```

#### Get Scheduled Trips
```http
GET /v1/trips/scheduled?date=2025-06-26
```

### 🎫 Booking Endpoints (Protected)

#### Create Booking
```http
POST /v1/bookings
Authorization: Bearer {token}
Content-Type: application/json

{
  "scheduled_trip_id": 1,
  "seat_id": 3,
  "start_station_id": 1,
  "end_station_id": 5
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "confirmed",
    "booked_at": "2025-06-25 14:01:31",
    "trip": {
      "name": "Cairo-Asyut Express",
      "departure_time": "2025-06-26 14:01:31",
      "arrival_time": "2025-06-26 20:01:31"
    },
    "bus": {
      "name": "Bus A01",
      "license_plate": null
    },
    "seat": {
      "number": "S3"
    },
    "start_station": {
      "id": 1,
      "name": "Cairo",
      "city": null
    },
    "end_station": {
      "id": 5,
      "name": "Asyut",
      "city": null
    }
  },
  "message": "Booking created successfully."
}
```

#### Get My Bookings
```http
GET /v1/bookings
Authorization: Bearer {token}
```

## 📁 Postman Collection Structure

### Import Instructions:
1. Open Postman
2. Click "Import" button
3. Create new collection: "Fleet Management System API"
4. Create the following folder structure:

```
Fleet Management System API
├── 📁 Authentication
│   ├── 📄 Register User
│   │   └── POST {{base_url}}/auth/register
│   ├── 📄 Login User  
│   │   └── POST {{base_url}}/auth/login
│   ├── 📄 Get Current User
│   │   └── GET {{base_url}}/auth/user
│   └── 📄 Logout User
│       └── POST {{base_url}}/auth/logout
│
├── 📁 Stations
│   ├── 📄 Get All Stations
│   │   └── GET {{base_url}}/v1/stations
│   ├── 📄 Get Stations by City
│   │   └── GET {{base_url}}/v1/stations/by-city?city=Cairo
│   └── 📄 Get Station by ID
│       └── GET {{base_url}}/v1/stations/1
│
├── 📁 Trips & Availability
│   ├── 📄 Get Available Seats (Single)
│   │   └── GET {{base_url}}/v1/trips/available-seats
│   ├── 📄 Get Available Seats (Multiple)
│   │   └── GET {{base_url}}/v1/trips/available-seats-multiple
│   └── 📄 Get Scheduled Trips
│       └── GET {{base_url}}/v1/trips/scheduled
│
└── 📁 Bookings (Protected)
    ├── 📄 Create Booking
    │   └── POST {{base_url}}/v1/bookings
    └── 📄 Get My Bookings
        └── GET {{base_url}}/v1/bookings
```

### Environment Variables:
```
base_url: http://localhost:8000/api
auth_token: {{your_token_here}}
```

### Collection Variables:
- Set `Authorization` type to `Bearer Token`
- Use `{{auth_token}}` for protected endpoints

## 🗄️ Database Schema

### 📊 Entity Relationship Diagram

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Users    │    │   Stations  │    │    Buses    │
├─────────────┤    ├─────────────┤    ├─────────────┤
│ id (PK)     │    │ id (PK)     │    │ id (PK)     │
│ name        │    │ name        │    │ name        │
│ email       │    │ city        │    │ capacity    │
│ password    │    │ created_at  │    │ license_plate│
│ is_admin    │    │ updated_at  │    │ created_at  │
│ created_at  │    └─────────────┘    │ updated_at  │
│ updated_at  │                       └─────────────┘
└─────────────┘                              │
       │                                     │
       │    ┌─────────────┐                 │
       │    │   Trips     │                 │
       │    ├─────────────┤                 │
       │    │ id (PK)     │                 │
       │    │ name        │                 │
       │    │ origin_station_id (FK)        │
       │    │ destination_station_id (FK)   │
       │    │ created_at  │                 │
       │    │ updated_at  │                 │
       │    └─────────────┘                 │
       │           │                        │
       │           │                        │
       │    ┌─────────────┐                 │
       │    │ Trip Stops  │                 │
       │    ├─────────────┤                 │
       │    │ id (PK)     │                 │
       │    │ trip_id (FK)│                 │
       │    │ station_id (FK)               │
       │    │ order       │                 │
       │    │ created_at  │                 │
       │    │ updated_at  │                 │
       │    └─────────────┘                 │
       │           │                        │
       │           │                        │
       │    ┌─────────────┐                 │
       │    │Scheduled    │◄────────────────┘
       │    │Trips        │
       │    ├─────────────┤
       │    │ id (PK)     │
       │    │ trip_id (FK)│
       │    │ bus_id (FK) │
       │    │ departure_time
       │    │ arrival_time│
       │    │ status      │
       │    │ created_at  │
       │    │ updated_at  │
       │    └─────────────┘
       │           │
       │           │    ┌─────────────┐
       │           │    │   Seats     │
       │           │    ├─────────────┤
       │           │    │ id (PK)     │
       │           │    │ bus_id (FK) │
       │           │    │ seat_number │
       │           │    │ created_at  │
       │           │    │ updated_at  │
       │           │    └─────────────┘
       │           │           │
       │           │           │
       └───────────┼───────────┼─────────┐
                   │           │         │
                   ▼           ▼         ▼
            ┌─────────────────────────────────┐
            │          Bookings               │
            ├─────────────────────────────────┤
            │ id (PK)                        │
            │ user_id (FK)                   │
            │ scheduled_trip_id (FK)         │
            │ seat_id (FK)                   │
            │ start_station_id (FK)          │
            │ end_station_id (FK)            │
            │ status                         │
            │ created_at                     │
            │ updated_at                     │
            └─────────────────────────────────┘
```

### 🔗 Key Relationships

- **User** → **Bookings** (1:N)
- **Station** → **Trips** (Origin/Destination) (1:N)
- **Trip** → **TripStops** (1:N)
- **Trip** → **ScheduledTrips** (1:N)
- **Bus** → **Seats** (1:N)
- **Bus** → **ScheduledTrips** (1:N)
- **ScheduledTrip** → **Bookings** (1:N)

## 🚀 Installation

### Prerequisites
- PHP 8.1+
- Laravel 11
- MySQL 8.0+
- Docker & Docker Compose
- Composer

### Setup Steps

1. **Clone Repository**
```bash
git clone <repository-url>
cd fleet-management-system
```

2. **Environment Setup**
```bash
cp .env.example .env
# Edit .env with your database credentials
```

3. **Install Dependencies**
```bash
composer install
npm install
```

4. **Start Services (Docker)**
```bash
./vendor/bin/sail up -d
```

5. **Generate Application Key**
```bash
./vendor/bin/sail artisan key:generate
```

6. **Run Migrations & Seeders**
```bash
./vendor/bin/sail artisan migrate --seed
```

7. **Access Application**
- **API**: http://localhost:8000/api
- **Admin**: http://localhost:8000/admin
- **User Dashboard**: http://localhost:8000/dashboard

### Default Credentials
- **Admin**: admin@golyv.com / password
- **User**: user@example.com / password

## 🧪 Testing

Run the comprehensive test suite:

```bash
./vendor/bin/sail artisan test
```

**Test Coverage:**
- ✅ 24 Tests Passing
- ✅ 170 Assertions
- ✅ Unit Tests: SeatAvailabilityService
- ✅ Feature Tests: Authentication, Bookings, Seat Availability
- ✅ API Integration Tests

## 👨‍💼 Admin Dashboard

### Features
- **📊 Dashboard**: System statistics and overview
- **🚌 Trip Management**: Create, edit, delete trips
- **📅 Scheduled Trips**: Manage trip schedules, cancel/delete trips
- **🎫 Booking Management**: View and manage all bookings
- **🚉 Station Management**: CRUD operations for stations

### Admin Actions
- ✅ Cancel scheduled trips (with booking protection)
- ✅ Delete trips (with validation)
- ✅ View booking statistics
- ✅ Manage fleet operations

## 📈 System Statistics

### Available Routes
```
Cairo ──► Giza ──► AlFayyum ──► AlMinya ──► Asyut
```

### Sample Data
- **5 Stations**: Cairo, Giza, AlFayyum, AlMinya, Asyut
- **21+ Scheduled Trips**: Daily schedules
- **2 Buses**: 12-seat capacity each
- **Multi-segment Booking**: Support for partial route bookings
