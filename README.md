# API for SimplyBudget app

## Requirments

* Docker
* docker-compose 

## Running

1. Create network for application: `docker network create budzet-rozliczeniowy`
2. Run `docker-compose up` to build and start server
3. Run `docker-compose exec app composer install` to download required packages
4. Go to http://localhost:8080 to see the app is working

