{strip}
<div>
    <button id="update-sitemap" class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.color}" type="button">Обновить файлы Sitemap</button>
    <button id="update-robots_txt" class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.color}" type="button">Обновить файл robots.txt</button>
    <button id="disallow-robots" class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.color}" type="button">Запретить индексирование сайта</button>
    <a class="link" href="/robots.txt" target="_blank">Текущее содержимое файла robots.txt</a>
</div>
<div class="sensei-message sensei-message_warning sensei-message_big">Все операции необходимо выполнять, находясь на основном домене сайта!</div>
{include file='file:[admin]site_options_form.inc.tpl'}
{/strip}