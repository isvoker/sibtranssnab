CKEDITOR.editorConfig = function( config ) {
	config.language = 'ru';
	config.allowedContent = true;
	config.fillEmptyBlocks = true;

	config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre;div';

	config.height = '55vh';
    
    config.codemirror = {
		autoFormatOnStart: true,
		autoFormatOnModeChange: true,
		showTrailingSpace: false,
		theme: 'monokai'
	};

	config.toolbar = [
		{ name: 'document', items: [ 'Source' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items: [ 'Find', 'Replace', 'SelectAll' ] },
		{ name: 'links', items: [ 'Link', 'Unlink' ] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv'] },
		{ name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
		{ name: 'justify', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'styles', items: ['Format', 'FontSize' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'about', items: [ 'About' ] }
	];
};