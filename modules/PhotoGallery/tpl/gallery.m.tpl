{strip}
<div class="photogallery-module js__gallery-container" data-album-id="{$galleryCurrentAlbum.fields.id}" data-part="1">
    {if $BackLink}
    <a class="button button--dark" href="{$BackLink.url}">{$BackLink.name}</a>
    {/if}
    
    {if $galleryAlbums}
    <div class="photogallery-albums-list flex ff-rw">
        {foreach $galleryAlbums as $album}
        <div class="col-4">
            <a class="photogallery-albums-list-item" href="{$album.extraData.url}">
                <div class="photogallery-albums-list-item-image-container">
                    <div class="photogallery-albums-list-item-image" style="background-image:url('{$album.extraData.cover_resized}');"></div>
                </div>
                <div class="photogallery-albums-list-item-title theme-color-2">{$album.fieldsForOutput.name}</div>
            </a>
        </div>
        {/foreach}
    </div>
    {/if}

    <div class="photogallery-images-container flex ff-rw jc-c">
        {include file='PhotoGallery/tpl/images.inc.tpl'}
    </div>
    <div class="photogallery-request-runner hidden"></div>
        
    {if $galleryShowLoadButton}
    <div class="photogallery-module-load-button-container">
	    <a class="button button--gray photogallery-load-button js__gallery-load-btn" href="#">Показать еще</a>
    </div>
    {/if}

	<div class="photogallery-content text-content">
        {if $galleryCurrentAlbum}
            {$galleryCurrentAlbum.fieldsForOutput.content}
        {else}
            {$textContent}
        {/if}
	</div>
</div>
{/strip}