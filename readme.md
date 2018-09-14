## How to run the tests

After the repository has been cloned cd into the project root and run the following commands:

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
touch database/database.sqlite
```

These commands set everything up ready for the tests to be run. To run the test type
```./vendor/bin/phpunit```