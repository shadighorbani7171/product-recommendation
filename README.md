Project Title: Semi-Intelligent Product Recommendation System

This Laravel-based project is a smart product recommendation API that suggests relevant products to users based on:

🔍 User preferences (selected categories)

👀 User interactions (views, likes, purchases, search history)

📊 Trending data (popular products by time frame)

💰 Price range analysis (average viewed/purchased prices)

Key Features:
✅ RESTful API for managing products and recommendations

✅ Personalized suggestions based on real-time user behavior

✅ Authentication with Laravel Sanctum

✅ Search-based, purchase-based, and feedback-driven recommendations

✅ Fully extendable for machine learning in future

Tech Stack:
PHP (Laravel 10)

MySQL

Laravel Sail (Docker)

Postman (API testing)

React (Frontend - in progress)

TypeScript (Frontend - in progress)

🇮🇷 فارسی (توضیح برای گیت‌هاب)
عنوان پروژه: سیستم پیشنهاد محصول نیمه‌هوشمند

این پروژه با استفاده از فریم‌ورک Laravel توسعه داده شده و یک سیستم پیشنهاد محصول است که بر اساس رفتار کاربر، محصولات مرتبط را پیشنهاد می‌دهد:

🔍 ترجیحات کاربر (دسته‌بندی‌های منتخب)

👀 تعاملات کاربر (بازدید، لایک، خرید، جستجو)

📊 محصولات ترند بر اساس زمان (هفتگی، روزانه، ماهانه)

💰 تحلیل محدوده قیمتی (بر اساس میانگین بازدیدها یا خریدها)

قابلیت‌ها:
✅ API کامل برای مدیریت و پیشنهاد محصولات

✅ پیشنهادات شخصی‌سازی‌شده بر اساس رفتار واقعی کاربر

✅ سیستم احراز هویت با Laravel Sanctum

✅ پیشنهاد بر اساس جستجو، خرید، و بازخورد (لایک/دیس‌لایک)

✅ قابلیت گسترش برای استفاده از الگوریتم‌های یادگیری ماشین در آینده

تکنولوژی‌ها:
PHP (Laravel 10)

MySQL

Laravel Sail (Docker)

Postman برای تست API

React (فرانت‌اند - در حال توسعه)

TypeScript (فرانت‌اند - در حال توسعه)




 


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
