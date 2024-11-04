# Nu-Capital

A CLI program that calculates taxes on profits or losses from stock market transactions.

---

## Provisioning

**Provisioning using Docker:**

Provisioning should be done running the command below on your terminal. A container
will be started, and you will jump inside a bash session inside it: 

`docker-compose run --rm -it nu-capital-cli /bin/bash`

After jumping inside the container run:

`composer install`

While inside the bash session you can interact with the application (execute it or run tests) following the next instructions

## Execution

Application execution can be done via normal input: `php app/app.php`

Or via input redirection (fill out input.txt with desired input): `php app/app.php < input.txt`


## Tests

To execute the application test suite run `./vendor/bin/phpunit test/`

---

## Disclaimers to reviewers

- The project is written in PHP 8.2. Although we already have a stable 8.3 version, the latest official docker image for PHP cli applications uses version 8.2. 
- The entrypoint for the application resides at `/app/app.php`.
- The application state is controlled via an in memory ledger style structure, where I store all main events.
- The `src\Services` namespace is responsible to handle the business logic.
- The `\src\Controllers` namespace was created to segregate input handling from the aforementioned business logic.
- The only external dependency for this project is `phpunit` which is used to simplify testing, contained at `\test`

