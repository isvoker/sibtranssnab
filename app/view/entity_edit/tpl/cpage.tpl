{strip}
<div class="row">
	<div class="label group-title">Настройки доступа</div>
	<div class="input columns">
		<table class="sensei-form-table col-6 columns">
			<thead>
				<tr>
					<th class="col-6">Группа</th>
					{foreach $permsInfo as $permName}
					<th class="col-3">{$permName}</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach $allUserGroups as $grp}
				<tr>
					<th>{$grp.description}</th>
					{foreach from=$permsInfo key=permCode item=permName}
					<td>
						<input class="js__input input_checkbox" type="checkbox" name="perms_{$grp.id}[]" value="{$permCode}"
							{if $curPerms[$grp.id] and $curPerms[$grp.id] is div by $permCode} checked="checked"{/if}/>
					</td>
					{/foreach}
				</tr>
				{/foreach}
			</tbody>
		</table>

		<div class="text-note">Для администратора разрешение чтения определяет наличие страницы в меню и НЕ влияет на доступность.</div>
	</div>
</div>
{/strip}