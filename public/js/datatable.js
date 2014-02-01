var table;
YUI().use('datatable', function(Y) {
    table = new Y.DataTable({
	columns: columns,
	data: data,
	scrollable: "y"
    }).render("#datatable");

    $('.yui3-datatable-message').html(filtersHtml);
    $('.yui3-datatable-message').show();
    $(".yui3-datatable-filter").on("change", function() {
	$('.yui3-datatable-data  > tr').show();

	var elementId = $(this).attr('id');
	var element = '#' + elementId + ' option:selected';

	if ($(element).val() != "-1")
	{
	    $('.yui3-datatable-data > tr').hide();
	    $('.yui3-datatable-data  > tr > td').each(function(k, v) {
		if ($(element).text() == $(this).text())
		    $(this).parent().show();
	    });
	}

	$('.yui3-datatable-filter').each(function(k, v) {
	    if ($(this).attr('id') != elementId)
		$(this).children().removeAttr("selected");
	});
    });
    $('span.form-datatable-new').on("click", function() {
	$('.yui3-datatable-message').append(getInputs(filters));
	$('.yui3-datatable-message tr:first-child').next("tr").addClass("form-datatable-add-block");
	createNumberIncr++;
	$('.form-datatable-add').on("click", function() {
	    addEvent();
	});
	$('.form-datatable-new').hide();

    });

    $('.form-datatable-profile').on("change", function(event) {
	event.stopPropagation();
	var elementId = $(this).attr('id');
	$('.form-datatable-profile').each(function(k, v) {
	    if ($(this).attr('id') != elementId)
		$(this).children().removeAttr("selected");
	});
	$("#profile").val(this.value);
    });
    
    table.modifyColumn('use', {
        formatter: function (o) {
            return o.value ? '<input type="checkbox" disabled checked value="'+ o.value +'"/>' : '<input type="checkbox" disabled value="'+ o.value +'"/>';
        }
    });
});

function addEvent() {
    $('.yui3-datatable-message').append(getInputs(filters));
    createNumberIncr++;

    $('.form-datatable-remove').on("click", function() {
	$(this).parent().parent().remove();
    });
}

function getInputs(filters) {
    var input = '<tr>';
    $.each(filters, function(key, value) {
        var disabled = '';
        if (value == 'id')
            disabled = 'disabled="disabled"';

        if (value == 'analyte')
        {
            $('#analyte > select').attr("name", "create-" + value + "[" + createNumberIncr + "]");
            input += '<td>'+ $('#analyte').html() +'</td>';
        }
        else if (value == 'analyte_is')
        {
            $('#analyte_is > select').attr("name", "create-" + value + "[" + createNumberIncr + "]");
            input += '<td>'+ $('#analyte_is').html() +'</td>';
        }
        else if (value == 'use')
        {
            input += '<td><input type="checkbox" name="create-' + value + '[' + createNumberIncr + ']"/></td>';
        }
        else if (value == 'unit')
        {
            $('#unit > select').attr("name", "create-" + value + "[" + createNumberIncr + "]");
            input += '<td>'+ $('#unit').html() +'</td>';
        }
        else{
            input += '<td><input ' + disabled + ' type="text" name="create-' + value + '[' + createNumberIncr + ']" class="datatable-class-' + value + '"/></td>';
        }
    });
    input += '<td class="form-datatable-edit"><span class="form-datatable-add"></span><span class="form-datatable-remove"></span></td>';
    return input + '</tr>';
}

function changeElement(element, pk) {
    var parentId = '#' + $(element).parent().parent().attr('id');
    var input = '';

    $.each(editable, function(key, value) {
        if (value == "use"){
            $(parentId + ' .yui3-datatable-col-' + value + ' > input').prop('disabled',false);
            $(parentId + ' .yui3-datatable-col-' + value + ' > input').attr("name", "update-" + value + "[" + pk + "]");
        }
        else if (value == 'analyte_is')
        {
            $('#analyte_is > select').attr("name", "update-" + value + "[" + pk + "]");
            input = $('#analyte_is').html();
            $(parentId + ' .yui3-datatable-col-' + value).html(input);
        }
        else if (value == 'unit')
        {
            $('#unit > select').attr("name", "update-" + value + "[" + pk + "]");
            input = $('#unit').html();
            $(parentId + ' .yui3-datatable-col-' + value).html(input);
        }
        else if (value == 'accepted_flag')
        {
            var response = 1; 
            $(element).attr("disabled", true);
            alert($(element).children().attr('class'));
            if ($(element).children().attr('class') == "btn-reject") 
            {
                response = 0;
            }
            input = '<input type="hidden" value="' + response + '" name="update-' + value + '[' + pk + ']"/>';
            $(parentId + ' .yui3-datatable-col-' + value).html(input);
        }
        else{
            input = '<input class="datatable-class-' + value + '" type="text" value="' + $(parentId + ' .yui3-datatable-col-' + value).html() + '" name="update-' + value + '[' + pk + ']"/>';
            $(parentId + ' .yui3-datatable-col-' + value).html(input);
        }
    });
}

function removeElement(element, pk)
{
    $.ajax({
	type: "GET",
	dataType: "json",
	url: deleteUrl,
	data: {pk: pk},
	success: function(data) {
	    if (data.status) {
		$(element).parent().parent().remove();
		$("#yui3-datatable-filter-id option").each(function() {
		    if ($(this).text() == pk) {
			$(".yui3-datatable-filter option[value='" + $(this).val() + "']").remove();
		    }
		});

		$.each(data, function(key, value) {
		    if (value.id == pk) {
			delete data[key];
			table.removeRow(value.id);
		    }
		});
	    } else {
		alert(data.message);
	    }
	}
    });
}

