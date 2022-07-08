{strip}
<div style="max-width:800px;padding:10px;margin:0 auto">
	<p style="margin:10px 0"><a style="color:{$theme_color_2};font-size:24px" href="{$host}">{$server_name}</a></p>
	<p style="color:#4C4C4C;font-size:16px;font-weight:700;margin:10px 0"><strong>{$site_name}</strong></p>
	<div style="background-color:{$theme_color_2};height: 3px;margin: 20px 0;"></div>

	<div style="color:#4C4C4C;font-size:14px;line-height:1.5;margin-bottom:20px;min-height: 200px">
		{if $template}
			{include file=$template}
		{else}
			{$message}
		{/if}
	</div>

	<div style="background:#444;padding:20px;color:#FFF;font-size:14px">
		Данное сообщение было сформировано автоматически, отвечать на него не нужно.<br/>
		<span style="font-size:18px;color:#FFF">{$site_name}</span><br/>
		{$site_copyright}
	</div>
</div>
{/strip}