{strip}
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
    <meta charset="{$cfg.charset}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content="{$RTP.description}"/>
    <meta name="keywords" content="{$RTP.keywords}"/>
    {if $RTP.noindex}
    <meta name="robots" content="noindex"/>
    {/if}
    <meta name="author" content="АЕ веб-студия"/>
    <meta name="generator" content="CMF AEngine"/>
    {if $RTP.isAjaxPage}
    <meta name="fragment" content="!">
    {/if}

    <title>{$RTP.pageTitle}</title>

    {if $RTP.alternateHrefMobile}
    <link rel="alternate" media="only screen and (max-width: 768px)" href="{$RTP.alternateHrefMobile}"/>
    {/if}
    {if $RTP.canonicalHref}
    <link rel="canonical" href="{$RTP.canonicalHref}"/>
    {/if}

    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico"/>

    {include file="file:[main]styles.inc.tpl"}

	{$customHtml.head}

    {literal}
    <style id="FrameKiller">
    body{display:none!important}
    </style>
    <script>
    if(self===top||self.location.hostname.match(/^(webvisor\.com)$/)){const FrameKiller=document.getElementById('FrameKiller');FrameKiller.parentNode.removeChild(FrameKiller);}else{top.location=self.location;}
    </script>
    {/literal}

    <script>
    window.CSRF_KEY='{$session.token}';
    {if $ya_counter_id}
    window.ya_counter_id={$ya_counter_id};
    {/if}
    </script>
</head>

<body>
<div class="serviceMsg">
    <div id="ErrorMsg">{$ErrorMsg}</div>
    <div id="ErrorMsgDbg">{$ErrorMsgDbg}</div>
    <div id="InfoMsg">{$InfoMsg}</div>

    {if $validBrowser.answer eq 'old'}
    Ваш браузер {$validBrowser.name} {$validBrowser.yourVersion} устарел. Требуется версия не ниже {$validBrowser.correctVersion}.
    {elseif $validBrowser.answer eq 'bad'}
    В используемом Вами браузере корректные работа и отображение сайта не гарантируются. Настоятельно рекомендуем Вам обновить Ваш браузер.
    {/if}

    <noscript><strong>Отключён JavaScript</strong><br/>Ваш браузер не поддерживает работу со скриптами, или их поддержка отключена пользователем. Корректная работа сайта невозможна!</noscript>
</div>
{/strip}