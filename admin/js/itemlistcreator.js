/**
 *------------------------------------------------------------------------------
 *  com_visforms by vi-solutions for Joomla! 3.x
 *------------------------------------------------------------------------------
 * @package     com_visforms
 * @copyright   Copyright (c) 2014 vi-solutions. All rights preserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Aicha Vack
 * @link        http://www.vi-solutions.de
 *
 * @version     1.0.0 2014-04-20
 * @since       1.0
 *------------------------------------------------------------------------------
 */

(function ($) {
    //public plugin functions go in there
    $.extend($.fn, {
        createVisformsOptionCreator: function (options) {
            var visformsOptionCreator = $.data(this[0], "visformsOptionCreator");
            if (visformsOptionCreator) {
                return visformsOptionCreator;
            }
            visformsOptionCreator = new $.visformsOptionCreator(options);
            $.data(this[0], "visformsOptionCreator", visformsOptionCreator);
        },
        storeVisformsOptionCreatorData: function(){
            var visformsOptionCreator = $.data(this[0], "visformsOptionCreator");
            visformsOptionCreator.storeData();
        },

    });
    $.visformsOptionCreator = function (options) {
        var defaults = {
            texts: {
                txtMoveUp: "Move Up",
                txtMoveDown: "Move Down",
                txtMoveDragDrop: "Move with drag and drop",
                txtDelete: "Delete",
                txtAddItem: "Add item",
                txtAddAndNewItem: "Add & New",
                txtCreateItem: "Create item",
                txtAlertRequired: "Value and Label are required",
                txtTitle: "Title",
                txtItemsImported: "Items imported",
                txtReaderError: "Unable to read file ",
                txtNoDataToImport: "No data to import!",
                txtDescr: ""
            },
            params: {
                //HTML field id and names and parameter names in db depend on structure of xml file...
                //name attribute of field that will contain the items string (from xml file)
                //this name is composed of f_ plus ctype + plus _list_hidden as a concention but you may change the _list_hidden Extension with dbFieldExt option
                //ctype is an important option which will control the plugin
                fieldName: 'f_radio_list_hidden',
                //Prefix of field ids (from fieldset name in xml file)
                idPrefix: "",
                //as a convention, this should be _list_hidden (is used to detemine ctype
                dbFieldExt: "",
                //list of fields for the popup
                //each field has the parmaters frequired (true/false), ftype (text/checkbox), fname (field name in json object, stored in db),
                //flabel (field label in json object, stored in db)
                hdnMFlds: {},
                ctype: "",
                header: "",
                items: "",
                rowTemplate: "",
                storedFieldClass: [],

            }
        };
        //use true as first paremeter to merge objects recursivly
        var settings = $.extend(true, {}, defaults, options);
        //create an array with field names
        var hdnMFldNames = $.map(settings.params.hdnMFlds, function (n, i) {
            return n.fname;
        });
        //create an array with names of required fields
        var requiredFields = $.map(settings.params.hdnMFlds, function (n, i) {
            if (n.frequired == true) return n.fname;
        });
        var ctype = getCType();
        var dbFieldName = settings.params.idPrefix + settings.params.fieldName;
        var importField = settings.params.idPrefix + "f_" + ctype + settings.params.importField;
        var importSeparator = settings.params.idPrefix + "f_" + ctype + settings.params.importSeparator;
        var addButtonId = "add" + ctype;
        var itemListContId = "itemListCont" + ctype;
        var itemListId = "itemList" + ctype;
        var idFieldName = settings.params.idPrefix + "f_" + ctype + '_lastId';
        var lastId = $('#' + idFieldName).val();

        var sortableClass = ($().sortable) ? 'ui-sortable' : 'notSortable';

        function getCType() {
            var ctype = "";
            if (settings.params.ctype == "") {
                var leftTrimmed = settings.params.fieldName.replace("f_", "");
                ctype = leftTrimmed.replace(settings.params.dbFieldExt, "");
            }
            else {
                ctype = settings.params.ctype;
            }
            return ctype;
        }

        var optionsTable = {
            createTable: function () {
                var html = '<div id="' + itemListContId + '">' +
                    '<div><p><a id="' + addButtonId + '" class="btn" href="#">' + settings.texts.txtCreateItem + '</a></p>' +
                    ((settings.texts.txtDescr) ? '<p>'+settings.texts.txtDescr + '</p>' : '') +
                    '<table id="' + itemListId + '" class="'+sortableClass+' table table-striped table-condensed" style="position:relative;"><thead>' + settings.params.header + '</thead><tbody>' + settings.params.items + '</tbody></table></div>';
                $(html).insertBefore('#' + dbFieldName);
                // necessary in order to create tooltips on heaeder
                $('#' + itemListContId).trigger("subform-row-add", $('#' + itemListId));
            },
            createTableRow: function () {
                var row = $(settings.params.rowTemplate).appendTo('#' + itemListId + ' tbody');
                row.find('.itemUp').on('click', optionsTable.itemUp);
                row.find('.itemDown').on('click', optionsTable.itemDown);
                row.find('input.focus').focus();
            },
            //enable itemUp/itemDown arrow with CSS; always use first before setting class disabled on first and last child
            removeArrowClassDisabled: function () {
                $("#" + itemListId + " .liItem").find(".itemUp").removeClass("disabled");
                $("#" + itemListId + " .liItem").find(".itemDown").removeClass("disabled")
            },
            setArrowClassDisabled: function () {
                $("#" + itemListId + " .liItem").first().find(".itemUp").addClass("disabled");
                $("#" + itemListId + " .liItem").last().find(".itemDown").addClass("disabled");
            },
            setArrowDisabledState: function () {
                optionsTable.removeArrowClassDisabled();
                optionsTable.setArrowClassDisabled();
            },
            itemUp: function () {
                $(this).parents('.liItem').insertBefore($(this).parents('.liItem').prev());
                optionsTable.setArrowDisabledState();
            },
            itemDown: function () {
                $(this).parents('.liItem').insertAfter($(this).parents('.liItem').next());
                optionsTable.setArrowDisabledState();
            },
            setIds: function () {
                $("#" + itemListId + " .listitemid").each(function (i) {
                    if ($(this).val() == "") {
                        $(this).val(++lastId);
                    }
                });
            },
            setLastId: function () {
                $('#' + idFieldName).val(lastId);
            },
            setCountOfDefaultOptions: function () {
                var c = $("#" + itemListId + " .listitemischecked:checked").length;
                $("#" + settings.params.idPrefix + "f_" + ctype + "_countDefaultOpts").val(c);
            },
            checkRequiredFields: function () {
                var valid = true;
                $.each(requiredFields, function (key, value) {
                    $("." + value).each(function (i) {
                        if ($(this).val() == "") {
                            valid = false;
                        }
                    });
                });
                if (valid == false) {
                    alert(settings.texts.txtAlertRequired);
                }
                return valid;
            },
            createHiddenInput: function () {
                var itemsObj = {};
                var $rows = $("#" + itemListId + " .liItem").not('.listHeader').each(function (i) {
                    var itemObj = {};
                    var inputs = $(this).find("input").each(function (idx) {
                        var input = this;
                        $.each(hdnMFldNames, function (key, value) {
                            if ($(input).hasClass(value)) {
                                if ($(input).attr('type') === 'checkbox') {
                                    if ($(input).prop('checked')) {
                                        itemObj[value] = '1';
                                    }
                                }
                                else {
                                    itemObj[value] = $(input).val();
                                }
                            }
                        });
                    });
                    itemsObj[i] = itemObj;
                });
                return JSON.stringify(itemsObj);
            },
            setItemsStr: function (itemsStr) {
                if (itemsStr != "") {
                    $("#" + dbFieldName).val(itemsStr);
                }
            },
            browserSupportFileUpload: function () {
                var isCompatible = false;
                if (window.File && window.FileReader && window.FileList && window.Blob) {
                    isCompatible = true;
                }
                return isCompatible;
            },
            // Method that reads and processes the selected file
            importOptions: function (evt) {
                if (!optionsTable.browserSupportFileUpload()) {
                    alert('The File APIs are not fully supported in this browser!');
                }
                else {
                    var data = null;
                    var file = evt.target.files[0];
                    var reader = new FileReader();
                    reader.readAsText(file);
                    reader.onload = function (event) {
                        //var rawData = event.target.result;
                        var csvData = event.target.result;
                        var separator = $("#" + importSeparator).val();
                        var csvoptions = {"separator": separator};
                        //var itemsObj =  {};
                        try {
                            data = $.csv.toArrays(csvData, csvoptions);
                        }
                        catch (error) {
                            alert(settings.texts.txtReaderError);
                            return;
                        }
                        if (data && data.length > 0) {
                            //remove old list items
                            $("#" + itemListId + " .liItem").remove();
                            var listHtml = [];
                            $.each(data, function (idxx, option) {
                                //we must have at least 2 values (value and label)
                                if (option.length < 2) {
                                    //invalide data
                                    return;
                                }
                                if (option[0] === "" || option[1] === "") {
                                    //invalide data
                                    return;
                                }
                                // create list item
                                listHtml.push("<tr class=\"liItem\">");
                                listHtml.push("<td class=\"hiddenNotSortable\"><span class=\"itemMove\"><i class=\"icon-menu\" title=\"" + settings.texts.txtMoveDragDrop + "\"></i></span></td>");
                                listHtml.push("<td class=\"hiddenSortable\"><a class=\"itemUp\" href=\"#\"><i class=\"icon-arrow-up-3\" title=\"" + settings.texts.txtMoveUp + "\"></i></a></td>");
                                listHtml.push("<td class=\"hiddenSortable\"><a class=\"itemDown\" href=\"#\" ><i class=\"icon-arrow-down-3\" title=\"" + settings.texts.txtMoveDown + "\"></i></a></td>");
                                listHtml.push("<td><input type=\"hidden\" class=\"itemlist listitemid\" value=\"\" /></td>");
                                //add user inputs to visible list item
                                listHtml.push("<td><input type=\"text\" class=\"itemlist listitemvalue\" value=\"" + option[0] + "\" required=\"required\" /></td>");
                                listHtml.push("<td><input type=\"text\" class=\"itemlist listitemlabel\" value=\"" + option[1] + "\" required=\"required\" /></td>");
                                listHtml.push("<span class=\"itemValues\">" + option[1] + "</span>");
                                if ($.type(option[2]) !== "undefined" && $.type(option[2]) !== null && option[2] !== "") {
                                    listHtml.push("<td><input type=\"checkbox\" class=\"itemlist listitemischecked\" value=\"1\" checked=\"checked\" /></td>");
                                }
                                else {
                                    listHtml.push("<td><input type=\"checkbox\" class=\"itemlist listitemischecked\" value=\"1\" /></td>");
                                }
                                if ($.type(option[3]) !== "undefined" && $.type(option[3]) !== null && option[3] !== "") {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemredirecturl\" value=\"" + option[3] + "\" /></td>");
                                }
                                else {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemredirecturl\" value=\" \" /></td>");
                                }
                                if ($.type(option[4]) !== "undefined" && $.type(option[4]) !== null && option[4] !== "") {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemmail\" value=\"" + option[4] + "\" /></td>");
                                }
                                else {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemmail\" value=\" \" /></td>");
                                }
                                if ($.type(option[5]) !== "undefined" && $.type(option[5]) !== null && option[5] !== "") {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemmailcc\" value=\"" + option[5] + "\" /></td>");
                                }
                                else {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemmailcc\" value=\" \" /></td>");
                                }
                                if ($.type(option[6]) !== "undefined" && $.type(option[6]) !== null && option[6] !== "") {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemmailbcc\" value=\"" + option[6] + "\" /></td>");
                                }
                                else {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemmailbcc\" value=\" \" /></td>");
                                }
                                if ($.type(option[7]) !== "undefined" && $.type(option[8]) !== null && option[8] !== "") {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemlabelclass\" value=\"" + option[7] + "\" /></td>");
                                }
                                else {
                                    listHtml.push("<td><input type=\"text\" class=\"itemlist listitemlabelclass\" value=\" \" /></td>");
                                }
                                listHtml.push("<a class=\"itemRemove\" href=\"#\">" + settings.texts.txtDelete + "</a>");
                                listHtml.push("</tr>");
                            });
                            $("#" + itemListId + " tbody").append(listHtml.join(""));
                            $("#" + itemListId + " tbody").find('.itemUp').on('click', optionsTable.itemUp);
                            $("#" + itemListId + " tbody").find('.itemDown').on('click', optionsTable.itemDown);
                            optionsTable.setArrowDisabledState();
                            alert(settings.texts.txtItemsImported);

                        }
                        else {
                            alert(settings.texts.txtNoDataToImport);
                        }
                    };
                    reader.onerror = function () {
                        alert(settings.texts.txtReaderError);
                    };
                }
            }
        };

        function storeData() {
            optionsTable.setCountOfDefaultOptions();
            if (!optionsTable.checkRequiredFields()) {
                // invalid data, do not procede
                return false;
            }
            optionsTable.setIds();
            optionsTable.setLastId();
            optionsTable.setItemsStr(optionsTable.createHiddenInput());
        }

        optionsTable.createTable();
        optionsTable.setArrowClassDisabled();

        // Add event handler
        $('#' + addButtonId).on('click', function (e) {
            e.preventDefault();
            optionsTable.createTableRow();
            optionsTable.setArrowDisabledState();
        });
        $('.itemRemove').on('click', function (e) {
            e.preventDefault();
            $(this).parents('.liItem').remove();
            optionsTable.setArrowDisabledState();
        });
        $('.itemUp').on('click', optionsTable.itemUp);
        $('.itemDown').on('click', optionsTable.itemDown);
        //Fileupload to import options
        $("#" + importField).on('change', optionsTable.importOptions);

        if ($().sortable) {
            try {
                $("#" + itemListId).sortable({
                    items: ".liItem",
                    cancel: "input",
                    addClasses: false,
                    tolerance: "pointer",
                    axis: "y",
                    containment: "parent",
                    helper: function (e, ui) {
                        //hard set left position to fix y-axis drag problem on Safari
                        jQuery(ui).css({'left':'0px'})

                        ui.children().each(function () {
                            jQuery(this).width(jQuery(this).width());
                        });
                        jQuery(ui).children('td').addClass('dndlist-dragged-row');
                        return ui;
                    },
                    start: function (event, ui) {
                        this.idx = ui.item.index();
                    },
                    update: function (event, ui) {
                        optionsTable.setArrowDisabledState();
                    }
                });
            }
            catch (e) {
            }
        }
        //expose function storeData for external use (make it public)
        return {storeData: storeData};
    }
}(jQuery));