{strip}
{if $SliderWidget}
    {$SliderWidget}
{elseif $main_banner}
    {include file="file:[special]main-banner.inc.tpl"}
{/if}

{$mini_banners}

<div>
    {$CatalogWidget}
    <div class="main__front-content text-content">
        {$text_content}
    </div>
    <section class="hero-section">
        <div id="bodyfon" data-vide-bg="static/img/video3.jpg">
            <div class="bgtexture">
                <div class="hero-slider owl-carousel">
                    <div class="hero-item">
                        <div class="hero-text">
                            <!-- <div class="ht-cata">СпецПредложение</div> -->
                            <h1 class="wraptexth2">СибТрансСнаб</h1>
                            <p><span class="wraptext">Услуги лазерной резки и гибки металла, полимерная краска.
								</span></p>
                            <p><span class="wraptext">Проектирование, производство и продажа оборудования и запасных
										частей для сельского хозяйства, промышленности и строительства.
									</span></p>

                            <a href="/products" class="ht-btn">Узнать больше... <i class="arrow_right"></i></a>
                        </div>
                        <div class="hi-bg bgindex"></div>
                    </div>
                    <div class="hero-item">
                        <div class="hero-text">
                            <div class="ht-cata">СпецПредложение</div>
                            <h2 class="wraptexth2">Зернометатели</h2>
                            <p><span class="wraptext">Изготовим зернометатель, любой конфигурации, подбор характеристик.
								</span></p>
                            <a href="/products" class="ht-btn">Узнать больше... <i class="arrow_right"></i></a>
                        </div>
                        <div class="hi-bg set-bg bgindex" data-setbg="/files/incoming/images/zernomet.png"></div>
                    </div>
                    <div class="hero-item">
                        <div class="hero-text">
                            <div class="ht-cata">СпецПредложение</div>
                            <h2 class="wraptexth2">Шнековые траспортёры</h2>
                            <p><span class="wraptext">Различные варианты.</span></p>
                            <a href="/products" class="ht-btn">Читать <i class="arrow_right"></i></a>
                        </div>
                        <div class="hi-bg set-bg bgindex" data-setbg="/files/incoming/images/shnek.png"></div>
                    </div>
                    <div class="hero-item">
                        <div class="hero-text">
                            <div class="ht-cata">СпецПредложение</div>
                            <h2 class="wraptexth2">Мангалы, Дровники</h2>
                            <p><span class="wraptext">На заказ, любой дизайн. Покраска. Гарантия.</span></p>
                            <a href="/products" class="ht-btn">Заказать <i class="arrow_right"></i></a>
                        </div>
                        <div class="hi-bg set-bg bgindex" data-setbg="/files/incoming/images/mangal.png"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {$NewsWidget}
    {$FAQWidget}
	{$ReviewsWidget}
</div>
{/strip}