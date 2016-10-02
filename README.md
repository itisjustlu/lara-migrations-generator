#Laravel migrations generator
This packages only works with mysql for now
##Available for Laravel 5.3

###Why should I use this package?
If you have a previous database, and you want to import to laravel, this is the perfect package.
This package will import all of your tables, column with their respectives foreign keys.

### How to install?
Run
```
composer install lucasruroken/lara-migrations-generator dev-master
```

All you have to do is add the following provider into **config/app.php**
```
\LucasRuroken\LaraMigrationsGenerator\LaraMigrationsGeneratorProvider::class,
```

and run in your console
```
php artisan generate:migrations:mysql
```
