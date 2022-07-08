if ($.fn.pagination){
	$.fn.pagination.defaults.beforePageText = 'Стр.';
	$.fn.pagination.defaults.afterPageText = 'из {pages}';
	$.fn.pagination.defaults.displayMsg = 'Записи с {from} до {to} из {total}';
}
if ($.fn.datagrid){
	$.fn.datagrid.defaults.loadMsg = 'Запрос обрабатывается, ждите ...';
}
if ($.fn.treegrid && $.fn.datagrid){
	$.fn.treegrid.defaults.loadMsg = $.fn.datagrid.defaults.loadMsg;
}
if ($.messager){
	$.messager.defaults.ok = 'Ок';
	$.messager.defaults.cancel = 'Отмена';
}
$.map(['validatebox','textbox','passwordbox','filebox','searchbox',
		'combo','combobox','combogrid','combotree',
		'datebox','datetimebox','numberbox',
		'spinner','numberspinner','timespinner','datetimespinner'], function(plugin){
	if ($.fn[plugin]){
		$.fn[plugin].defaults.missingMessage = 'Это поле обязательно для заполнения.';
	}
});
if ($.fn.validatebox){
	$.fn.validatebox.defaults.rules.email.message = 'Введите корректный адрес электронной почты.';
	$.fn.validatebox.defaults.rules.url.message = 'Введите корректный URL.';
	$.fn.validatebox.defaults.rules.length.message = 'Введите значение между {0} и {1}.';
	$.fn.validatebox.defaults.rules.remote.message = 'Исправьте это поле.';
}
if ($.fn.calendar){
	$.fn.calendar.defaults.firstDay = 1;
	$.fn.calendar.defaults.weeks = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];
	$.fn.calendar.defaults.months = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
}
if ($.fn.datebox){
	$.fn.datebox.defaults.currentText = 'Сегодня';
	$.fn.datebox.defaults.closeText = 'Закрыть';
	$.fn.datebox.defaults.okText = 'Ок';
}
if ($.fn.datetimebox && $.fn.datebox){
	$.extend($.fn.datetimebox.defaults,{
		currentText: $.fn.datebox.defaults.currentText,
		closeText: $.fn.datebox.defaults.closeText,
		okText: $.fn.datebox.defaults.okText
	});
}
if ($.fn.etree){
	$.extend($.fn.etree.defaults,{
		editMsg:{
			norecord:{
				title: 'Предупреждение',
				msg: 'Ничего не выбрано.'
			}
		},
		destroyMsg:{
			norecord:{
				title: 'Предупреждение',
				msg: 'Ничего не выбрано.'
			},
			confirm:{
				title: 'Подтвердите',
				msg: 'Вы действительно хотите удалить элемент?'
			}
		}
	});
}