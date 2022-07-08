{strip}
<!DOCTYPE html>
<html>
<head>
    <title>Ваш браузер является устаревшим</title>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
    <style type="text/css">
        .bad-browser {
            width: 800px;
            padding: 20px;
            margin: 0 auto;
            font-size: 16px;
            font-family: Arial;
            line-height: 1.5;
        }
        img {margin: 20px 0;}
        p {text-align: center}
    </style>
</head>
<body>
    <div class="bad-browser">
        {if $validBrowser.answer eq 'old'}
            <p>Ваш браузер <b>&quot;{$validBrowser.name} {$validBrowser.your_version}&quot;</b> является устаревшим. Для корректного отображения содержимого сайта требуется более новая версия, которую Вы можете скачать, пройдя по <a href="{$validBrowser.link}">ссылке</a>.</p>
        {/if}
        {if $validBrowser.answer eq 'bad'}
            <p>К сожалению, в используемом Вами браузере не гарантируется корректное отображение содержимого сайта.&nbsp;
                Рекомендуется использовать следующие программы или их более поздние версии:</p>
            <ul>
            {foreach from=$validBrowser.allowed item=v key=k name=validBrowserLoop}
                <li>
                    <a href="{$v.link}">{$v.name}</a>
                </li>
            {/foreach}
            </ul>
        {/if}
    </div>
</body>
</html>
{/strip}