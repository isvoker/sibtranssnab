{strip}
<div id="history" style="visibility:hidden">
	<div class="easyui-accordion not-selected">
		<div title="Поиск" data-options="iconCls:'icon-search'">
			<form id="history__search-form"
				  class="sensei-form sensei-form_horizontal sensei-form_need-validation"
				  action="."
				  method="post">

				<div class="row">
					<div class="label">Найти</div>
					<div class="input">
						<input class="input_text js__input" type="text" name="query"/>
					</div>
				</div>

				<div class="row">
					<div class="label">За период</div>
					<div class="input">
						<input class="input_text twosome js__input ft_datetime" type="text" name="time_min"/>
						&ensp;–&ensp;<input class="input_text twosome js__input ft_datetime" type="text" name="time_max"/>
					</div>
				</div>

				<div class="row">
					<div class="label"></div>
					<div class="input sensei-buttons">
						<button type="button"
								class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} js__form-submit">
							Искать
						</button>
						<button type="button"
								class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.color} js__form-reset">
							Очистить
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<table id="history__table"></table>
</div>
{/strip}