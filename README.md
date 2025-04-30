# Laravel Blog API with Supabase Integration

This project is a backend RESTful API built with **Laravel** that supports blog management with **Supabase file uploads**, **JWT-based authentication**, **role-based access control**, and **modular route structure**. It's designed to be connected with any frontend (e.g., **React**, **SvelteKit**, **Vue**, **Angular**) or mobile platform.

---

## 🚀 Features

- 📝 Blog CRUD with local or cloud (Supabase) image upload.
- 🔐 Authentication via JWT (JSON Web Tokens).
- 👥 Role-based access control (Admin / User).
  - Admins can create, update, and delete blogs.
  - Users can comment on blogs.
- 🧪 Full-featured test suite.
- ☁️ Optional Supabase file handling.
- 📁 Clean route structure:
  - `/auth`
  - `/blogs`
  - `/comments`
  - `/tags`

---

## 🧪 Tests

The system has been tested using Laravel's built-in testing framework. Tests include:
- Blog creation (with and without Supabase).
- Auth flow (login, registration).
- Role restriction validation.
- Comment functionality.

To run tests:

```bash
php artisan test
```

---

## ⚙️ Environment Variables

Besides the default Laravel `.env` configuration, the following environment variables are required for Supabase integration:

```env
SUPABASE_URL=
SUPABASE_API_KEY=
SUPABASE_BUCKET=
```

---

## 🖼 Blog Creation Options

There are **two ways** to store blog images:

### 1. Supabase Cloud Upload (Default Enabled)
- Stores images in a Supabase bucket.
- Saves image URL and Supabase path to the database.

### 2. Local Storage (Alternative)
- Stores images in the local filesystem (`public/storage`).
- To use local storage:
  - Comment out the Supabase-related code in the `store` and `destroy` blog methods.
  - Remove Supabase columns from the migration files (`image_path_supabase`, `image_path_supabase_url`).
  - Run migration refresh if needed.

> **Note:** Choose one method based on your deployment preferences.

---

## 🔐 Authentication and Roles

- Users must log in to access protected routes.
- Authentication is handled using **JWT** tokens.
- Role system:
  - Admins: full blog CRUD access.
  - Regular users: can only comment on blog posts.

---

## 🛠 Technologies Used

- **Laravel 12**
- **MySQL**
- **JWT (Laravel Sanctum / tymon/jwt-auth)**
- **Supabase** (optional for cloud file storage)
- **PHPUnit** (testing)

---

## 🧩 Modular Route Structure

Routes are organized for maintainability:

```
routes/
├── api.php
├── auth.php
├── blogs.php
├── tags.php
└── comments.php
```

---

## 📦 Installation

```bash
git clone https://github.com/appletonmind/laravel-supabase-blog-api-with-jwt-role-auth.git
cd laravel-supabase-blog-api-with-jwt-role-auth
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Add Supabase variables if using cloud upload.

---

## 🔒 License

This project is **not open source**. You may **not use, modify, or distribute this code** without **prior written consent from the author**.

See [LICENSE](./LICENSE) for details.

---

## 📬 Contact

If you'd like to request permission to use this code, please reach out via email: `your-email@example.com`.
