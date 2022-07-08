{strip}
<div class="front-banners">
	<div class="container">
		{$insBtn}
		<div class="flex ff-rw jc-sa ai-fs">
			{foreach $mini_banners as $banner}
			<div class="front-banners__item">
				<div class="front-banners__item--container">
					<img class="front-banners__item--image" src="{$banner.extraData.image_resized}" alt="{$banner.fieldsForOutput.title}"/>
					{if $banner.fieldsForOutput.url}
					<a class="front-banners__item--link"
					   href="{$banner.fieldsForOutput.url}"
					   {if $banner.extraData.link_is_image} data-fancybox='front-images'{/if}
					   {if $banner.fields.is_target_blank} target="_blank"{/if}>
					</a>
					{/if}
				</div>

				{if $banner.fieldsForOutput.title}
				<div class="front-banners__item--title">{$banner.fieldsForOutput.title}</div>
				{/if}

				{if $banner.fieldsForOutput.description}
				<div class="front-banners__item--descr text-content">
					{$banner.fieldsForOutput.description}
				</div>
				{/if}
				{$banner.extraData.edit_btns}
			</div>
			{/foreach}
		</div>
	</div>
</div>
{/strip}