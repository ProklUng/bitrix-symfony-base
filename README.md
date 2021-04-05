# Bitrix Project

Заготовка для 1C Bitrix проектов. На базе https://github.com/regiomedia/bitrix-project

## Создание нового проекта

- Создать папку нового проекта в OSPanel/domains и Клонировать эту базовую сборку

К примеру:
```sh
git clone proklung/bitrix-symfony-base.git ./
```

- Настроить вебсервер для работы с директорией `sites/s1` либо сделать симлинк вида
  
  ```sh
  /home/bitrix/www -> /home/bitrix/projectname/sites/s1
  ```
  (Настроить локальный домен в OpenServer со ссылкой на sites/s1)

### Зависимости

- Установить зависимости composer (frontend):
```sh
composer install
```

- Установить зависимости npm (backend):
```sh
npm install
```

### Bitrix

- Удалить папку bitrix
- Инициализировать submodule:
```sh
git submodule init
```

- Запустить submodule (cклонируется bitrix):
```sh
git submodule update
```

Или настроить его иным способом.

### Символьные ссылки

В директорию `sites/s1` перенести публичные файлы сайта.

В виндовом терминале:

```sh
mklink "local" "../../local" /j
mklink "bitrix" "../../bitrix" /j
mklink "upload" "../../upload" /j
```

### База данных и окружение

- Создать базу данных на localhost

- Развернуть бэкап базы из bitrix/backup

- Создать файл `.env` 

```sh
touch .env
```

- Заполнить его данными в соответствии с файлом-образцом `.env.example`

- Развернуть окружение

```sh
./vendor/bin/jedi env:init default

Update: упрощенная локальная версия Jedi - в терминале: php bin/jedi env:init default

Эта команда скопирует в директорию `bitrix` системные файлы настроек сконфигурированные для работы с 
[переменными окружения](https://github.com/vlucas/phpdotenv), а также настройки 
[шаблонизатора Twig](https://github.com/maximaster/tools.twig) 
и [логгера Monolog](https://github.com/bitrix-expert/monolog-adapter)

### Миграции

- Установить [модуль миграций](https://github.com/arrilot/bitrix-migrations)

```sh
php migrator install
```

- Запуск миграций

```sh
php migrator migrate
```

** Доустановить модуль [Базовых Битрикс компонентов](https://github.com/bitrix-expert/bbc). в административном интефейсе: 

`Marketplace > Установленные решения > ББК (bex.bbc)`


### "Собрать" фронтенд

```sh
npm run encore -- dev
```

## Бэкенд

Composer и PSR-4 автозагрузка классов из директории `local/classes`. Пространство имен `\Local\ `

Проверка осуществляется командой (это запуск утилиты `phpcs` с предустановленными параметрами) 

```sh
composer run lint:php
```

Также есть возможность исправить часть обнаруженных ошибок утилитой `phpcbf`

```sh
composer run fix:php
```

## Фронтенд

В качестве "сборщика" изпользуется [symfony/webpack-encore](https://github.com/symfony/webpack-encore). 

По-умолчанию файлы фронтенда должны располагаться в директории `local/assets`.

Это можно переопределить в файле конфигурации [webpack.config.js](./webpack.config.js) 

Основные команды:

```sh
npm run encore -- dev          # запустить сборку для разработчика один раз
npm run encore -- dev --watch  # запустить сборку для разработчика в режиме слежения за файлами
npm run encore -- production   # запустить сборку для продакшена
```
    
#### Vue

Мини-модуль [vueInvoker](local/assets/scripts/util/vueInvoker.js) 
предназначен для инициализации Vue компонентов на странице.
Он упрощает использование Vue классическом веб-приложении, когда нет возможности 
использовать один "корневой" экземпляр `Vue` (Как, например, это устроено в одностраничных приложениях).

#### Использование:

Вывести на страницу элемент-плейсхолдер для компонента:

```html
<div class="vue-component" data-component="DemoApp" data-initial='{"test": "data"}'></div>
```

Создать соответствущий Vue-компонент (в директории `local/assets/scripts/vue/components/`:


```html
<template>
    <div class="demo-app">
        {{ hello }}

        {{ initial.test }}

    </div>
</template>

<script>
    export default {
      data() {
        return {
          hello: 'World',
        };
      },
      props: ['initial'],
    };
</script>
```

Добавить его в Коллекцию `local/assets/scripts/vue/collection.js`:

```js
import DemoApp from './components/DemoApp.vue';

export default {
  DemoApp,
};
```

## Многосайтовость

Структура проекта напоминает _заранее_ настроенную 
[многосайтовость на разных доменах](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=103&LESSON_ID=287) 
с отдельными директориями для каждого сайта. Файлы ядра подключаются _относительными_ символическими ссылками.
Для добавления нового сайта нужно создать новую директорю в `./sites/`(лучше всего если ее название будет 
соответствовать коду нового сайта). И добавить в нее ссылки на необходимые файлы и директории:

```
mkdir sites/s2             # создать директорию для дополнительного сайта
cd sites/s2                # перейти в нее
mklink "local" "../../local" /j
mklink "bitrix" "../../bitrix" /j
mklink "upload" "../../upload" /j

``` 

Далее необходимо настроить веб-сервер для работы с новым сайтом.