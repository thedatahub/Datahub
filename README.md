datahub
===========

A Symfony project created on September 28, 2016, 8:59 am.

## Setup

You'll need composer first.

```
./composer.phar install
# Composer will ask you to fill in any missing parameters before it continues
./scripts/update_install
```

## Initial app setup & first user

Run the following commands:

```
app/console app:setup

app/console doctrine:mongodb:fixtures:load --append
```

## Documentation

The REST API generates documentation available at `/docs/api`.

You can build static documentation by executing the following:

```
./scripts/build_docs
```

The script above will generate documentation which will be available at
`./docs/build/api/` and `./docs/build/code/`.

## Usage

These examples assume your instance is running at `http://localhost:8000`.

### Running the development server

```
app/console server:run
```

Example output:

```
 [OK] Server running on http://127.0.0.1:8000

 // Quit the server with CONTROL-C.
```

Your development server will be available at `http://localhost:8000` by
default.

### Getting an access token

```
curl 'http://localhost:8000/oauth/v2/token?grant_type=password&username=admin&password=datahub&client_id=slightlylesssecretpublicid&client_secret=supersecretsecretphrase'
```

Example output:

```
{
    "access_token": "ZDIyMGFiZGZkZWUzY2FjMmY4YzNmYjU0ODZmYmQ2ZGM0NjZiZjBhM2Q0Y2ZjMGNiMjc0ZWIyMmYyODMzMGJjZg",
    "expires_in": 3600,
    "token_type": "bearer",
    "scope": "internal web external",
    "refresh_token":  "MzhkYzY0MzMxM2FmNmQyODhiOWM4YzEzZjI3YzViZjg3ZThlMTA2YWY4ZTc2YjUwYzgxNzVhNTlmYTBkYWZhNQ"
}
```

### Revoking a token

This endpoint can be used to revoke both access and refresh tokens.

```
curl 'http://localhost:8000/oauth/v2/revoke?token=ZDIyMGFiZGZkZWUzY2FjMmY4YzNmYjU0ODZmYmQ2ZGM0NjZiZjBhM2Q0Y2ZjMGNiMjc0ZWIyMmYyODMzMGJjZg'
```

Example output:

```
{
    "result": "success",
    "message": "The token has been revoked."
}
```

## Testing

Testing will require a MongoDB instance, as well as Catmandu installed.
You can either take care of this yourself, or run the tests using the
provided Docker container.

Please ensure you've taken care of the initial setup described above before
attempting to run the tests.

### Running tests

```
./scripts/run_tests
```

### Running tests (Docker)

```
./scripts/run_tests_docker
```
