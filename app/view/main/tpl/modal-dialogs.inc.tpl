{strip}
{$QuizWidget}
<div id="md-callback" class="modal-dialog js__modal-dialog feedback-form">
    <a class="md__close js__md_close" href="#"></a>
    <span class="md__title h1">Заявка на обратный звонок</span>
    <div class="md__content">
        <form class="simple-form js__form" data-action="callback" data-goal="send-callback-form" data-resetonsubmit="1">
            <div class="sf__field">
                <input class="sf__input js__input required" type="text" name="name" placeholder="Ф.И.О." />
            </div>
            <div class="sf__field">
                <input class="sf__input js__input js__mask-phone required" type="text" name="phone" placeholder="Тел." />
            </div>
            <div class="sf__field">
                <input class="sf__input js__input" type="text" name="email" placeholder="E-mail" />
            </div>
            <div class="sf__field">
                <textarea class="sf__textarea js__input" name="comment" placeholder="Сообщение"></textarea>
            </div>
            <div class="sf__footnote text-content">
                {include file="file:[special]agreement.inc.tpl"}
            </div>
            <div class="flex ff-rw jc-sa ai-c">
                <a class="button button--white js__md-form_submit">ОТПРАВИТЬ</a>
            </div>
        </form>
    </div>
</div>
{/strip}