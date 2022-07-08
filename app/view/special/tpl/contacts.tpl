{strip}
    <div class="text-content js__tabs">
        <div class="nav nav-tabs nav-justified">
            <a class="nav-link js__tab-header active" >Офис</a>
            <a class="nav-link js__tab-header" >Производство</a>
        </div>

        <div class="tab-pane js__tab-content active" id="ofice">
            <section class="contact-section">
                <div class="contact-warp">
                    <!-- <div class="contact-warp set-bg" data-setbg="img/blog/big_.jpg"> -->
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="contact-info"><h4>Контактная информация</h4>
                                    <div class="ci-item"><i class="icon_phone"></i>+7 (3852) 60-90-30</div>
                                    <div class="ci-item"><i class="icon_mail"></i>info@sts22.ru</div>
                                </div>
                                <div class="contact-info"><h4>Адрес</h4>
                                    <div class="ci-item"><i class="icon_pin"></i>656058, Алтайский край, г.
                                        Барнаул, ул.Малахова, 153Б, 4 этаж
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                {if $HTML_BLOCK_CONTACT_MAP}
                                    <div class="contacts-map">
                                        {$HTML_BLOCK_CONTACT_MAP}
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="tab-pane js__tab-content" id="proizv">
            <section class="contact-section">
                <div class="contact-warp set-bg" data-setbg="img/blog/big_.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="contact-info"><h4>Контакты производства</h4>
                                <div class="ci-item"><i class="icon_phone"></i>+7 (3852) 60-99-53</div>
                                <div class="ci-item"><i class="icon_mail"></i>info@laserr.ru</div>
                            </div>
                            <div class="contact-info"><h4>Адрес</h4>
                                <div class="ci-item"><i class="icon_pin"></i>656012, Алтайский край, г. Барнаул,
                                    ул.Бриллиантовая 2а
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <script type="text/javascript" charset="utf-8" async
                                    src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A73aeee25533e83f5c787126dc69c8ea27dce33dc868414d5bb53ca588091db5e&amp;width=500&amp;height=400&amp;lang=ru_RU&amp;scroll=false"></script>
                        </div>
                    </div>
                </div>
                </div>
            </section>
        </div>

    <section class="contact-section" id="feedback">
        <div class="contact-warp set-bg" data-setbg="/files/incoming/images/blog/bgcontact.jpg" alt="контакты">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-6">
                        {include file="file:[special]callback-form-static.inc.tpl"}
                    </div>
                    <div class="col-md-offset-1 col-md-1"></div>
                    <div class="col-5 divnone">
                        <div class="ai-text"><h4>ООО "СибТрансСнаб"</h4>
                            <p>Наша задача действовать оперативно и гибко, стараясь предвосхищать потребности наших
                                клиентов, при этом добиваться высочайшего качества предоставляемых нами услуг и
                                выпускаемой продукции.</p>
                            <p>За 10 лет работы мы четко выстроили процесс производства, с точным контролем каждой
                                операции.</p>
                            <p>Применение самого современного оборудования и разработки собственного
                                конструкторского отдела позволяют нам быстро разрабатывать проект и качественно
                                производить как стандартное, так и уникальное оборудование, соответствующее
                                современным требованиям.</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
{/strip}