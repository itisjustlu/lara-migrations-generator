#Laravel migrations generator

##Available for Laravel 5.2

###Why should I use this package?
If you have a previous database, and you want to import to laravel, this is the perfect package.
This package will import all of your tables, column with their respectives foreign keys.

### How to install?
All you have to do is add the following code to **app/Console/Kernel.php** inside
the **protected $commands** var
```
\LucasRuroken\LaraMigrationsGenerator\Commands\LaraMigrationsGeneratorCommand::class,
```

and run in your console
```
php artisan generate:migrations:from-mysql
```
