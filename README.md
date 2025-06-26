# oshtow-backend

oshtow-backend connects travelers with people who need to send packages. In this app, travelers can accept parcels to carry with them on their trip, and anyone who has something to send can find a traveler heading to their desired destination. It's a smart way to make use of extra luggage space and help others deliver their items faster and more affordably.

## Table of Contents

-   [Features](#features)
-   [Project Structure & DDD](#project-structure--ddd)
-   [How to Run](#how-to-run)
-   [Contributing](#contributing)
-   [License](#license)

---

## Features

-   Travelers can accept and deliver packages for others.
-   Senders can find travelers going to their desired destination.
-   Secure, efficient, and affordable delivery system.
-   Modular, scalable backend using Domain-Driven Design (DDD).

---

## Project Structure & DDD

This project is architected using **Domain-Driven Design (DDD)** principles, which means the codebase is organized around the core business domains and their logic. Here's how the structure is laid out:

```
src/
├── Domain/
│   ├── Review/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   │   ├── ReviewRepository.php
│   │   │   └── Contracts/
│   │   │       └── IReviewRepository.php
│   ├── User/
│   ├── Project/
│   ├── Claim/
│   ├── Wallet/
│   ├── Payment/
│   └── ... (other subdomains)
│
├── Application/
│   ├── Api/
│   │   ├── Review/
│   │   ├── User/
│   │   ├── Project/
│   │   └── ... (other APIs)
│   └── Application.php
│
├── Core/
│   ├── Http/
│   ├── Providers/
│   ├── Exceptions/
│   └── Console/
│
└── Support/
```

### DDD Layers Explained

-   **Domain Layer (`src/Domain/`)**:  
    Contains the heart of the business logic. Each subdomain (e.g., Review, User, Project) has its own folder, with:

    -   `Models/`: Eloquent models representing domain entities.
    -   `Repositories/`: Data access logic, often split into `Contracts/` (interfaces) and concrete implementations.

-   **Application Layer (`src/Application/`)**:  
    Coordinates application activities. The `Api/` directory contains controllers, requests, and resources for each subdomain, handling HTTP requests and responses.

-   **Core Layer (`src/Core/`)**:  
    Contains shared infrastructure, such as HTTP handling, service providers, exceptions, and console commands.

-   **Support Layer (`src/Support/`)**:  
    (If used) Contains helpers, utilities, or cross-cutting concerns.

### Example: Review Subdomain

-   `Domain/Review/Models/Review.php`: The Review entity.
-   `Domain/Review/Repositories/Contracts/IReviewRepository.php`: The repository interface for reviews.
-   `Domain/Review/Repositories/ReviewRepository.php`: The concrete implementation of the review repository.
-   `Application/Api/Review/Controllers/ReviewController.php`: Handles HTTP requests for reviews.
-   `Application/Api/Review/Requests/ReviewRequest.php`: Handles validation for review-related requests.

---

## How to Run

1. **Clone the repository:**

    ```bash
    git clone https://github.com/yourusername/oshtow-backend.git
    cd oshtow-backend
    ```

2. **Install dependencies:**

    ```bash
    composer install
    ```

3. **Set up your environment:**

    - Copy `.env.example` to `.env` and configure your database and other settings.

4. **Run migrations:**

    ```bash
    php artisan migrate
    ```

5. **Start the development server:**
    ```bash
    php artisan serve
    ```

---

## Contributing

Contributions are welcome! Please open issues or submit pull requests for any improvements or bug fixes.

---

## License

This project is open-source and available under the [MIT License](LICENSE).
