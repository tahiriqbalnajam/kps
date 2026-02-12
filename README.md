# IDL School New

A comprehensive School Management System built with **Laravel 10** and **Vue 3**. This application handles all aspects of school administration including student management, fees, exams, attendance, and communication.

## 🚀 Features

### 🎓 Academic Management
- **Student Management**: Complete student profiles, admission handling, and bulk import/export.
- **Class & Section**: Manage classes, sections, and subject allocations.
- **Syllabus Tracking**: Track syllabus completion, chapters, and topics.
- **Timetable**: Dynamic timetable management for classes and teachers.

### 📝 Examination & Results
- **Exam Management**: Schedule exams, manage tests, and record marks.
- **Results & Reports**: Generate award lists, student report cards, and subject-wise performance analysis.
- **Tests**: Create and manage class tests (quizzes).

### 💰 Finance & Fee Management
- **Fee Collection**: Generate fee vouchers, track payments, and manage pending fees.
- **Accounting**: Basic income and expense tracking.
- **Payroll**: Teacher salary calculation and management.

### 📅 Attendance
- **Student Attendance**: Daily marking, monthly reports, and absentee tracking.
- **Teacher Attendance**: Track staff attendance and integrate with payroll.
- **Biometric/Online**: Support for online attendance checks.

### 📢 Communication
- **Notifications**: Integrated SMS and Push notifications using OneSignal.
- **Complaints**: System for parents/students to lodge complaints.
- **Mobile API**: Secure API endpoints for external mobile application integration.

### 👥 User Management
- **Role-Based Access Control (RBAC)**: Fine-grained permissions for Admins, Teachers, Parents, and Accountants using `spatie/laravel-permission`.

## 🛠 Tech Stack

- **Backend**: Laravel 10, Laravel Sanctum (Auth)
- **Frontend**: Vue 3, Element Plus (UI), Pinia (State Management), Vite
- **Database**: MySQL
- **Dependencies**: 
  - `spatie/laravel-permission`
  - `maatwebsite/excel`
  - `laravel-notification-channels/onesignal`

## 📦 Installation

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository_url>
   cd idlschoolnew
   ```

2. **Install Backend Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   ```
   *Update `.env` with your database credentials and other settings.*

5. **Generate App Key**
   ```bash
   php artisan key:generate
   ```

6. **Database Migration & Seeding**
   ```bash
   php artisan migrate --seed
   ```

7. **Build Frontend Assets**
   ```bash
   npm run build
   # or for development
   npm run watch
   ```

8. **Run Application**
   ```bash
   php artisan serve
   ```
   Access the app at `http://localhost:8000`.

## 📱 Mobile App API
The system exposes a secure API for mobile devices under `/api/v1/`.
- Requires `API_KEY` middleware.
- Supports Authentication, Attendance, and Student Data retrieval.

## 📄 License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
