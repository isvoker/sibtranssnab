{strip}
<table class="sensei-table columns">
	<thead>
		<tr>
			<td class="col-1">Операции</td>
			<td class="col-3">Идентификатор</td>
			<td class="col-3">Описание</td>
			<td class="col-5">Содержимое блока</td>
		</tr>
	</thead>
	<tbody>
		{foreach $textBlocks as $tb}
		<tr>
			<td>{$tb.edit_btns}</td>
			<td>{$tb.ident}</td>
			<td>{$tb.description}</td>
			<td>{$tb.content}</td>
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