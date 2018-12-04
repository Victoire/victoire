# Backend contribution

## Setup

Verify that you have `less` installed on your system, or install it if necessary:

```bash
npm install -g less
```

Then, install victoire and launch built-in server:

```bash
make start
```
Once the server has been launched, users fixtures have been loaded, you can connect to http://localhost:8000/

Username is `anakin@victoire.io` and password is `test`.

## Run tests

Launch Selenium:

```bash
docker run -d -p 4444:4444 selenium/standalone-firefox:2.53.1
```

And finally, run tests with Behat:

```bash
make test
```

or, to run specific scenario:

```bash
make test arg=Tests/Features/Page/create.feature:8
```