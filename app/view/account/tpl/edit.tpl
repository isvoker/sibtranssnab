{strip}
<form class="user-form js__sensei-form"
      action="."
      method="post"
      data-controller="users"
      data-action="updateAccount"
      data-logto="user-message">

    <div class="user-form__row">
        <span class="user-form__label">Логин:</span>
        <input class="user-form__input js__input required sensitive"
               type="text"
               name="login"
               value="{$user.login}" data-fieldname="Логин"/>
    </div>
    <div class="user-form__row">
        <span class="user-form__label">Эл. почта:</span>
        <input class="user-form__input js__input required sensitive"
               type="email"
               name="email"
               value="{$user.email}" data-fieldname="Email"/>
    </div>
    <div class="user-form__row">
        <span class="user-form__label">Имя пользователя:</span>
        <input class="user-form__input js__input required sensitive"
               type="text"
               name="name"
               value="{$user.name}" data-fieldname="Имя пользователя"/>
    </div>

    <div class="user-form__row">
        <span class="user-form__label">Актуальный пароль (не отображается):</span>
        <input class="user-form__input js__input sensitive"
               type="password"
               name="password"
               autocomplete="off"
               data-fieldname="Пароль"/>
        <div class="text-note">Для смены логина, почты или пароля необходимо ввести нынешний пароль.</div>
    </div>

    <div class="user-form__row">
        <span class="user-form__label">Новый пароль:</span>
        <input class="user-form__input js__input sensitive"
               type="password"
               name="new_password"
               autocomplete="off"
               data-fieldname="Новый пароль"/>
        <div class="text-note">
            <strong>Обратите внимание:</strong>
            <ul style="padding-left:12px">
                <li>* минимальная длина пароля — 8 символов;</li>
                <li>* пароль не может состоять только из цифр;</li>
                <li>* пароль не должен быть похож на логин или email;</li>
                <li>* крайне не рекомендуется использовать одинаковые пароли в разных аккаунтах, соблюдайте правило "Один аккаунт — один пароль".</li>
            </ul>
        </div>
    </div>

    <div class="user-form__row">
        <button type="submit" class="button button--dark">Сохранить</button>
    </div>
</form>

<div id="user-message"></div>
{/strip}