{strip}
<div class="photogallery-widget js__gallery-container" data-is-widget="1" data-album-id="{$album.fields.id}" data-part="1">
    {if $PhotoGalleryAdminImagesLink}
    <a class="update-link update-link--static" href="{$PhotoGalleryAdminImagesLink}" target="_blank">Управление изображениями</a>
    {/if}

    <div class="photogallery-images-container flex ff-rw">
        {include file='PhotoGallery/tpl/images.inc.tpl'}
    </div>
    <div class="photogallery-request-runner hidden"></div>
    
    {if $GalleryShowLoadButton}
    <div class="photogallery-module-load-button-container">
        <a class="button button--gray photogallery-load-button js__gallery-load-btn" href="#">Показать еще</a>
    </div>
    {/if}
</div>
{/strip}