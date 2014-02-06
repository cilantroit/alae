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
//	var elementId = $(this).attr('id');
//	$('.form-datatable-profile').each(function(k, v) {
//	    if ($(this).attr('id') != elementId)
//		$(this).children().removeAttr("selected");
//	});
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
        else if (value == 'cs_number')
        {
            input += '<td><input ' + disabled + ' type="text" name="create-' + value + '[' + createNumberIncr + ']" class="datatable-class-' + value + '" value="8"/></td>';
        }
        else if (value == 'qc_number')
        {
            input += '<td><input ' + disabled + ' type="text" name="create-' + value + '[' + createNumberIncr + ']" class="datatable-class-' + value + '" value="4"/></td>';
        }
        else{
            input += '<td><input ' + disabled + ' type="text" name="create-' + value + '[' + createNumberIncr + ']" class="datatable-class-' + value + '"/></td>';
        }
    });
    input += '<td class="form-datatable-edit"><span class="form-datatable-add"></span><span class="form-datatable-remove"></span></td>';
    return input + '</tr>';
}

function changeElement(element, pk) 
{    
    var answer = confirm("¿Desea editar esta información?");
    if (answer === true)
    {
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
                if ($(element).children().attr('class') == "btn-reject") 
                {
                    response = 0;
                }
                input = $(parentId + ' .yui3-datatable-col-' + value).html() + '<input type="hidden" value="' + response + '" name="update-' + value + '[' + pk + ']"/>';
                $(parentId + ' .yui3-datatable-col-' + value).html(input);
            }
            else{
                input = '<input class="datatable-class-' + value + '" type="text" value="' + $(parentId + ' .yui3-datatable-col-' + value).html() + '" name="update-' + value + '[' + pk + ']"/>';
                $(parentId + ' .yui3-datatable-col-' + value).html(input);
            }
        });
    }
    else{
        return false;
    }
}

function removeElement(element, pk)
{
    var answer = confirm("¿Desea eliminar este dato?");
    if (answer === true)
    {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: deleteUrl,
            data: {pk: pk},
            success: function(data) {
                if (data.status) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            }
        });
    }
    else{
        return false;
    }
}

function excel(id)
{    
    switch(id)
    {
        case 1:
            var name = "listado_de_analitos";
            break;
        case 2:
            var name = "listado_de_estudios";
            break;
        case 3:
            var name = "parametros_de_verificacion_de_lotes";
            break;
        case 4:
            var name = "codigos_no_automatizables";
            break;
        case 5:
            var name = "lotes_sin_asignar";
            break;
        case 6:
            var name = "listado_de_usuarios";
            break;
        default:
            var name = "listado";
            break;
    }
    
    var headers = '';
    $(".yui3-datatable-columns tr").each(function (index) {
         headers += '<tr>';
         $(this).children("th").each(function (index2) {
            if($(this).children("div").is(':visible')){
                headers += '<th>'+$(this).children("div").text()+'</th>';
            }
         });
         headers += '</tr>';
     });

    var rows = '';
     $(".yui3-datatable-data tr").each(function (index) {
         if($(this).is(':visible'))
         {
            rows += '<tr>';
            $(this).children("td").each(function (index2) {
                rows += '<td>'+$(this).text()+'</td>';
            });
            rows += '</tr>';
         }
     });

    var content = '<table><thead>'+headers+'</thead><tbody>'+rows+'</tbody></table>';
    window.open(basePath + '/excel.php?data=' + encodeURIComponent(content)+'&name='+name);
}