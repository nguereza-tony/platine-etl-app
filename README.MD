## PLATINE ETL APPLICATION
**A Platine PHP ETL (Extract-Transform-Load) application template**

### Requirements 
- Any web server **(Apache, nginx, lighttp, etc.)**
- **PHP >= 7.4**, **PHP 8** 


### Installation
```bash
git clone git@github.com:nguereza-tony/platine-etl-app.git

cd platine-etl-app

composer install
```


### Configuration
```bash
cp .env.example .env
```
Update configurations like 
```ini
PL_APP_URL=http://localhost/platine-etl-app/public
PL_DB_DRIVER=mysql
PL_DB_NAME=db_etl_app
PL_DB_HOST=127.0.0.1
PL_DB_PORT=3306
PL_DB_USER=root
PL_DB_PASSWORD=
```


### Migration
Execute migration
```bash
php platine migration:init
php platine migration:migrate
php platine seed:exec
```


### Launch the application
Access the application via http://localhost/platine-etl-app/public 

### License
MIT License See [LICENSE.MD](LICENSE.MD)

### Resources 
 - [Documentation](https://docs.platine-php.com)