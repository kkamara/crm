![crm.png](https://github.com/kkamara/useful/raw/main/crm.png)

![crm2.png](https://github.com/kkamara/useful/raw/main/crm2.png)

# Client Relational Management System

(2016) This project is a remake of my [barebones CRM](https://github.com/kkamara/oldcrm). Built with Laravel 5.x.

## Installation

Install [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/).


## Setup

Our [Makefile](https://github.com/kkamara/laravel-crm/blob/master/Makefile) is based at the root of project directory and provides us with a number of useful commands.

What you want to do is go to the root directory where you've stored this project and run the following commands:
```
cp .env.example .env # make our environment variables accessible to the app
make dev
```

This will build our app and all it's required services and libraries, as well as provide seed data for the database service.

When `make dev` completes your app should be accessible from any web browser on your system at the following address:
```
http://localhost:8000
```

# Popular APIs Included
- [Spatie's laravel-permissions](https://github.com/spatie/laravel-permission)

 - [Gmail API](https://developers.google.com/gmail/api/guides/)


Front end design by [maridlcrmn & j_holtslander](https://codepen.io/j_holtslander/pen/XmpMEp)

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[BSD](https://opensource.org/licenses/BSD-3-Clause)
