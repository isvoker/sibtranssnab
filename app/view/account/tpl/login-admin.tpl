{strip}
<form class="sensei-form sensei-form_horizontal sensei-form_small sensei-form_need-validation js__sensei-form"
      action="."
      method="post"
      data-controller="users"
      data-action="auth"
      data-logto="user-message">

    <div class="row">
        <div class="label">Логин</div>
        <div class="input">
            <input class="input_text js__input required sensitive"
                   type="text"
                   name="login"
                   required="required"
                   autofocus="autofocus"
                   data-fieldname="Логин"/>
        </div>
    </div>

    <div class="row">
        <div class="label">Пароль</div>
        <div class="input">
            <input class="input_text js__input required sensitive"
                   type="password"
                   name="password"
                   required="required"
                   autocomplete="off"
                   data-fieldname="Пароль"/>
        </div>
    </div>

    <div class="row">
        <div class="label"></div>
        <div class="input sensei-buttons">
            <button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit}"
                    type="submit">
                Войти
            </button>
            <a class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.color}"
               href="{$cfg.url.passwordReset}">
                Забыли пароль?
            </a>
        </div>
    </div>

</form>

<div id="user-message"></div>
{/strip}