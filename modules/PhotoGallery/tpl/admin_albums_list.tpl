{strip}
<div class="js__module" data-is-sortable="1">
    <div class="top-buttons-float">
        <a class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.color}" href="{$insertAlbumUrl}">+ Добавить альбом</a>
	    {if {$backlink}}<a class="sensei-btn sensei-btn_m sensei-btn_white" href="{$backlink}"><- Назад</a>{/if}
        {if $parentAlbum.extraData.url}
        <a class="link external" href="{$parentAlbum.extraData.url}" target="_blank">Открыть на сайте</a>
        {/if}

        {if $parentAlbum.extraData.images_url}
	    <a class="link external" href="{$parentAlbum.extraData.images_url}">Изображения текущего альбома</a>
	    {/if}
        <a class="go-to-top" href="#">Наверх</a>
    </div>

    {if $parentAlbum}
    <h2>Дочерние альбомы</h2>
    {/if}

    <div class="items-list js__items-list">
        {foreach $albums as $album}
        <div class="items-list__item flex ff-rn jc-sb ai-c" data-album-id="{$album.fields.id}">
            <div class="items-list__info">
                <p class="bold-black">{$album.fieldsForOutput.name}</p>
                {if $album.extraData.url}
                <p class="small-grey">
                    Страница альбома: <a class="link external" href="{$album.extraData.url}" target="_blank">открыть</a>
                </p>
                {/if}

	            <p class="small-grey">Изображений в альбоме: {$album.extraData.images_count}</p>

                <p class="links">
	                {if $album.fields.page_id}
                    <a class="link" href="{$album.extraData.albums_url}">Альбомы</a>
	                {/if}
                    <a class="link" href="{$album.extraData.images_url}">Изображения</a>
                </p>

                {if !$album.fieldsForOutput.page_id}
	            <div class="gallery-widget-code-block">
		            Код виджета ( <a class="link js__copy-to-clipboard" data-clipboard-target="#widget-code-{$album.fields.id}" href="#">Скопировать</a> ):
		            <div id="widget-code-{$album.fields.id}">{$album.extraData.widget_code}</div>
	            </div>
	            {/if}
            </div>

            {if $album.extraData.preview_images}
	        <div class="items-list__preview">
		        <div class="gallery-preview-images">
                    {foreach $album.extraData.preview_images as $PreviewImage}
	                    {if $PreviewImage@iteration <= 5}
	                        <img class="gallery-preview-image-{$PreviewImage@iteration}" src="{$PreviewImage.extraData.img_th_path}" alt="" />
	                    {else}
	                        {break}
	                    {/if}
                    {/foreach}
		        </div>
	        </div>
            {/if}

            <div class="items-list__action-btns">
                <a class="link item-action item-move js__move-item" href="#">Переместить</a>
                <a class="link item-action item-update" href="{$album.extraData.update_url}">Редактировать</a>
                <a class="link item-action item-remove js__album-remove" data-album-id="{$album.fields.id}" href="#">Удалить</a>
            </div>
        </div>
        {foreachelse}
        <div class="items-list-empty">
            Дочерних альбомов пока нет
        </div>
        {/foreach}
    </div>
</div>
{/strip}