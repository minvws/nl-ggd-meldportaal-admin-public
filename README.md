# Meldportaal User Admin

Meldportaal gebruiker beheer

Front-end application to manage user of the meldportaal.

## Production Install
This is a Laravel application. It can be hosted with apache/nginx and php8 or higher.

The docker install provided is for development purposes only. Additional security measures should be taken when using this application in production.

## Developer Install

After creating a local git checkout of this repository, getting this project up
and running is a matter of:

1. Start the Docker container
2. Generate SSL certificates
3. Install the PHP dependencies
4. Set up the application
5. Create the database

### Quick Start:

For those familiar with the process, use the following commands:

```sh
# Create a local git checkout of this repository
git clone git@github.com:minvws/nl-ggd-meldportaal-admin.git nl-ggd-meldportaal-admin && cd $_

# 1. Create an .env file
cp .env.example .env

# 2. Generate SSL certificates
docker-compose exec meldportaal-admin bash ./bin/generate-crypto.sh 'meldportaal-admin.localdev' 'ssl/'

# 3. Install the PHP dependencies
docker-compose exec meldportaal-admin composer install --ignore-platform-req=ext-sockets

# 4. Set up the application
docker-compose exec meldportaal-admin php artisan key:generate

# 5. Create the database
docker-compose exec meldportaal-admin php artisan migrate

# 6. Start the Docker container
docker-compose up -d
```

## Usage

The application offers user management for user of the meldportaal

## Development

This application has been developed in Laravel, please see the [Laravel docs][laravel-docs]
for Laravel specific details.

### Users

Before logging in and using the application, an account needs to be created 
using the `artisan user:admin` command. For example:

```sh
docker-compose exec meldportaal-admin php artisan user:admin 'user@example.com' 'User Name' 'P@s$W0rD'
```

This will output a string that contains a code that needs to be provided to a 
2FA one-time password generator. 

The string looks like this:
```
otpauth://totp/MP:{password}?secret=A1B2C3D4E5F6G7h8&issuer=INGE3&algorithm=SHA1&digits=6&period=30
```

The value of `secret` is to be used as input for your 2FA one-time password generator.

If an actual image is needed (for instance to scan the QR code from a mobile device),
the output from this command can be piped to something like [qrencode](https://github.com/fukuchi/libqrencode/)
and output on the screen (in this case using `display`)

```sh
docker-compose exec meldportaal-admin php artisan user:admin 'user@example.com' 'User Name' 'P@s$W0rD' \
  | qrencode -o- \
  | display
```

<sup>Depending on the OS and other system details, other applications than `qrencode` and `display` might have to be used.</sup>

### Docker compose

- Before running `docker-compose up`, be sure to create and edit a `.env` file .

- Whenever something changes in the docker setup, don't forget to re-build the
containers:
  ```sh
  docker-compose exec meldportaal-admin up --build --remove-orphans
  ```

- A script that checks the develop environment is run when `docker-compose up` is run.
  This script will report any problems and try to make suggestions on how to resolve things.
  These suggestions can usually be run inside the Docker container (using `docker-compose exec meldportaal-admin some-command`).

- Docker-compose might show this warning:
  ```WARNING: The [...] variable is not set. Defaulting to a blank string.```
  This is caused when a `.env` file has not been created or when the `.env` file
  is missing a variable used in the docker(-compose) file.


[laravel-docs]: https://laravel.com/docs/8.x
