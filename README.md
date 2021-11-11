# crm



## To run locally

Have [docker](https://docs.docker.com/engine/install/) & [docker-compose](https://docs.docker.com/compose/install/) installed on your operating system.

```bash
git submodule init && \
    git submodule update && \
    cp .env.example .env && \
    make dev
```

## To run tests

```bash
vendor/bin/phpunit
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[LICENSE.md](https://github.com/kkamara/octobercms/blob/main/LICENSE.md)
