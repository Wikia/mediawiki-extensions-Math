/*!
 * VisualEditor UserInterface MWLatexInspector class.
 *
 * @copyright 2011-2015 VisualEditor Team and others; see AUTHORS.txt
 * @license The MIT License (MIT); see LICENSE.txt
 */

/**
 * MediaWiki abstract inspector for quick editing of different formulas
 * that the Math extension provides.
 *
 * @abstract
 * @class
 * @extends ve.ui.MWLiveExtensionInspector
 *
 * @constructor
 * @param {Object} [config] Configuration options
 */
ve.ui.MWLatexInspector = function VeUiMWLatexInspector( config ) {
	// Parent constructor
	ve.ui.MWLatexInspector.super.call( this, config );
};

/* Inheritance */

OO.inheritClass( ve.ui.MWLatexInspector, ve.ui.MWLiveExtensionInspector );

/* Static properties */

ve.ui.MWLatexInspector.static.dir = 'ltr';

/* Methods */

/**
 * @inheritdoc
 */
ve.ui.MWLatexInspector.prototype.initialize = function () {
	var inputField, displayField, idField;

	// Parent method
	ve.ui.MWLatexInspector.super.prototype.initialize.call( this );

	this.displaySelect = new OO.ui.ButtonSelectWidget( {
		items: [
			new OO.ui.ButtonOptionWidget( {
				data: 'default',
				icon: 'math-display-default',
				label: ve.msg( 'math-visualeditor-mwlatexinspector-display-default' )
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'inline',
				icon: 'math-display-inline',
				label: ve.msg( 'math-visualeditor-mwlatexinspector-display-inline' )
			} ),
			new OO.ui.ButtonOptionWidget( {
				data: 'block',
				icon: 'math-display-block',
				label: ve.msg( 'math-visualeditor-mwlatexinspector-display-block' )
			} )
		]
	} );

	this.idInput = new OO.ui.TextInputWidget();

	inputField = new OO.ui.FieldLayout( this.input, {
		align: 'top',
		label: ve.msg( 'math-visualeditor-mwlatexdialog-card-formula' )
	} );
	displayField = new OO.ui.FieldLayout( this.displaySelect, {
		align: 'top',
		label: ve.msg( 'math-visualeditor-mwlatexinspector-display' )
	} );
	idField = new OO.ui.FieldLayout( this.idInput, {
		align: 'top',
		label: ve.msg( 'math-visualeditor-mwlatexinspector-id' )
	} );

	// Initialization
	this.$content.addClass( 've-ui-mwLatexInspector-content' );
	this.form.$element.append(
		inputField.$element,
		this.generatedContentsError.$element,
		displayField.$element,
		idField.$element
	);
};

/**
 * @inheritdoc
 */
ve.ui.MWLatexInspector.prototype.getSetupProcess = function ( data ) {
	return ve.ui.MWLatexInspector.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			var display = this.selectedNode.getAttribute( 'mw' ).attrs.display || 'default';
			this.displaySelect.selectItemByData( display );
			this.displaySelect.on( 'choose', this.onChangeHandler );
		}, this );
};

/**
 * @inheritdoc
 */
ve.ui.MWLatexInspector.prototype.getTeardownProcess = function ( data ) {
	return ve.ui.MWLatexInspector.super.prototype.getTeardownProcess.call( this, data )
		.first( function () {
			this.displaySelect.off( 'choose', this.onChangeHandler );
		}, this );
};

/**
 * @inheritdoc
 */
ve.ui.MWLatexInspector.prototype.updateMwData = function ( mwData ) {
	var display, id;

	// Parent method
	ve.ui.MWLatexInspector.super.prototype.updateMwData.call( this, mwData );

	display = this.displaySelect.findSelectedItem().getData();
	id = this.idInput.getValue();

	mwData.attrs.display = display !== 'default' ? display : undefined;
	mwData.attrs.id = id || undefined;
};

/**
 * @inheritdoc
 */
ve.ui.MWLatexInspector.prototype.formatGeneratedContentsError = function ( $element ) {
	return $element.text().trim();
};
