<?php
/**
 * Visforms view for Visforms
 *
 * @author       Ingmar Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2017 vi-solutions
 * @since        Joomla 3.6.2
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.ui');
JHtml::_('jquery.ui', array('sortable'));

// custom form fields
$index                 = 0;
$customData           = array();
$customData[$index++] = array("reset", "reset", "reset");
$customData[$index++] = array("submit", "submit", "submit");
// contact form fields
$index                 = 0;
$contactData           = array();
$contactData[$index++] = array("text", "first", "first name");
$contactData[$index++] = array("text", "last", "last name");
$contactData[$index++] = array("text", "company", "company name");
$contactData[$index++] = array("text", "city", "city");
$contactData[$index++] = array("text", "postal", "postal code");
$contactData[$index++] = array("text", "country", "country");
$contactData[$index++] = array("text", "phone", "phone number");
$contactData[$index++] = array("email", "email", "email");
$contactData[$index++] = array("textarea", "message", "your message");
$contactData[$index++] = array("reset", "reset", "reset");
$contactData[$index++] = array("submit", "submit", "submit");
// registration form fields
$index                      = 0;
$registrationData           = array();
$registrationData[$index++] = array("text", "title", "title");
$registrationData[$index++] = array("text", "first", "first name");
$registrationData[$index++] = array("text", "last", "last name");
$registrationData[$index++] = array("text", "maiden", "maiden name");
$registrationData[$index++] = array("fieldsep", "fieldsep1", "fieldseparator");
$registrationData[$index++] = array("number", "age", "age");
$registrationData[$index++] = array("date", "birth", "birth date");
$registrationData[$index++] = array("multicheckbox", "gender", "gender");
$registrationData[$index++] = array("fieldsep", "fieldsep2", "fieldseparator");
$registrationData[$index++] = array("checkbox", "business", "company account");
$registrationData[$index++] = array("text", "company_first", "company owner first name");
$registrationData[$index++] = array("text", "company_last", "company owner last name");
$registrationData[$index++] = array("text", "company_name", "company name");
$registrationData[$index++] = array("text", "company_tax", "tax identification number");
$registrationData[$index++] = array("url", "company_web", "company web side");
$registrationData[$index++] = array("text", "company_position", "your position");
$registrationData[$index++] = array("fieldsep", "fieldsep3", "fieldseparator");
$registrationData[$index++] = array("text", "street", "street address");
$registrationData[$index++] = array("text", "street2", "street address line 2");
$registrationData[$index++] = array("text", "city", "city");
$registrationData[$index++] = array("text", "state", "state / province");
$registrationData[$index++] = array("text", "postal", "postal / zip code");
$registrationData[$index++] = array("text", "country", "country");
$registrationData[$index++] = array("fieldsep", "fieldsep4", "fieldseparator");
$registrationData[$index++] = array("text", "phone_area", "phone area code");
$registrationData[$index++] = array("text", "phone", "phone number");
$registrationData[$index++] = array("text", "mobil_area", "mobil area code");
$registrationData[$index++] = array("text", "mobil", "mobil number");
$registrationData[$index++] = array("email", "email", "email");
$registrationData[$index++] = array("url", "web", "web side");
$registrationData[$index++] = array("fieldsep", "fieldsep5", "fieldseparator");
$registrationData[$index++] = array("checkbox", "newsletter", "subscribe to newsletter");
$registrationData[$index++] = array("checkbox", "terms", "terms and conditions accepted");
$registrationData[$index++] = array("reset", "reset", "reset");
$registrationData[$index++] = array("submit", "submit", "submit");
// product form fields
$index                 = 0;
$productData           = array();
$productData[$index++] = array("text", "pid", "product id");
$productData[$index++] = array("text", "name", "name");
$productData[$index++] = array("textarea", "description", "description");
$productData[$index++] = array("number", "price", "price");
$productData[$index++] = array("number", "quantity", "quantity");
$productData[$index++] = array("reset", "reset", "reset");
$productData[$index++] = array("submit", "submit", "submit");
// all form field types
$index                 = 0;
$allData           = array();
$allData[$index++] = array("text", "text", "text");
$allData[$index++] = array("password", "password", "password");
$allData[$index++] = array("email", "email", "email");
$allData[$index++] = array("date", "date", "date");
$allData[$index++] = array("number", "number", "number");
$allData[$index++] = array("url", "url", "url");
$allData[$index++] = array("hidden", "hidden", "hidden");
$allData[$index++] = array("textarea", "textarea" , "textarea");
$allData[$index++] = array("checkbox", "checkbox", "checkbox");
$allData[$index++] = array("multicheckbox", "multicheckbox", "multicheckbox");
$allData[$index++] = array("radio", "radio", "radio");
$allData[$index++] = array("select", "select", "select");
$allData[$index++] = array("select", "gender", "gender");
$allData[$index++] = array("file", "fileupload", "fileupload");
$allData[$index++] = array("fieldsep", "fieldseparator", "fieldseparator");
$allData[$index++] = array("image", "image", "image");
$allData[$index++] = array("reset", "reset", "reset");
$allData[$index++] = array("submit", "submit", "submit");
// test form field types
/*$index                 = 0;
$testData           = array();
$testData[$index++] = array("text", "text", "text");
$testData[$index++] = array("email", "email", "email");
$testData[$index++] = array("date", "date", "date");
$testData[$index++] = array("checkbox", "checkbox", "checkbox");
$testData[$index++] = array("multicheckbox", "multicheckbox", "multicheckbox");
$testData[$index++] = array("radio", "radio", "radio");
$testData[$index++] = array("select", "select", "select");
$testData[$index++] = array("multicheckbox", "gender", "multicheckbox");
$testData[$index++] = array("radio", "gender", "radio");
$testData[$index++] = array("select", "sex", "select");
$testData[$index++] = array("submit", "submit", "submit");*/
// add field TDs
$addTdCheck    = '<input id="__char__cb0" name="__char__cid[]" value="0" onclick="Joomla.isChecked(this.checked);" checked="" type="checkbox">';
$addTdName     = '<input name="" id="" value="" class="inputbox required" size="50" placeholder="set name" maxlength="50" required="" aria-required="true" type="text">';
$addTdLabel    = '<input name="" id="" value="" class="inputbox required" size="50" placeholder="set label" maxlength="50" required="" aria-required="true" type="text">';
$addTdFED      = '<input id="__char__fb0" name="__char__fid[]" value="0" onclick="Joomla.isChecked(this.checked);" type="checkbox">';
$addTdRequired = '<input id="__char__rb0" name="__char__rid[]" value="0" onclick="Joomla.isChecked(this.checked);" type="checkbox">';
$addTdMove     = '<td class="hiddenSortable" style="text-align: center; vertical-align: middle;"><a href="#" class="up" title="move the field up"><span class="icon-arrow-up-4"/></a><a href="#" class="down" title="move the field down"><span class="icon-arrow-down-4"/></a></td>' . '<td class="hiddenNotSortable"><span class="itemMove"><i class="icon-menu" title="'. JText::_( "COM_VISFORMS_ITEMLISTCREATOR_MOVE_DRAG_AND_DROP" ).'"></i></span></td>';
$addTdDelete   = '<td style="text-align: center; vertical-align: middle;"><a href="#" class="remove" title="remove the field" onclick="visTable.tableDeleteRow(event)"><span class="icon-delete"/></a></td>';
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        // todo: possibly freeze the field type selection for the first submit button
        // existing table order fields: attach up/down handler
        jQuery(".up,.down").click(function(event) {
            visTable.tableMoveRow(event, this);
        });
        jQuery('[name="jform[saveresult]"]').on('change', function(event){
           if (jQuery(this).val() == 1) {
               jQuery('.nav-tabs li').last().show();
           }
           else {
               jQuery('.nav-tabs li').last().hide();
           }
        });
        // disable 'go further' buttons on start, enable after form got created
        jQuery('#toolbar-file').find('button').prop('disabled', true);
        jQuery('#toolbar-forms').find('button').prop('disabled', true);
        jQuery('#toolbar-file-2').find('button').prop('disabled', true);
        jQuery('#toolbar-archive').find('button').prop('disabled', true);
        jQuery('#toolbar-home').find('button').prop('disabled', true);
        jQuery('#toolbar-user').find('button').prop('disabled', true);
        // disable generate example data on start, enable after form got created
        jQuery('#vis-creator-data input, #vis-creator-data select').prop('disabled', true);
        visHelperAsync.stopWaitDonut();
        visTable.tableAddSortableClass();
        if (jQuery().sortable) {
            try {
                jQuery('[id*=table-creator]').sortable({
                    items: "tr",
                    cancel: "a,input,.chzn-container",
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
                });
            }
            catch (e) {
            }
        }
    });
</script>
<script type="text/javascript">
    var fid = 0;
    var visHelper = {
        test: function () {
            //fid = 57;
            //this.createExampleData();
        },
        dummy: function (event) {
            event.preventDefault();
        },
        createForm: function (event, tag, char) {
            this.createFormHelper(event, tag, char);
        },
        createFormHelper: function (event, tag, char) {
            // disable all 'create' buttons
            jQuery('.btn-create-form, .btn-add-field, .btn-submit-form, .btn-test-form').attr("disabled", "disabled");
            var parentId = 'vis-creator-' + tag;
            var checkedName = char + 'cid';
            var waitTag = tag;
            event.preventDefault();

            // form name and title
            var formTitle = this.testGroupInvalid('jform_title');
            var formName  = this.testGroupInvalid('jform_name');
            var invalid = '' == formTitle || '' == formName;

            // form fields
            var selected = jQuery('#' + parentId).find('input[name="' + checkedName + '\[\]"]:checked, input[name="xid\[\]"]:checked');
            var fields = [];
            for(var i = 0; i < selected.length; ++i) {
                var tr       = jQuery(selected[i]).parents('tr');
                var tdType   = jQuery(tr).find('td:nth-child(4) #jform_typefield');
                var type     = tdType.val();
                var name     = this.testInvalid(tr, 'td:nth-child(5) input');
                var label    = this.testInvalid(tr, 'td:nth-child(6) input');
                var fed      = jQuery(tr).find('td:nth-child(7) input').is(":checked");
                var required = jQuery(tr).find('td:nth-child(8) input').is(":checked");
                invalid = invalid || '' == name || '' == label || '' == type || '0' == type;
                fields.push({type: type, name: name, label: label, fed: fed, required: required});
                // red coloring for select type controls
                if('' == type || '0' == type) {
                    tdType.addClass('invalid');
                }
                else {
                    tdType.removeClass('invalid');
                }
            }

            // stay if something is missing
            if(invalid) {
                // enable all 'create' buttons
                jQuery('.btn-create-form, .btn-add-field, .btn-submit-form, .btn-test-form').removeAttr("disabled");
                return;
            }

            // form settings
            var saveresult = jQuery('#jform_saveresult').find('input:checked').val();
            var allowfedv  = jQuery('#jform_allowfedv').find('input:checked').val();
            var ownrecordsonly  = jQuery('#jform_ownrecordsonly').find('input:checked').val();
            // data to send
            var data = 'data=' + JSON.stringify({ fields: fields, name: formName, title : formTitle, saveresult: saveresult, allowfedv: allowfedv, ownrecordsonly: ownrecordsonly, "<?php echo JSession::getFormToken();?>" : 1});
            // show the waiting icon during the request
            visHelperAsync.startWaitDonut('.icon_ajax-call-wait_' + waitTag);

            // send form data
            jQuery.ajax({
                type: 'POST',
                url: 'index.php?option=com_visforms&task=visCreator.createForm',
                data: data,
                success: function(data, textStatus, jqXHR) {
                    // hide the waiting icon
                    visHelperAsync.stopWaitDonut();
                    if (data.success) {
                        fid = data.fid;
                        // disable all controls
                        jQuery('#j-main-container input, #j-main-container select').prop('disabled', true);
                        // force chosen select to updatad disable status
                        jQuery('#j-main-container select').trigger('liszt:updated');
                        jQuery('.nav-tabs li').not('.active').hide();
                        jQuery('.nav-tabs li').last().show();
                        // bootstrap button groups need a trick
                        jQuery('label.btn, .btn-group').addClass('disabled');
                        jQuery('.btn-group').css('pointer-events', 'none');
                        // disable all 'create' buttons
                        jQuery('.btn-create-form, .btn-add-field, .btn-submit-form, .btn-test-form').attr("disabled", "disabled");
                        // empty out all move and delete columns
                        var main = jQuery('#j-main-container');
                        jQuery(main).find('tr td:nth-last-child(1)').html('');
                        jQuery(main).find('tr td:nth-last-child(2)').html('');
                        // enable 'go further' buttons
                        jQuery('#toolbar-file').find('button').removeAttr("disabled");
                        jQuery('#toolbar-forms').find('button').removeAttr("disabled");
                        jQuery('#toolbar-file-2').find('button').removeAttr("disabled");
                        jQuery('#toolbar-archive').find('button').removeAttr("disabled");
                        jQuery('#toolbar-home').find('button').removeAttr("disabled");
                        jQuery('#toolbar-user').find('button').removeAttr("disabled");
                        // enable generate example data
                        jQuery('#vis-creator-data input, #vis-creator-data select').removeAttr("disabled");
                        jQuery('#vis-creator-data').find('.btn-create-example-data').removeAttr("disabled");
                        // create 'open field' links
                        var tab = jQuery('#' + parentId);
                        //      remove tab table second to the last column 'move'
                        jQuery(tab).find('tr th:nth-last-child(2)').remove();
                        jQuery(tab).find('tr td:nth-last-child(2)').remove();
                        //      add new title 'open' and fill in field links to last column 'delete'
                        var title = '<?php echo JText::_('COM_VISFORMS_CREATOR_FIELD_TABLE_OPEN_DESC'); ?>';
                        jQuery(tab).find('tr th:nth-last-child(1)').text('<?php echo JText::_('COM_VISFORMS_CREATOR_FIELD_TABLE_OPEN'); ?>');
                        jQuery(tab).find('tr th:nth-last-child(1)').attr("data-original-title", title);
                        // trigger subform-row-add on tab-container in order to update the tooltips
                        jQuery(tab).find('tr th:nth-last-child(1)').trigger("subform-row-add", jQuery(tab));
                        jQuery(tab).find('tr td:nth-last-child(1)').each(function (index, value) {
                            if (typeof data.fields[index] !== 'undefined') {
                                var style = '';
                                var name = fields[index].name;
                                var type = fields[index].type;
                                if ('gender' !== name && 'sex' !== name) {
                                    if ('multicheckbox' === type || 'select' === type || 'radio' === type) {
                                        style = 'color: #FF5722;'
                                        title = '<?php echo JText::_('COM_VISFORMS_CREATOR_FIELD_TABLE_OPEN_SELECT_DESC'); ?>';
                                    }
                                }
                                var onClick = "visHelper.openField(event, '" + data.fields[index] + "')";
                                jQuery(this).html('<a href="#" class="edit" title="' + title + '" onclick="' + onClick + '"><span class="icon-folder" style="' + style + '"/>');
                            }
                        });
                    }
                    // update side bar menus
                    if(data.sidebar) {
                        var html = jQuery(data.sidebar).html();
                        var sidebarOpen = jQuery('#j-sidebar-container').hasClass('j-sidebar-visible');
                        jQuery('#j-toggle-sidebar-wrapper ').html(html);
                        if (sidebarOpen) {
                            jQuery('#j-toggle-button-wrapper').addClass('j-toggle-visible');
                        }
                        else {
                            jQuery('#j-toggle-button-wrapper').addClass('j-toggle-hidden');
                            jQuery('#j-toggle-sidebar-icon').removeClass('j-toggle-visible icon-arrow-left-2').addClass('j-toggle-hidden icon-arrow-right-2');
                        }
                    }
                    // show success message
                    alert(data.message);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // hide the waiting icon
                    visHelperAsync.stopWaitDonut();
                    // enable all 'create' buttons
                    jQuery('.btn-create-form, .btn-add-field, .btn-submit-form, .btn-test-form').removeAttr("disabled");
                    // give error feedback
                    visHelper.showError(jqXHR.responseText, errorThrown);
                },
                dataType: 'json',
                async: true
            });
        },
        testInvalid: function (tr, id) {
            var value  = jQuery(tr).find(id).val();
            if('' == value) {
                jQuery(tr).find(id).addClass('invalid');
            }
            else {
                jQuery(tr).find(id).removeClass('invalid');
            }
            return value;
        },
        testGroupInvalid: function (id) {
            var value  = jQuery('#' + id).val();
            if('' == value) {
                jQuery('#' + id).addClass('invalid');
                jQuery('#' + id + '-lbl').addClass('invalid');
            }
            else {
                jQuery('#' + id).removeClass('invalid');
                jQuery('#' + id + '-lbl').removeClass('invalid');
            }
            return value;
        },
        createExampleData: function (event) {
            if(null != event) {
                event.preventDefault();
            }
            var count = jQuery('#jform_count_example_data').val();

            // data to send
            var data = 'data=' + JSON.stringify({ fid: fid, count: count, "<?php echo JSession::getFormToken();?>" : 1});

            // show the waiting icon during the request
            visHelperAsync.startWaitDonut('.icon_ajax-call-wait_data');

            // send form data
            jQuery.ajax({
                type: 'POST',
                url: 'index.php?option=com_visforms&task=visCreator.createExampleData',
                data: data,
                success: function(data, textStatus, jqXHR) {
                    // hide the waiting icon
                    visHelperAsync.stopWaitDonut();
                    alert(data.message);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // hide the waiting icon
                    visHelperAsync.stopWaitDonut();
                    // give error feedback
                    visHelper.showError(jqXHR.responseText, errorThrown);
                },
                dataType: 'json',
                async: true
            });
        },
        open: function (event, url) {
            event.preventDefault();
            window.open(url.replace('__fid__', fid));
        },
        openField: function (event, id) {
            event.preventDefault();
            var fieldUrl = '<?php echo JRoute::_("index.php?option=com_visforms&task=visfield.edit&layout=edit"); ?>';
            var url = fieldUrl + '&id=' + id + '&fid=' + fid;
            url = url.replace(/&amp;/g, '&');
            window.open(url);
        },
        navigate: function (no) {
            var url = '';
            var openNewWindow = true;
            switch (no) {
                case 1:
                    url = '<?php echo JRoute::_("index.php?option=com_visforms&task=visform.edit&id=__fid__"); ?>';
                    break;
                case 2:
                    url = '<?php echo JRoute::_("index.php?option=com_visforms&view=visfields&fid=__fid__"); ?>';
                    break;
                case 3:
                    url = '<?php echo JRoute::_("index.php?option=com_visforms&view=vispdfs&fid=__fid__"); ?>';
                    break;
                case 4:
                    url = '<?php echo JRoute::_("index.php?option=com_visforms&view=visdatas&fid=__fid__"); ?>';
                    break;
                case 5:
                    url = '<?php echo JRoute::_("index.php?option=com_menus&view=item&layout=edit&menutype=mainmenu"); ?>';
                    break;
                case 6:
                    url = '<?php echo JRoute::_("index.php?option=com_menus&view=item&client_id=0&menutype=usermenu&layout=edit"); ?>';
                    break;
                case 7:
                    url = '<?php echo JRoute::_("index.php?option=com_visforms&view=viscreator"); ?>';
                    openNewWindow = false;
                    break;
            }
            url = url.replace(/&amp;/g, '&');
            url = url.replace('__fid__', fid);
            if(openNewWindow) {
                window.open(url);
            }
            else {
                location.href = url;
            }
        },
        showError: function (responseText, errorThrown) {
            var dlg = jQuery('#ajax-modal-error-dialog');
            if(responseText.startsWith('<!DOC')) {
                jQuery(dlg).find('.modal-body').html(errorThrown);
                jQuery(dlg).find('.modal-sub-title').html('');
            }
            else {
                jQuery(dlg).find('.modal-body').html(responseText);
                jQuery(dlg).find('.modal-sub-title').html('Text Status: ' + errorThrown);
            }
            jQuery(dlg).modal('show');
        }
    };
    var visTable = {
        tableAddSortableClass : function () {
            var sortableClass = (jQuery().sortable) ? 'ui-sortable' : 'notSortable';
            jQuery('[id*=table-creator]').addClass(sortableClass);
        },
        tableAddRow: function (event, tag, char) {
            // default would be: check for missing required values and point to the first one (including open fly by text: please fill this field)
            // may scroll the page back to the top after adding a new row in case of missing form title and long field list
            // the required check is done after a click on the 'create form and fields' button anyways
            event.preventDefault();
            jQuery('#select-type-field select').chosen('destroy');
            // new row
            var row = jQuery('<tr>');
            // the columns: set check boxes identifier
            // this is a complete chosen listzbox, which we have to process later in order to work
            var tdSelect   = jQuery('#select-type-field');
            var clone = jQuery(tdSelect).clone();
            var tdCheck    = '<?php echo $addTdCheck; ?>'.replace(/__char__/g, char);
            var tdName     = '<?php echo $addTdName; ?>'.replace(/__char__/g, char);
            var tdLabel    = '<?php echo $addTdLabel; ?>'.replace(/__char__/g, char);
            var tdFED      = '<?php echo $addTdFED; ?>'.replace(/__char__/g, char);
            var tdRequired = '<?php echo $addTdRequired; ?>'.replace(/__char__/g, char);
            // all append
            row.append(jQuery('<?php echo $addTdMove; ?>'));
            row.append(jQuery('<td class="center hidden-phone">').html(tdCheck));
            row.append(jQuery('<td>').html(clone));
            row.append(jQuery('<td>').html(tdName));
            row.append(jQuery('<td>').html(tdLabel));
            row.append(jQuery('<td class="center hidden-phone">').html(tdFED));
            row.append(jQuery('<td class="center hidden-phone">').html(tdRequired));
            row.append(jQuery('<?php echo $addTdDelete; ?>'));
            // new row right before the submit button row if any
            this.addRelativeToSubmit(row, tag);
            jQuery(row).find(".up,.down").click(function(event) {
                visTable.tableMoveRow(event, this);
            });
            // distroy old chosen from template and initialize new chosen
            jQuery('table').find('select').chosen();
        },
        addRelativeToSubmit: function (row, tag) {
            // new row right before the submit button row if any
            tag = '#table-creator-' + tag;
            var rows = jQuery(tag + ' tbody tr');
            var found = false;
            jQuery.each(rows, function (index, tr) {
                var type = jQuery(tr).find('select').val();
                if('submit' === type || 'reset' === type) {
                    // a submit button was found: add new field before
                    jQuery(tr).before(row);
                    found = true;
                    return false;
                }
            });
            if( !found) {
                // no submit button was found: add new field at the end
                jQuery(tag + ' tbody tr:last').after(row);
            }
        },
        tableDeleteRow: function (event) {
            event.preventDefault();
            var elements = jQuery(event.target).parentsUntil('tbody');
            elements[elements.length-1].remove();
        },
        tableMoveRow: function(event, element) {
            event.preventDefault();
            var row = jQuery(element).parents("tr:first");
            if (jQuery(element).is(".up")) {
                row.insertBefore(row.prev());
            }
            else {
                row.insertAfter(row.next());
            }
        }
    };
    var visHelperAsync = {
        startWaitDonut: function (waitDonut) {
            jQuery(waitDonut).show();
        },
        stopWaitDonut: function (waitDonut) {
            if(null == waitDonut) {
                jQuery('.icon_ajax-call-wait').hide();
            }
            else {
                jQuery(waitDonut).hide();
            }
        }
    };
</script>
<!-- hidden pre defined templates repository  -->
<div id="templates" style="display: none">
    <div id="select-type-field"><?php echo $this->typefield->getCreatorInput(); ?></div>
</div>
<form id="adminForm" class="form-validate" action="<?php echo JRoute::_("$this->baseUrl&view=$this->editViewName&layout=edit&id=$this->id&fid=$this->fid"); ?>" method="post" name="adminForm"><?php
if (!empty( $this->sidebar)) { ?>
    <div id="j-sidebar-container" class="span2"><?php echo $this->sidebar; ?></div>
    <div id="j-main-container" class="span10"><?php
}
else { ?>
    <div id="j-main-container"><?php
} ?>
    <div id="j-main-container">
        <div class="form-horizontal"><?php
	        echo (new JLayoutFile('div.quickstart_help_element'))->render(array('step' => 1, 'description' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP1')); ?>
            <fieldset class="adminform"><?php
	            echo $this->form->getControlGroup('title'); ?>
            </fieldset>
            <div class="row-fluid">
                <div class="span5 break-1200">
                    <fieldset class="adminform"><?php
	                    echo $this->form->getControlGroup('name');
                        foreach ($this->form->getFieldset('form_basics') as $field) {
                            echo $field->getControlGroup();
                        } ?>
                    </fieldset>
                </div><?php
	            echo (new JLayoutFile('creator.warning'))->render(); ?>
            </div> <?php
	        echo (new JLayoutFile('div.quickstart_help_element'))->render(array('step' => 2, 'description' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP2', 'hideTextStep' => true));
            echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'vis-creator-contact'));

            $tag = 'custom'; $char = 'a';
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'vis-creator-'.$tag, JText::_('COM_VISFORMS_CREATOR_CREATE_TAB_CUSTOM_FORM')); ?>
            <div class="row-fluid btns-create-form"> <?php
		        echo (new JLayoutFile('creator.create_buttons'))->render(array('tag' => $tag, 'char' => $char, 'text' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP3'));
		        echo (new JLayoutFile('div.icon_ajax_call_wait'))->render($tag); ?>

            </div> <?php
	        echo (new JLayoutFile('creator.fields_table'))->render(array('tag' => $tag, 'char' => $char, 'data' => $customData, 'form' => $this, 'tdMove' => $addTdMove, 'tdDelete' => $addTdDelete));
	        echo JHtml::_('bootstrap.endTab');

	        $tag = 'contact'; $char = 'b';
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'vis-creator-'.$tag, JText::_('COM_VISFORMS_CREATOR_CREATE_TAB_CONTACT_FORM')); ?>
            <div class="row-fluid btns-create-form"><?php
		        echo (new JLayoutFile('creator.create_buttons'))->render(array('tag' => $tag, 'char' => $char, 'text' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP3V2'));
		        echo (new JLayoutFile('div.icon_ajax_call_wait'))->render($tag); ?>
            </div> <?php
	        echo (new JLayoutFile('creator.fields_table'))->render(array('tag' => $tag, 'char' => $char, 'data' => $contactData, 'form' => $this, 'tdMove' => $addTdMove, 'tdDelete' => $addTdDelete));
	        echo JHtml::_('bootstrap.endTab');

	        $tag = 'login'; $char = 'c';
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'vis-creator-'.$tag, JText::_('COM_VISFORMS_CREATOR_CREATE_TAB_REGISTRATION_FORM')); ?>
            <div class="row-fluid btns-create-form"><?php
		        echo (new JLayoutFile('creator.create_buttons'))->render(array('tag' => $tag, 'char' => $char, 'text' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP3V2'));
		        echo (new JLayoutFile('div.icon_ajax_call_wait'))->render($tag); ?>
            </div> <?php
	        echo (new JLayoutFile('creator.fields_table'))->render(array('tag' => $tag, 'char' => $char, 'data' => $registrationData, 'form' => $this, 'tdMove' => $addTdMove, 'tdDelete' => $addTdDelete));
	        echo JHtml::_('bootstrap.endTab');

	        $tag = 'product'; $char = 'd';
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'vis-creator-'.$tag, JText::_('COM_VISFORMS_CREATOR_CREATE_TAB_PRODUKT_FORM')); ?>
            <div class="row-fluid btns-create-form"><?php
		        echo (new JLayoutFile('creator.create_buttons'))->render(array('tag' => $tag, 'char' => $char, 'text' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP3V2'));
		        echo (new JLayoutFile('div.icon_ajax_call_wait'))->render($tag); ?>
            </div> <?php
	        echo (new JLayoutFile('creator.fields_table'))->render(array('tag' => $tag, 'char' => $char, 'data' => $productData, 'form' => $this, 'tdMove' => $addTdMove, 'tdDelete' => $addTdDelete));
	        echo JHtml::_('bootstrap.endTab');

	        $tag = 'all'; $char = 'e';
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'vis-creator-'.$tag, JText::_('COM_VISFORMS_CREATOR_CREATE_TAB_ALL_FIELD_TYPES_FORM')); ?>
            <div class="row-fluid btns-create-form"> <?php
		        echo (new JLayoutFile('creator.create_buttons'))->render(array('tag' => $tag, 'char' => $char, 'text' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP3V2'));
		        echo (new JLayoutFile('div.icon_ajax_call_wait'))->render($tag); ?>
            </div> <?php
	        echo (new JLayoutFile('creator.fields_table'))->render(array('tag' => $tag, 'char' => $char, 'data' => $allData, 'form' => $this, 'tdMove' => $addTdMove, 'tdDelete' => $addTdDelete));
	        echo JHtml::_('bootstrap.endTab');

	        /*$tag = 'test'; $char = 'f';
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'vis-creator-'.$tag, 'Test Field Types'); ?>
            <div class="row-fluid btns-create-form"> <?php
		        echo (new JLayoutFile('creator.create_buttons'))->render(array('tag' => $tag, 'char' => $char, 'text' => 'COM_VISFORMS_CREATOR_QUICKSTART_STEP3'));
		        echo (new JLayoutFile('div.icon_ajax_call_wait'))->render($tag); ?>
            </div> <?php
	        echo (new JLayoutFile('creator.fields_table'))->render(array('tag' => $tag, 'char' => $char, 'data' => $testData, 'form' => $this, 'tdMove' => $addTdMove, 'tdDelete' => $addTdDelete));
	        echo JHtml::_('bootstrap.endTab');*/
	        $step =  (new JLayoutFile('div.quickstart_help_element'))->render(array('step' => 5, 'hideTextStep' => true, 'tag' => 'span'));
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'vis-creator-data', $step . ' ' . JText::_('COM_VISFORMS_CREATOR_TAB_GENERATE_EXAMPLE_DATA_LABEL')); ?>
            <div class="row-fluid btns-create-form">
                <button title="<?php echo JText::_('COM_VISFORMS_CREATOR_CREATE_DATA'); ?>" onclick="visHelper.createExampleData(event)" class="btn btn-small btn-info btn-create-example-data hasTooltip" disabled><?php echo JText::_('COM_VISFORMS_CREATOR_CREATE_DATA'); ?></button>
               <?php echo (new JLayoutFile('div.quickstart_help_element'))->render(array('step' => 6, 'description' => 'COM_VISFORMS_CREATOR_CREATE_DATA_DESCR', 'hideTextStep' => true, 'tag' => 'span'));
	            echo (new JLayoutFile('div.icon_ajax_call_wait'))->render('data'); ?>
            </div>
            <fieldset class="adminform"><?php
	            $field = $this->form->getField('count-example-data');
	            echo $field->getControlGroup(); ?>
            </fieldset> <?php
	        echo JHtml::_('bootstrap.endTab');

            echo JHtml::_('bootstrap.endTabSet'); ?>
        </div><?php
        $layout = new JLayoutFile('div.form_hidden_inputs');
        echo $layout->render(); ?>
    </div><?php
    // ajax modal error dialog
    echo (new JLayoutFile('div.ajax_modal_error_dialog'))->render(); ?>
</form>
