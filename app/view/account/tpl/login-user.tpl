{strip}
<form class="user-form js__sensei-form"
      action="."
      method="post"
      data-controller="users"
      data-action="auth"
      data-logto="user-message">

    <div class="user-form__row">
        <span class="user-form__label">Логин или email:</span>
        <input class="user-form__input js__input required sensitive" type="text" name="login" data-fieldname="Логин"/>
    </div>

    <div class="user-form__row">
        <span class="user-form__label">Пароль:</span>
        <input class="user-form__input js__input required sensitive" type="password" name="password" data-fieldname="Пароль" />
    </div>

    <div class="user-form__row">
        <button class="button button--dark" type="submit">Войти</button>
    </div>

    <div class="user-form__row">
        <a class="link user-form__link" href="{$cfg.url.signup}">Регистрация</a>
        <a class="link user-form__link" href="{$cfg.url.passwordReset}">Забыли пароль?</a>
    </div>
</form>

<div id="user-message"></div>
{/strip}