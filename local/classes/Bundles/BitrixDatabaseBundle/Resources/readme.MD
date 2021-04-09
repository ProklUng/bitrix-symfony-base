Инфоблок common, очищать элементы, генерировать подразделы, 5 элементов.

php bin/console migrator:elements common content --truncate=true --sections=true --count=5

Инфоблок spravochnik, не очищать элементы, не генерировать подразделы, 5 элементов

php bin/console migrator:elements spravochnik content --truncate=false --sections=false --count=5