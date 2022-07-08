{strip}
{foreach from=$relationships key=entity item=rel}
<div class="row">
	<div class="label group-title">{$rel.name}</div>
	<div class="input columns">
		<input class="rel-objects-search" type="text" name="__vis_search" maxlength="255" data-entity="{$entity}"/>
		<div class="text-note">Воспользуйтесь поиском</div>
		<table class="sensei-form-table col-6 columns rel-objects-table">
			<tbody>
				{foreach $rel.objects as $Obj}
				<tr>
					<th class="col-1">
						<input class="js__input input_checkbox" type="checkbox" name="rel_with[{$entity}]" value="{$Obj.fieldsForOutput.id}" checked="checked"/>
					</th>
					<td class="col-11">{$Obj.fieldsForOutput.name}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
{/foreach}
{/strip}