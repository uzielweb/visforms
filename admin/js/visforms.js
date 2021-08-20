var editValue = null;
var fieldsWithOptionlist = ['visf_multicheckbox', 'visf_radio', 'visf_select'];

if (window.addEventListener) {
	window.addEventListener("load", initPage, false);
} 
else if (window.attachEvent) {
	var r = window.attachEvent("onload", initPage); 
} 
else {
	window.alert("Problem to add EventListener to Window Object !");  
}

function initPage() {
	typeFieldInit();
}

//hide parameters from "defaultvalue" for all field types
function hiddenProperties() {
	document.getElementById('visf_text').style.display = "none";
    document.getElementById('visf_email').style.display = "none";
    document.getElementById('visf_date').style.display = "none";
    document.getElementById('visf_url').style.display = "none";
    document.getElementById('visf_number').style.display = "none";
    document.getElementById('visf_password').style.display = "none";
    document.getElementById('visf_hidden').style.display = "none";
    document.getElementById('visf_textarea').style.display = "none";
    document.getElementById('visf_checkbox').style.display = "none";
    document.getElementById('visf_multicheckbox').style.display = "none";
    document.getElementById('visf_radio').style.display = "none";
    document.getElementById('visf_select').style.display = "none";
    document.getElementById('visf_file').style.display = "none";
    document.getElementById('visf_image').style.display = "none";
    document.getElementById('visf_reset').style.display = "none";
    document.getElementById('visf_submit').style.display = "none";
    document.getElementById('visf_fieldsep').style.display = "none";
    document.getElementById('visf_pagebreak').style.display = "none";
    document.getElementById('visf_calculation').style.display = "none";
    document.getElementById('visf_location').style.display = "none";
    document.getElementById('visf_signature').style.display = "none";
    document.getElementById('visf_multicheckboxsql').style.display = "none";
    document.getElementById('visf_radiosql').style.display = "none";
    document.getElementById('visf_selectsql').style.display = "none";
    hideFieldsWithOptionList();
}

function hideFieldsWithOptionList() {
    var n = fieldsWithOptionlist.length;
    for (var i=0; i< n; i++) {
        document.getElementById(fieldsWithOptionlist[i] + '_options').style.display = "none";
    }
}

function showFieldWithOptionList(field) {
    var n = fieldsWithOptionlist.length;
    for (var i=0; i< n; i++) {
        if (fieldsWithOptionlist[i] === field) {
            document.getElementById(fieldsWithOptionlist[i] + '_options').style.display = "";
        }
    }
}

//initialise field, display parameters for selected field type 
function typeFieldInit() {
	hiddenProperties();
    var ffield = 'visf_' + getSelectedFieldType();
	
    //no type set yet
    //or no hidden properties and so nothing to display
	if ((ffield != 'visf_0') && (ffield != 'visf_submit') && (ffield != 'visf_reset') && (ffield != 'visf_pagebreak')) {
        document.getElementById(ffield).style.display = "";
        showFieldWithOptionList(ffield);
	}
    setRequiredAsterix ();
    editOnlyFieldChange();
    setGridSizesOptionsVisibility();
    preSelectSolitaryOptionOnChange();
    toggleReloadOnChange();
    renderAsDataListChange();
}

//perform actions which are necessary when the type of a field is changed
function typeFieldChange() {
	hiddenProperties();
    var ffield = 'visf_' + getSelectedFieldType();
	
    //no type set yet
    //or no hidden properties and so nothing to display
	if ((ffield != 'visf_0') && (ffield != 'visf_submit') && (ffield != 'visf_reset') && (ffield != 'visf_pagebreak')) {
        document.getElementById(ffield).style.display = "";
        showFieldWithOptionList(ffield);
	}
    //Insert an asterix for required options
    setRequiredAsterix ()
    //Handle restsricts
    setGridSizesOptionsVisibility();
}

function formatFieldDateChange(o, ffield, text) {
    if (text) {
        resetSelectValue(o, ffield);
        alert(text);
        return false;
    } 
	else {
        var calendarsToChange = ['tdate_calender', 'tdate_calender_min', 'tdate_calender_max'];
        var n = calendarsToChange.length;
        for (var i = 0; i < n; i++) {
            var el = document.getElementById('jform_defaultvalue_' + calendarsToChange[i]);
            if (el) {
                // set selected in dateformat select list to new value
                formatFieldDateChangeSelected();

                // setup calendar with correct dateformat
                formatDateCalendarChange(calendarsToChange[i]);
            }
        }
    }	
}

function formatDateChangeInputValue (id) {
	// get value of initial Date field
    var el = document.getElementById('jform_defaultvalue_' + id)
    var date = el.value;
	
	// if there is a date value set, change date format acording to selected listbox value
	if (! date == "") {
		// find date delimiter
		var date_delimiter = date.match(/\/|-|\./);
		var date_parts = date.split(date_delimiter[0]);

		// get date parts. Each date_delimiter represents a defined date format and a fix position of date parts
		switch (date_delimiter[0]) {
			case "/" :
				var month = date_parts[0];
				var day = date_parts[1];
				var year = date_parts[2];
				break;
			case "-" :
				var year = date_parts[0];
				var month = date_parts[1];
				var day = date_parts[2];
				break;
			case "." :
				var day = date_parts[0];
				var month = date_parts[1];
				var year = date_parts[2];
				break;
		}

		// get new date output format
        var d_format = document.getElementById('jform_defaultvalue_tdateformat_row').value;
	
		//find date format delimiter
		var d_format_delimiter = d_format.match(/\/|-|\./);
		
		// construct the formated date string. Each date format delimiter represents a defined date format and a fix position on date parts
		switch (d_format_delimiter[0]) {
			case '/' :
				var formatted_date = month + d_format_delimiter + day + d_format_delimiter + year;
				break;
			case '-' :
				var formatted_date = year + d_format_delimiter + month + d_format_delimiter + day;
				break;
			case '.' :
				var formatted_date = day + d_format_delimiter + month + d_format_delimiter + year;
				break;
		}
        el.setAttribute('data-alt-value', formatted_date);
        el.value = formatted_date;
	}
}

function formatFieldDateChangeSelected () {
	for(i=document.getElementById('jform_defaultvalue_tdateformat_row').options.length-1;i>=0;i--) {
		if(document.getElementById('jform_defaultvalue_tdateformat_row').options[i].getAttribute('selected')) {
            document.getElementById('jform_defaultvalue_tdateformat_row').options[i].removeAttribute('selected');
        }
        if(document.getElementById('jform_defaultvalue_tdateformat_row').options[i].selected) {
            document.getElementById('jform_defaultvalue_tdateformat_row').options[i].setAttribute('selected', 'selected');
        }
    }
}

function formatDateCalendarChange (id) {
	// get new date output format
	var d_format = document.getElementById('jform_defaultvalue_tdateformat_row').value;
    var btn = (document).getElementById('jform_defaultvalue_' + id + '_btn');
	
	// get dateformat for php and for javascript
	d_format = d_format.split(';');

    formatDateChangeInputValue (id);
    var calendar = btn.parentNode.parentNode.parentNode.parentNode.querySelectorAll('.field-calendar')[0];
    var instance = calendar._joomlaCalendar;
    if (instance) {
        instance.params.dateFormat =  d_format[1];
    }
}

//we need to restict some actions for fields which are restrictors and give an error message
function fieldUsed(o, ffield, msg) {
    if (o.id.indexOf('editonlyfield') > 0) {
        var idx = o.selectedIndex;
        var selected = o[idx].value;
        var selectedValue = selected[0].value;
        if (selectedValue == "0") {
            return true;
        }
    }
    resetSelectValue(o, ffield);
    window.alert(msg);
    return false;
}

//set asterix in labels for parameters which are required
//we cannot use Joomla! form field attribute required because we get an error when a hidden parameter which is required is not set and we try to save the visforms field
function setRequiredAsterix () {
    var sel = getSelectedFieldType();
    switch (sel) {
        case 'checkbox' :
            var el = [document.getElementById('jform_defaultvalue_f_checkbox_attribute_value-lbl')];
            break;
        case 'image':
            var el = [document.getElementById('jform_defaultvalue_f_image_attribute_alt-lbl')];
            el.push (document.getElementById('jform_defaultvalue_f_image_attribute_src-lbl')); 
            break;
        case 'multicheckbox' :
            var el = [document.getElementById('jform_defaultvalue_f_multicheckbox_list_hidden-lbl')];
            break;
        case 'select' :
            var el = [document.getElementById('jform_defaultvalue_f_select_list_hidden-lbl')];
            break;
        case 'radio' :
            var el = [document.getElementById('jform_defaultvalue_f_radio_list_hidden-lbl')];
            break;
        case 'location' :
            var el = [document.getElementById('jform_defaultvalue_f_location_defaultMapCenter_lat-lbl')];
            el.push (document.getElementById('jform_defaultvalue_f_location_defaultMapCenter_lng-lbl'));
            break;
        default :
            break;
    }

    if (el) {
        var n = el.length;
        for (var i = 0; i < n; i++) {
            changeLabel(el[i]);
        }
    }
}


//insert asterix in label
function changeLabel (el, index, arr) {
    var label = el.innerHTML + '<span class="star"> *</span>';
    el.innerHTML = label;
}

function setGridSizesOptionsVisibility() {
    var sel = getSelectedFieldType();
    var el = document.getElementById('bootstrapGridSizes')
    if (el) {
        switch (sel) {
            case '0':
            case 'submit' :
            case 'reset' :
            case 'image' :
            case 'pagebreak' :
            case 'hidden' :
                el.style.display = "none";
                break;
            default:
                el.style.display = "";
                break;
        }
    }
}

function editOnlyFieldChange() {
    //remove all options except the default option from the field list in parameter equalTo
    var fieldtype = getSelectedFieldType();
    var editonly = document.getElementById('jform_' + 'editonlyfield');
    if (editonly) {
        var equalToList = document.getElementById('jform_defaultvalue_f_' + fieldtype + '_validate_equalTo');
        var showWhenList = document.getElementById('jform_defaultvalue_f_' + fieldtype + '_showWhen');
        var uncheckedValue = document.getElementById('jform_defaultvalue_f_' + fieldtype + '_unchecked_value');
        var idx = editonly.selectedIndex;
        if (editonly.options[idx].value === "1") {
            //hide equalto and conditional fields
            if (equalToList) {
                equalToList.parentNode.parentNode.style.display = "none";
            }
            if (showWhenList) {
                showWhenList.parentNode.parentNode.style.display = "none";
            }
            if (uncheckedValue) {
                uncheckedValue.parentNode.parentNode.style.display = "none";
            }
        } 
		else {
            //show equalto and conditional fields
            if (equalToList) {
                equalToList.parentNode.parentNode.style.display = "";
            }
            if (showWhenList) {
                showWhenList.parentNode.parentNode.style.display = "";
            }
            if (uncheckedValue) {
                uncheckedValue.parentNode.parentNode.style.display = "";
            }
        }
    }
}

function resetSelectValue(o, value) {
    var selectbox = document.getElementById(o.id);
    var optlength = selectbox.options.length;
    for (var i = 0; i < optlength; i++)
    {
        if (selectbox.options[i].value == value) {
            selectbox.options[i].selected = true;
            jQuery('#' + o.id).trigger('liszt:updated');
        }
    }
}

function getSelectedFieldType() {
    var ft = document.getElementById('jform_typefield');
    var idx = ft.selectedIndex;
    return ft[idx].value;
}

function toggleReloadOnChange() {
    var field = document.getElementById('jform_defaultvalue_f_selectsql_toggle_reload');
    if (field) {
        var showWhenList = document.getElementById('jform_defaultvalue_f_selectsql_showWhen');
        var reloadList = document.getElementById('jform_defaultvalue_f_selectsql_reload');
        var hideEmpty = document.getElementById('jform_defaultvalue_f_selectsql_hideOnEmptyOptionList');
        var preSelectSolitaryOption = document.getElementById('jform_defaultvalue_f_selectsql_preSelectSolitaryOption');
        var hideOnPreSelectedSolitaryOption = document.getElementById('jform_defaultvalue_f_selectsql_hideOnPreSelectedSolitaryOption');
        var editonly = document.getElementById('jform_' + 'editonlyfield');
        var dataList = document.getElementById('jform_defaultvalue_f_selectsql_render_as_datalist');
        var isEditOnly = false;
        var isDataList = false;
        if (editonly) {
            if (editonly.options[editonly.selectedIndex].value === "1") {
                isEditOnly = true;
            }
        }
        if (dataList) {
            if (dataList.options[dataList.selectedIndex].value === "1") {
                isDataList = true;
            }
        }
        var idx = field.selectedIndex;
        if (field.options[idx].value === "1") {
            if (showWhenList) {
                showWhenList.parentNode.parentNode.style.display = "none";
            }
            if (reloadList) {
                reloadList.parentNode.parentNode.style.display = "";
            }
            if (hideEmpty) {
                hideEmpty.parentNode.parentNode.style.display = "";
            }
            if (preSelectSolitaryOption) {
                if (isDataList) {
                    preSelectSolitaryOption.parentNode.parentNode.style.display = "none";
                }
                else {
                    preSelectSolitaryOption.parentNode.parentNode.style.display = "";
                    // show/hide dependant field according to this field's value
                    preSelectSolitaryOptionOnChange();
                }
            }
        }
        else {
            if (showWhenList) {
                if (isEditOnly) {
                    showWhenList.parentNode.parentNode.style.display = "none";
                }
                else {
                    showWhenList.parentNode.parentNode.style.display = "";
                }
            }
            if (reloadList) {
                reloadList.parentNode.parentNode.style.display = "none";
            }
            if (hideEmpty) {
                hideEmpty.parentNode.parentNode.style.display = "none";
            }
            if (preSelectSolitaryOption) {
                preSelectSolitaryOption.parentNode.parentNode.style.display = "none";
            }
            if (hideOnPreSelectedSolitaryOption) {
                hideOnPreSelectedSolitaryOption.parentNode.parentNode.style.display = "none";
            }
        }
    }
}

function preSelectSolitaryOptionOnChange() {
    var field = document.getElementById('jform_defaultvalue_f_selectsql_preSelectSolitaryOption');
    if (field) {
        var hideOnPreSelectedSolitaryOption = document.getElementById('jform_defaultvalue_f_selectsql_hideOnPreSelectedSolitaryOption');
        var idx = field.selectedIndex;
        if (field.options[idx].value === "1") {
            if (hideOnPreSelectedSolitaryOption) {
                hideOnPreSelectedSolitaryOption.parentNode.parentNode.style.display = "";
            }
        }
        else {
            if (hideOnPreSelectedSolitaryOption) {
                hideOnPreSelectedSolitaryOption.parentNode.parentNode.style.display = "none";
            }
        }
    }
}

function renderAsDataListChange() {
    var field = document.getElementById('jform_defaultvalue_f_selectsql_render_as_datalist');
    if (field) {
        var customselectvaluetext = document.getElementById('jform_defaultvalue_f_selectsql_customselectvaluetext');
        var required = document.getElementById('jform_defaultvalue_f_selectsql_attribute_required');
        var custominfo = document.getElementById('jform_defaultvalue_f_selectsql_custominfo');
        var customerror = document.getElementById('jform_defaultvalue_f_selectsql_customerror');
        var multiple = document.getElementById('jform_defaultvalue_f_selectsql_attribute_multiple');
        var size = document.getElementById('jform_defaultvalue_f_selectsql_attribute_size');
        var preSelectSolitaryOption = document.getElementById('jform_defaultvalue_f_selectsql_preSelectSolitaryOption');
        var hideOnPreSelectedSolitaryOption = document.getElementById('jform_defaultvalue_f_selectsql_hideOnPreSelectedSolitaryOption');
        var idx = field.selectedIndex;
        if (field.options[idx].value === "1") {
            if (customselectvaluetext) {
                customselectvaluetext.parentNode.parentNode.style.display = "none";
            }
            if (required) {
                required.parentNode.parentNode.style.display = "none";
            }
            if (custominfo) {
                custominfo.parentNode.parentNode.style.display = "none";
            }
            if (customerror) {
                customerror.parentNode.parentNode.style.display = "none";
            }
            if (multiple) {
                multiple.parentNode.parentNode.style.display = "none";
            }
            if (size) {
                size.parentNode.parentNode.style.display = "none";
            }
            // as we have a dataList and not select, these field options do not make any sence
            // hide them
            if (preSelectSolitaryOption) {
                preSelectSolitaryOption.parentNode.parentNode.style.display = "none";
            }
            if (hideOnPreSelectedSolitaryOption) {
                hideOnPreSelectedSolitaryOption.parentNode.parentNode.style.display = "none";
            }
        }
        else {
            if (customselectvaluetext) {
                customselectvaluetext.parentNode.parentNode.style.display = "";
            }
            if (required) {
                required.parentNode.parentNode.style.display = "";
            }
            if (custominfo) {
                custominfo.parentNode.parentNode.style.display = "";
            }
            if (customerror) {
                customerror.parentNode.parentNode.style.display = "";
            }
            if (multiple) {
                multiple.parentNode.parentNode.style.display = "";
            }
            if (size) {
                size.parentNode.parentNode.style.display = "";
            }
            // proper display values for preSelectSolitaryOption and hideOnPreSelectedSolitaryOption only depend on selected value of _reload Option
            // and they are set properly by toggleReloadChange()
            toggleReloadOnChange();
        }
    }
}