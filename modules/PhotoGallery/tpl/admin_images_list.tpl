{strip}
<div class="js__module">
    <div class="top-buttons-float">
        <a class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.color} js__select-image-from-fm"
           data-album-id="{$currentAlbum.fields.id}"
           href="#">+ Добавить из менеджера</a>
        <a class="sensei-btn sensei-btn_m sensei-btn_white" href="{$backlink}"><- Назад</a>

        {if $currentAlbum.extraData.url}
		<a class="link external" href="{$currentAlbum.extraData.url}" target="_blank">Открыть на сайте</a>
	    {/if}

        <a class="go-to-top" href="#">Наверх</a>
    </div>

    {if $albums}
    <div class="photogallery-filter-album">
        <form action="/" method="get">
            <span>Альбом:</span>&nbsp;
            <select class="gallery-albums-list-filter" name="gallery_albums_list_filter">
	            <option data-url="{$imagePagePath}"></option>
                {foreach $albums as $album}
                <option data-url="{$album.extraData.admin_url}"{if $currentAlbum.fields.id eq $album.fields.id} selected="selected"{/if}>
                    {$album.fields.name|truncate:100:'...':false}
                </option>
                {/foreach}
            </select>
            <a class="link gallery-albums-list-filter-button" href="#">Выбрать</a>
        </form>
    </div>
    {/if}

    <div class="images-list flex ff-rw">
        {foreach $images as $image}
            {include file='PhotoGallery/tpl/admin_image.inc.tpl'}
        {foreachelse}
            <div class="items-list-empty">Изображений нет, или вы не выбрали альбом</div>
        {/foreach}
    </div>
    <div class="selected-elements-actions">
        <p>Действия с выделенными элементами:</p>
        <p><a class="link images-list-select-all" data-selected="0" data-select-text="Выделить все" data-unselect-text="Снять выделение" href="#">Выделить все</a></p>
        <p><a class="link images-list-remove-selected" href="#">Удалить</a></p>

        <div class="gallery-images-move-to">
            <p>Переместить изображения:</p>
            Альбом&nbsp;
            <select class="gallery-images-move-to-album" name="gallery_images_move_to_albums">
                <option value="0">- Без альбома -</option>
                {foreach $albums as $album}
                <option value="{$album.fields.id}">{$album.fields.name|truncate:100:'...':false}</option>
                {/foreach}
            </select>
            <a class="images-list-move-selected link"
               data-current-album-id="{$album.fields.id}"
               href="#">Перенести</a>
        </div>
    </div>
</div>
{/strip}