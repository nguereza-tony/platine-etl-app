## PLATINE ETL APPLICATION
**A Platine PHP ETL (Extract-Transform-Load) application template**

### Requirements 
- Any web server **(Apache, nginx, lighttp, etc.)**
- **PHP >= 7.4**, **PHP 8** 


### Installation
Platine Application can be installed using composer
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

### Install on docker
If you want to test platine using docker follow the step below:
- Build the platine image
```bash
docker-compose build app
```
- Run the containers
```bash
docker-compose up -d
```
- Install composer dependencies
```bash
docker-compose exec app composer install --no-interaction --optimize-autoloader --no-dev
```
Then open `http://server_domain_or_IP:8000`, for example `http://localhost:8000`.  

### License
MIT License See [LICENSE.MD](LICENSE.MD)

### Resources 
 - [Documentation](https://docs.platine-php.com)  
 - Tutorial Youtube (French): [Budget Management Application](https://www.youtube.com/playlist?list=PLbUHyVcL-6Q8H6ip40eSdpt4MjRXYGPz9)
