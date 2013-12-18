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
});

function addEvent() {
    $('.yui3-datatable-message').append(getInputs(filters));
    createNumberIncr++;

    $('.form-datatable-remove').on("click", function() {
        $(this).parent().parent().remove();
    });
}

function getInputs(filters) {
    console.log(filters);
    var input = '<tr>';
    $.each(filters, function(key, value) {
        var disabled = '';
        if (value == 'id')
            disabled = 'disabled="disabled"';

        input += '<td><input ' + disabled + ' type="text" name="create-' + value + '[' + createNumberIncr + ']" required/></td>';
    });
    input += '<td class="form-datatable-edit"><span class="form-datatable-add"></span><span class="form-datatable-remove"></span></td>';
    return input + '</tr>';
}

function changeElement(element, pk) {
    var parentId = '#' + $(element).parent().parent().attr('id');
    var input = '';

    $.each(editable, function(key, value) {
        input = '<input type="text" value="' + $(parentId + ' .yui3-datatable-col-' + value).html() + '" required name="update-' + value + '[' + pk + ']"/>';
        $(parentId + ' .yui3-datatable-col-' + value).html(input);
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