{strip}
<form class="user-form sensei-form_need-validation js__sensei-form"
      action="."
      method="post"
      data-controller="users"
      data-action="passwordReset"
      data-logto="user-message">

    <input class="js__input" type="hidden" name="token" value="{$token}"/>

    <div class="user-form__row">
        <div class="user-form__label marked">Новый пароль</div>
        <input class="user-form__input js__input required sensitive"
               type="password"
               name="password"
               required="required"
               autocomplete="off"
               autofocus="autofocus"
               data-fieldname="Новый пароль"/>
    </div>

    <div class="user-form__row">
        <button type="submit" class="button button--dark">Отправить</button>
    </div>
</form>

<div id="user-message"></div>
{/strip}