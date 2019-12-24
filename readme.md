# Server requirements 
 -   Linux server (It is recommended)
 -   Php >= 7.2.0
 -   Mysql
 -   Mongodb 
# Related projects 
 - [PassID](https://github.com/necipcanguler/PassID) (required)
 - [PassID Admin](https://github.com/necipcanguler/PassID-Admin) (required)
# Installation 
Make the database connection from the .env file

    php artisan migrate    
    php artisan db:seed 