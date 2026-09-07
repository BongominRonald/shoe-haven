# Shoe Haven

Shoe Haven is a Laravel 12 e-commerce application for managing and selling footwear.

## Application areas

### Storefront
- Home / hero carousel
- Product catalogue and search
- Categories and brands
- Product details and reviews
- Cart and checkout
- Orders and order confirmation
- Wishlist
- Recently viewed products
- Contact and newsletter subscription
- Google authentication

### Admin panel
The admin area is organised into five groups:

1. **Overview** - dashboard and store health
2. **Catalog** - products, categories, inventory, homepage/hero
3. **Sales & Customers** - orders and customer accounts
4. **Communication** - customer messages and newsletter subscribers

The sidebar is responsive and collapsible, with breadcrumbs and consistent page navigation. Its desktop state is remembered in the browser.

## Installation

Requirements:
- PHP 8.2+
- Composer
- Node.js 20+
- SQLite or MySQL

Run:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Then open `http://127.0.0.1:8000`.

The seeded administrator is:
- Email: `admin@shoehaven.com`
- Password: `password`

Change this password before using the application outside local development.

## Database

`database/migrations` is the source of truth. The older SQL exports are stored in `database/legacy/` for reference only.

The bundled SQLite database is structurally aligned with the current migrations, but `migrate:fresh --seed` is recommended for a clean development environment.

## Frontend

The project uses Bootstrap 5 through Vite, with Shoe Haven-specific styles in `resources/css/app.css` and JavaScript in `resources/js/app.js`.

## Testing

Run:

```bash
php artisan test
```

## Production notes

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Configure a production database and mail provider.
- Run `npm run build`.
- Run `php artisan storage:link`.
- Replace the seeded administrator password.
- Configure real payment processing before accepting live payments.
