<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
    <title><?php echo $_SERVER[ 'HTTP_HOST' ]; ?> | сервисный режим</title>
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="user-scalable=yes, width=device-width" />
    <meta charset="UTF-8"/>
    <style>
        html,body{margin:0;min-width:320px;padding:0}
        html{font-size:16px;font-family:Arial,sans-serif}
        .service-mode-info{margin:50px auto;max-width:1100px;padding:0 15px}
        .service-mode-info p{font-size:1rem;margin:10px 0;text-align:center}
        .service-mode-info a{color:#0a90eb;text-decoration:underline}
    </style>
</head>
<body>
    <div class="service-mode-info">
        <?php echo SiteOptions::get('service_mode_message'); ?>
    </div>
</body>
</html>
