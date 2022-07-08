{strip}
<footer class="footer">
    <div class="footer__top">
        <div class="container-fluid">
            <a class="footer__alternate-link" href="{$RTP.canonicalHref}">Полная версия</a>
            <div class="footer__title">
                {$HTML_BLOCK_FOOTER_TITLE}
            </div>
        </div>
    </div>
    <div class="footer__middle">
        <div class="container-fluid">
            <a class="footer__link flex ff-rn ai-c" href="https://web-ae.ru/" target="_blank">
                <span>Разработка сайта -</span>
                <img src="/static/img/footer-developer-logo.png" alt=""/>
            </a>
        </div>
    </div>
</footer>
<div class="bottom-buttons">
    <div class="container-fluid flex ff-rn jc-sa">
        {if $QuizWidget}
        <a class="button button--light bottom-buttons__button js__quiz-button" href="#">Заявка</a>
        {else}
        <a class="button button--light bottom-buttons__button  js__md_open" data-md="md-callback" href="#">Заявка</a>
        {/if}
        {if $contact_phone}
        <a class="button button--dark bottom-buttons__button" href="tel:{$contact_phone}">Позвонить</a>
        {/if}
    </div>
</div>
{include file="file:[main]modal-dialogs.inc.tpl"}
{/strip}