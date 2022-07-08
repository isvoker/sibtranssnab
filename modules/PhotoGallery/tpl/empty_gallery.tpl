{strip}
{if $galleryAdminUrl}
    <div class="photogallery-empty-admin-notice">
        На данный момент к странице не привязан ни один альбом. <a class="link" href="{$galleryAdminUrl}">Перейдите в админ-панель</a>.
    </div>
{else}
    <div class="photogallery-empty-notice">На данный момент в галерее нет изображений</div>
{/if}
{/strip}