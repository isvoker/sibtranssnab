{strip}
<div class="sensei-form sensei-form_horizontal">
	{foreach $props as $prop}
	<div class="row">
		<div class="label">{$prop.name}</div>
		<div class="input">{$prop.value}</div>
	</div>
	{/foreach}
</div>
{/strip}