{strip}
<form class="user-form sensei-form_need-validation js__sensei-form"
      action="."
      method="post"
      data-controller="users"
      data-action="passwordResetRequest"
      data-logto="user-message">

    <div class="user-form__row">
        <div class="user-form__label marked">Логин</div>
        <input class="user-form__input js__input required sensitive"
               type="text"
               name="login"
               required="required"
               autofocus="autofocus"
               data-fieldname="Логин"/>
    </div>

    <div class="user-form__row">
        <div class="user-form__label marked">Проверочный код</div>
        {include file="file:[special]captcha.inc.tpl"}
    </div>

    <div class="user-form__row">
        <button type="submit" class="button button--dark">Отправить</button>
        <a class="link" href="{$cfg.url.login}">На страницу входа</a>
    </div>
</form>

<div id="user-message"></div>
{/strip}