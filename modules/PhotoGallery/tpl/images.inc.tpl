{strip}
{foreach $galleryImages as $imageItem}
<a class="photogallery-photo-item" data-fancybox="PhotoGallery" data-caption="{$imageItem.fields.caption}" href="{$imageItem.fields.img_path}">
    <div class="photogallery-photo-item-image" style="background-image:url('{$imageItem.extraData.img_th_path}')"></div>
    {if $imageItem.fields.caption}
    <div class="photogallery-photo-item-title">{$imageItem.fields.caption}</div>
    {/if}
</a>
{/foreach}
{/strip}