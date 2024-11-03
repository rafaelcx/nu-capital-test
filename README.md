# Nu-Capital

A CLI program that calculates taxes on profits or losses from stock market transactions.

---

Provisioning using Docker

```
docker-compose run --rm -it nu-capital-cli /bin/bash
```

Application execution via normal input or input redirection:

```
php app/app.php
php app/app.php < input.txt
```

Running Tests:

```
./vendor/bin/phpunit test/
```

