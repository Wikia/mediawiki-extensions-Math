/*!
 * VisualEditor ContentEditable MWMathNode class.
 *
 * @copyright 2011-2015 VisualEditor Team and others; see AUTHORS.txt
 * @license The MIT License (MIT); see LICENSE.txt
 */

/**
 * ContentEditable MediaWiki math node.
 *
 * @class
 * @extends ve.ce.MWInlineExtensionNode
 *
 * @constructor
 * @param {ve.dm.MWMathNode} model Model to observe
 * @param {Object} [config] Configuration options
 */
ve.ce.MWMathNode = function VeCeMWMathNode() {
	// Parent constructor
	ve.ce.MWMathNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ce.MWMathNode, ve.ce.MWLatexNode );

/* Static Properties */

ve.ce.MWMathNode.static.name = 'mwMath';

ve.ce.MWMathNode.static.primaryCommandName = 'mathDialog';

ve.ce.MWMathNode.static.iconWhenInvisible = 'math';

/* Registration */

ve.ce.nodeFactory.register( ve.ce.MWMathNode );
