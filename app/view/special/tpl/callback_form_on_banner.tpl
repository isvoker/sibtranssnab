{strip}
<div class="callback-form-on-banner js__form">
    <div class="callback-form-on-banner__container">
        <span class="callback-form-on-banner__title">Заявка на обратный звонок</span>
        <form class="simple-form js__form" data-action="callback_on_banner" data-goal="send-callback-form" data-resetonsubmit="1">
            <div class="sf__field">
                <input class="sf__input js__input required" type="text" name="name" placeholder="Ф.И.О." />
            </div>
            <div class="sf__field">
                <input class="sf__input js__input required" type="text" name="phone" placeholder="Тел." />
            </div>
            <div class="sf__field">
                <input class="sf__input js__input" type="text" name="email" placeholder="E-mail" />
            </div>
            <div class="sf__field">
                <textarea class="sf__textarea js__input" name="comment" placeholder="Сообщение"></textarea>
            </div>
            <div class="sf__footnote">
                {include file="file:[special]agreement.inc.tpl"}
            </div>
            <div class="sf__buttons flex ff-rw jc-sa ai-c">
                <a class="button button--white sf__button js__submit-form">ОТПРАВИТЬ</a>
            </div>
        </form>
    </div>
</div>
{/strip}