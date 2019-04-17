### Installation

```bash
composer install
cp .env.example .env    // set db vars
                        // set FRONT_END_URL to client endpoint
php artisan jwt:secret && php artisan storage:link
php artisan migrate
```
