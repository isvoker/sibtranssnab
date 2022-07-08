### Установка

Для установки внешних пакетов, в корневой директории проекта выполните команду:

```shell
composer install
```
Создайте необходимые конфигурационные файлы:
```shell
mv app/config/CfgSecret.php.sample app/config/CfgSecret.php
mv app/config/Cfg.php.sample app/config/Cfg.php
```
Заполните ваши данные в **CfgSecret.php**. Отредактируйте **Cfg.php** если необходимо.

Для первичного наполнения БД выполните команду:
```
php app/cli/aengine.php --install [-l "${adminLogin}"] [-p "${adminPass}"]
```
Готово!