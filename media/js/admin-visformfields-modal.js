/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	"use strict";
	/**
	 * Javascript to insert the link
	 * View element calls jSelectVisformfields when an Visformfield is clicked
	 * jSelectVisformfields creates the link tag, sends it to the editor,
	 * and closes the select frame.
	 **/
	window.jSelectVisformfield = function (field, editor) {

		if (!Joomla.getOptions('xtd-visformfields')) {
			// Something went wrong!
			window.parent.jModalClose();
			return false;
		}

		editor = Joomla.getOptions('xtd-visformfields').editor;

        var tag = '${' + field + '}';
		/** Use the API, if editor supports it **/
		if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
			window.parent.Joomla.editors.instances[editor].replaceSelection(tag)
		} else {
			window.parent.jInsertEditorText(tag, editor);
		}

		window.parent.jModalClose();
	};
})();
