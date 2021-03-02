# Datahub

[![Software License][ico-license]](LICENSE)
[![Build Status](https://travis-ci.org/thedatahub/Datahub.svg?branch=master)](https://travis-ci.org/thedatahub/Datahub)

The Datahub is a metadata aggregator. This application allows data providers to 
aggregate and publish metadata describing objects on the web through a RESTful 
API leveraging standardized exchange formats.

The Datahub is build with the [Symfony framework](https://symfony.com) and 
[MongoDB](https://www.mongodb.org).

## Features

* A RESTful API which supports:
  * Ingest and retrieval of individual metadata records.
  * Validation of ingested records against XSD schemas.
  * Supports OAuth to restrict access to the API.
* An OAI-PMH endpoint for harvesting metadata records.
* Includes support for [LIDO XML](http://lido-schema.org/) but can be extended 
to include MARC XML, Dublin Core or other formats.

## Requirements

This project requires following dependencies:

* PHP = 5.6.* or 7.0.*
  * With the php-cli, php-intl, php-mbstring and php-mcrypt extensions.
  * The [PECL Mongo](https://pecl.php.net/package/mongo) (PHP5) or [PECL Mongodb](https://pecl.php.net/package/mongodb) (PHP7) extension. Note that the _mongodb_ extension must be version 1.2.0 or higher. Notably, the package included in Ubuntu 16.04 (_php-mongodb_) is only at 1.1.5.
* MongoDB >= 3.2.10

## Install

Via Git:

``` bash
$ git clone https://github.com/thedatahub/Datahub.git datahub
$ cd datahub
$ composer install # Composer will ask you to fill in any missing parameters 
  before it continues
```

You will be asked to configure the connection to your MongoDB database. You 
will need to provide these details:

* The connection to your MongoDB instance (i.e. mongodb://127.0.0.1:27017)
* The username of the user (i.e. datahub)
* The password of the user
* The database where your data will persist (i.e. datahub)

Before you install, ensure that you have a running MongoDB instance, and you 
have created a user with the right permissions. From the 
[Mongo shell]https://docs.mongodb.com/getting-started/shell/client/) run these
commands to create the required artefacts in MongoDB:

```
> mongo -u siteUserAdmin -p passw0rd --authenticationDatabase admin
> use datahub
> db.createUser(
   {
     user: "datahub",
     pwd: "password",
     roles: [ "readWrite", "dbAdmin" ]
   }
)
```

The configuration parameters will be stored in `app/config/parameters.yml`.  
You'll need to run an initiial one-time setup script, which will scaffold the 
database structure, generate CSS assets and create the application 'admin' user.

``` bash
$ app/console app:setup
$ app/console doctrine:mongodb:fixtures:load --append
```

If you want to run the datahub for testing or development purposes, execute 
this command:

``` bash
$ app/console server:run
```

Use a browser and Navigate to [http://127.0.0.1:8000](http://127.0.0.1:8000). 
You should now see the welcome screen.

Refer to the [Symfony setup documentation](https://symfony.com/doc/current/setup/web_server_configuration.html) 
to complete your installation using a fully featured web server to make your 
installation operational in a production environment.

## Usage

### Credentials

The application is installed with as default username `admin` and as default password `datahub`. Changing this is highly recommended.

### The REST API

The REST API is available at `api/v1/data`. Documentation about the available 
API methods can be found  at `/docs/api`.

#### POST and PUT actions

The PUT and POST actions expect and XML formatted body in the HTTP request. 
The Content-Type HTTP request header also needs to be set accordingly. 
Currently, supported: `application/lido+xml`. Finally, you will need to add a 
valid OAuth token via the `access_token` query parameter.

A valid POST HTTP request looks like this:

```
POST /api/v1/data?access_token=MThmYWMxMjFlZWZmYjVmZDU2NDNmZWIzYTE0YmNiYTk3YTc5ODJmMWJjOGI1MjE5MWY4ZjEyZWZlZmM2ZmZmNg HTTP/1.1
Host: example.org
Content-Type: application/lido+xml
Cache-Control: no-cache

<?xml version="1.0" encoding="UTF-8"?>
<lido:lido xmlns:lido="http://www.lido-schema.org" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.lido-schema.org http://www.lido-schema.org/schema/v1.0/lido-v1.0.xsd">
	<lido:lidoRecID lido:source="Deutsches Dokumentationszentrum für Kunstgeschichte - Bildarchiv Foto Marburg" lido:type="local">DE-Mb112/lido-obj00154983</lido:lidoRecID>
	<lido:category>
...
```

#### GET actions

Sending a GET HTTP request to the `api/v1/data` endpoint will return a 
paginated list of all the records available in the API. The endpoint will 
return a HTTP response with a JSON formatted body. The endpoint respects the 
[HATEOAS](https://en.wikipedia.org/wiki/HATEOAS) constraint.

Content negotation is currently only supported via a file extension on 
individual resource URL's. Negotation via the HTTP Accept header is on the 
roadmap.

```
GET api/v1/data               # only JSON supported
GET api/v1/data/objectPID     # return JSON
GET api/v1/data/objectPID.xml # return XML
```

### The OAI endpoint

The datahub supports the [OAI-PMH protocol](https://www.openarchives.org/OAI/openarchivesprotocol.html). 
The endpoint is available via the `/oai` path.

```
GET oai/?metadataPrefix=oai_lido&verb=ListIdentifiers
GET oai/?metadataPrefix=oai_lido&verb=ListSets
GET oai/?metadataPrefix=oai_lido&verb=ListRecords
GET oai/?metadataPrefix=oai_lido&verb=ListRecords&metadataPrefix=oai_lido&set=creator:brueghel_pieter_ii
GET oai/?metadataPrefix=oai_lid&verb=GetRecord&metadataPrefix=oai_lido&identifier=objectPID
GET oai/?metadataPrefix=oai_lido&verb=ListIdentifiers&metadataPrefix=oai_lido&from=2017-06-29T05:22:30Z&until=2017-07-14T04:22:30Z
```

The datahub implements grouping of records into sets, but no soft deletes. As such, the OAI endpoint doesn't indicate whether a record has been deleted.

### OAuth support and security

The datahub API can be set up to be either a public or a private API. The 
`public_api_method_access` parameter in `parameters.yml` allows you to 
configure which parts of the API are public or private:

`````YAML
    # Setting this to some unknown value like [FOO] disables public api access
    # Leaving this option empty [] means allowing all methods for anonymous access
    # public_api_method_access: [FOO]
    public_api_method_access: [GET]
`````

The datahub requires OAuth authentication to ingest or retrieve metadata 
records. The administrator has to issue a user account with a client_id and a 
client_secret to individual Users or client applications. Before clients can 
access the API, they have to request an access token:

```bash
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

The endpoint can also be used to revoke both access and refresh tokens.

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

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed 
recently.

## Testing

Testing will require a MongoDB instance, as well as Catmandu installed. You 
can either take care of this yourself, or run the tests using the provided 
Docker container.

Please ensure you've taken care of the initial setup described above before 
attempting to run the tests.

Running tests:

```
./scripts/run_tests
```

Running tests using Docker:

```
./scripts/run_tests_docker
```

## Front end development

Front end workflows are managed via [yarn](https://yarnpkg.com/en/) and 
[webpack-encore](https://symfony.com/blog/introducing-webpack-encore-for-asset-management).

The layout is based on [Bootstrap 3.3](https://getbootstrap.com/docs/3.3/) 
and managed via sass. The code can be found under `app/resources/public/sass`. 

Javascript files can be found under `app/resources/public/js`. Dependencies are 
managed via `yarn`. Add vendor modules using `require`.

Files are build and stored in `web/build` and included in `app/views/app/base.html.twig`
via the `asset()` function.

The workflow configuration can be found in `webpack.config.js`.

Get started:

```
# Install all dependencies
$ yarn install
# Build everything in development
$ yarn run encore dev
# Watch files and build automatically
$ yarn run encore dev --watch
# Build for production
$ yarn run encore production
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Authors

[All Contributors][link-contributors]

## Copyright and license

The Datahub is copyright (c) 2016 by Vlaamse Kunstcollectie vzw and PACKED vzw.

This is free software; you can redistribute it and/or modify it under the 
terms of the The GPLv3 License (GPL). Please see [License File](LICENSE) for 
more information.

[ico-version]: https://img.shields.io/packagist/v/:vendor/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/:vendor/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/:vendor/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/:vendor/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/:vendor/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/:vendor/:package_name
[link-travis]: https://travis-ci.org/:vendor/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/:vendor/:package_name/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/:vendor/:package_name
[link-downloads]: https://packagist.org/packages/:vendor/:package_name
[link-author]: https://github.com/:author_username
[link-contributors]: ../../contributors
