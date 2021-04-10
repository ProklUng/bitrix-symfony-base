###################

Запуск генерации содержимого проекта. Последовательно выполняются команды
из ключа structure_project конфигурации бандла (файл local/configs/packages/bitrix_database.yaml)

php bin/console migrator:structure

###################

Инфоблок common, очищать элементы, генерировать подразделы, 5 элементов.

php bin/console migrator:elements common content --truncate=true --sections=true --count=5

Инфоблок spravochnik, не очищать элементы, не генерировать подразделы, 5 элементов

php bin/console migrator:elements spravochnik content --truncate=false --sections=false --count=5

HL-block ExampleHighload. Очищать элементы, генерировать подразделы, 5 элементов.

php bin/console migrator:elements-hl ExampleHighload --truncate=true --count=5