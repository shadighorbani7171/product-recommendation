# Product Recommendation API

A Laravel-based smart product recommendation API that provides personalized product suggestions using various recommendation strategies.

## Features

- User Authentication (Login/Register)
- Product Management
- Recommendation System:
  - Personalized Recommendations
  - Trending Products
  - Similar Products
  - Search-based Recommendations
- User Preference Management
- Product Feedback System

## Requirements

- PHP >= 8.1
- Composer
- Docker
- MySQL

## Installation

1. Clone the repository:
```bash
git clone https://github.com/shadighorbani7171/product-recommendation.git
cd product-recommendation
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Start Laravel Sail:
```bash
./vendor/bin/sail up -d
```

5. Run migrations and seeders:
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

## API Endpoints

### Authentication
- POST `/api/login` - User login
- POST `/api/register` - User registration
- POST `/api/logout` - User logout (requires authentication)

### Products
- GET `/api/products` - List all products
- GET `/api/products/{id}` - Get specific product
- POST `/api/products` - Create new product
- PUT `/api/products/{id}` - Update product
- DELETE `/api/products/{id}` - Delete product

### Recommendations
- GET `/api/recommendations` - Get personalized recommendations
- GET `/api/recommendations/trending` - Get trending products
- GET `/api/recommendations/similar/{product}` - Get similar products
- GET `/api/recommendations/search-based` - Get search-based recommendations
- POST `/api/recommendations/preferences` - Update user preferences (requires authentication)
- POST `/api/recommendations/{product}/feedback` - Record product feedback (requires authentication)

## Testing

Run tests using Laravel Sail:
```bash
./vendor/bin/sail artisan test
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
