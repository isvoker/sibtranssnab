{strip}
<form class="user-form sensei-form_need-validation js__sensei-form"
      action="."
      method="post"
      data-controller="users"
      data-action="singup"
      data-logto="user-message">

    <div class="user-form__row">
        <div class="user-form__label">Логин:</div>
        <input class="user-form__input js__input required sensitive"
               type="text"
               name="login"
               required="required"
               autofocus="autofocus"
               data-fieldname="Логин"/>
    </div>

    <div class="user-form__row">
        <div class="user-form__label">Email:</div>
        <input class="user-form__input js__input required sensitive"
               type="email"
               name="email"
               required="required"
               data-fieldname="Email"/>
    </div>

    <div class="user-form__row">
        <div class="user-form__label">Имя пользователя:</div>
        <input class="user-form__input js__input required sensitive"
               type="text"
               name="name"
               required="required"
               data-fieldname="Имя" />
    </div>

    <div class="user-form__row">
        <div class="user-form__label">Пароль:</div>
        <input class="user-form__input js__input required sensitive"
               type="password"
               name="password"
               data-fieldname="Пароль"/>
    </div>

    <div class="user-form__row">
        <div class="user-form__label">Проверочный код:</div>
        {include file="file:[special]captcha.inc.tpl"}
    </div>

    <div class="user-form__row">
        {include file="file:[special]agreement.inc.tpl"}
    </div>
    <div class="user-form__row">
        <button type="submit"
                class="button button--dark sensei-form__button">Зарегистрироваться</button>
    </div>
    <div class="user-form__row">
        <a class="link" href="{$cfg.url.login}">Уже есть учётная запись?</a>
    </div>
</form>

<div id="user-message"></div>
{/strip}