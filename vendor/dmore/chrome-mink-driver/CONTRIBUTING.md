Contributing
============

Feel free to fork and improve.

You can run the tests locally using the docker image

```bash
cd chrome-mink-driver
docker run --rm -it -v $(pwd):/code -e DOCROOT=/code/vendor/mink/driver-testsuite/web-fixtures registry.gitlab.com/behat-chrome/docker-chrome-headless bash
```

Then in the shell:

```bash
composer install
vendor/bin/phpunit
```
