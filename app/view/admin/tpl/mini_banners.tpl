{strip}
<div class="sensei-message sensei-message_info">Все изображения должны быть квадратные 250x250 px, иначе они обрежутся!</div>
<table class="sensei-table columns">
	<thead>
		<tr>
			<td class="col-1">Операции</td>
			<td class="col-2">Изображение</td>
			<td class="col-4">Заголовок</td>
			<td class="col-5">Ссылка</td>
		</tr>
	</thead>
	<tbody>
		{foreach $mini_banners as $mb}
		<tr>
			<td>{$mb.extraData.edit_btns}</td>
			<td style="text-align:center">
				<img src="{$mb.extraData.image_resized}" alt="{$mb.fieldsForOutput.title}">
			</td>
			<td>{$mb.fieldsForOutput.title}</td>
			<td>{$mb.fieldsForOutput.url}</td>
		</tr>
		{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4">{$insBtn}</td>
		</tr>
	</tfoot>
</table>
{$paginator}
{/strip}