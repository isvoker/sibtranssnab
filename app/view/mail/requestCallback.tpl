{strip}
<table style="border:0;border-collapse:collapse;margin:30px 0;width:100%">
    <tr>
        <td style="border:0;padding:10px 0;width:300px">Тема сообщения:</td>
        <td style="border:0;padding:10px 0;font-weight:700">{$data.subject}</td>
    </tr>
    <tr>
        <td style="border:0;padding:10px 0">Дата сообщения:</td>
        <td style="border:0;padding:10px 0;font-weight:700">{$data.time}</td>
    </tr>
    <tr>
        <td style="border:0;padding:10px 0">Имя отправителя:</td>
        <td style="border:0;padding:10px 0;font-weight:700">{$data.name}</td>
    </tr>
    {if $data.phone}
        <tr>
            <td style="border:0;padding:10px 0">Номер телефона отправителя:</td>
            <td style="border:0;padding:10px 0;font-weight:700">{$data.phone}</td>
        </tr>
    {/if}
    {if $data.email}
    <tr>
        <td style="border:0;padding:10px 0">Е-mail отправителя:</td>
        <td style="border:0;padding:10px 0;font-weight:700">
            <a href="mailto:{$data.email}" style="color:{$theme_color_2}">{$data.email}</a>
        </td>
    </tr>
    {/if}
    {if $data.comment}
    <tr>
        <td style="border:0;padding:10px 0">Текст сообщения:</td>
        <td style="border:0;padding:10px 0;font-weight:700">{$data.comment}</td>
    </tr>
    {/if}
</table>
{/strip}