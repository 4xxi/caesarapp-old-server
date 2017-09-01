# caesarapp-server

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/55b5e122-1861-4757-bc92-10f4269ea6a3/big.png)](https://insight.sensiolabs.com/projects/55b5e122-1861-4757-bc92-10f4269ea6a3)

==========
## Setup
### Linux/Windows/MacOS
Install [Docker and Docker Compose](https://docs.docker.com/engine/installation)
### MacOS only
Install Docker Sync
```bash
gem install docker-sync
```
## Run
### Linux/Windows
```bash
docker-compose up -d
```
### MacOS
```bash
docker-sync-stack start
```
## Configuration .env file
```bash
ENV=dev

HTTPS_PORT=443
HTTP_PORT=80

MONGODB_HOST=mongodb
MONGODB_PORT=27017
MONGODB_DATABASE=caesar
#do not change!
WWW_DIR=/var/www/html
```

## API
API documentation availabl at `/api` endpoint.
