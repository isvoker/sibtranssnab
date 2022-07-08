{strip}
    <nav role='navigation' class="navbar navbar-expand-md navbar-light fixed-top justify-content-end" id="navbar">
        <div class="collapse navbar-collapse justify-content-end" id="navbarToggler">
            <ul class="navbar-nav">
                {if $show_front}
                    <li class="nav-item">
                        <div class="flip">
                            <a href="/">
                                <div class="front">Главная</div>
                                <div class="back">Главная</div>
                            </a>
                        </div>
                    </li>
                {/if}
                {foreach $menu as $item}

                    <li class="nav-item {if $item.active}active{/if}" >
                        <div class="flip">
                            {if $item.href === '/services/'}
                                <a href="#">
                                    <div class="front">{$item.name}▼</div>
                                    <div class="back">{$item.name}▼</div>
                                </a>
                                {else}
                                <a href="{$item.href}">
                                    <div class="front">{$item.name}</div>
                                    <div class="back">{$item.name}</div>
                                </a>
                            {/if}

                            {if $item.sub_menu and $depth - 1}
                                {include file='file:[menu]MainNavigator.inc.tpl' menu=$item.sub_menu}
                            {/if}
                        </div>
                    </li>
                {/foreach}
            </ul>
        </div>
    </nav>

{/strip}