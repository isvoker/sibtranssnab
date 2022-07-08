{strip}
<header class="header">
    <div class="header__top">
        <div class="container-fluid flex ff-rn jc-sa ai-c">
            {if $MainNavigator}
            <div class="f-00">
                <a class="header__menu-button js__toggle-menu-layer" href="#"><i></i></a>
            </div>
            {/if}
            <div class="header__info f-11">
                {$HTML_BLOCK_HEADER_TITLE}
            </div>
        </div>
    </div>

    {$MainNavigator}

    <div class="header__bottom container-fluid">
        <div class="flex ff-rw jc-sb">
            {if $HTML_BLOCK_ADDRESS}
            <div class="header__contacts flex ff-rn ai-c">
                <div class="header__icon header__icon--location f-00"></div>
                <div class="text-content">
                    {$HTML_BLOCK_ADDRESS}
                </div>
            </div>
            {/if}

            {if $HTML_BLOCK_PHONES}
            <div class="header__contacts flex ff-rn ai-c">
                <div class="header__icon header__icon--phone f-00"></div>
                <div class="text-content">
                    {$HTML_BLOCK_PHONES}
                </div>
            </div>
            {/if}
        </div>
        <div class="flex ff-rn jc-sa">
            {$ToponymsWidget}
            {$OrdersWidget}
            {$header_login}
        </div>
        <a class="button button--light button__fw header__feedback-btn  js__md_open" data-md="md-callback" href="#">ЗАКАЗАТЬ ЗВОНОК</a>
    </div>
</header>
{/strip}