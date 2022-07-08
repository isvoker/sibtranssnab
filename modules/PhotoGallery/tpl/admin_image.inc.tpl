{strip}
<div class="images-list-item" data-image-id="{$image.fields.id}">
    <a class="images-list-item__image" data-fancybox='gallery-image' data-caption="{$image.fieldsForOutput.caption}" href="{$image.fieldsForOutput.img_path}">
        <img src="{$image.extraData.admin_image_resized}" data-image-url="{$image.fieldsForOutput.img_path}" alt=""/>
    </a>
    <div class="images-item-name">{$image.extraData.file_name|truncate:80:'...':true}</div>

    <div class="images-item-buttons flex jc-sb">
        <div class="images-item-selector"></div>

        <div class="flex">
            {if $image.extraData.update_link}
                <a class="images-list-update-item" href="{$image.extraData.update_link}"></a>
            {/if}
            <a class="images-list-remove-item" href="#" data-image-id="{$image.fields.id}"></a>
        </div>
    </div>
</div>
{/strip}