YUI().use('datatable', function(Y) {
    var table = new Y.DataTable({
        columns: columns,
        data: data,
        scrollable: "y",
        height: "200px",
        width: "600px"
    }).render("#datatable");

    $('.yui3-datatable-message').html(getDataFilters(filters));
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
    $('.form-datatable-new').on("click", function() {
        $('.yui3-datatable-message').append(getInputs(editable));
        $('.yui3-datatable-message tr:first-child').next("tr").addClass("form-datatable-add-block");
        createNumberIncr++;
        $('.form-datatable-add').on("click", function() {
            addEvent();
        });
        $('.form-datatable-new').hide();
    });
});

function getDataFilters(filters) {
    var select = '<tr>';
    $.each(filters, function(key, value) {
        select += '<td><select id="yui3-datatable-filter-' + key + '" class="yui3-datatable-filter">' + getOptions(value) + '</select></td>';
    });
    select += '<td><span class="form-datatable-new">nuevo</span><a href="' + excel + '">excel</a><a href="' + pdf + '">pdf</a><input type="submit"/></td>';
    return select + '</tr>';
}

function getOptions(data) {
    var options = '<option value="-1">autofiltro</option>';
    $.each(data, function(key, value) {
        options += '<option value="' + key + '">' + value + '</option>'
    });
    return options;
}

function addEvent() {
    $('.yui3-datatable-message').append(getInputs(filters));
    createNumberIncr++;

    $('.form-datatable-remove').on("click", function() {
        $(this).parent().parent().remove();
    });
}

function getInputs(editable) {
    var input = '<tr>';
    $.each(editable, function(key, value) {
        var disabled = '';
        if (value == 'id')
            disabled = 'disabled="disabled"';

        input += '<td><input ' + disabled + ' type="text" class="form-datatable-input" name="create-' + value + '[' + createNumberIncr + ']" required/></td>';
    });
    input += '<td><span class="form-datatable-add">agregar</span><span class="form-datatable-remove">eliminar</span></td>';
    return input + '</tr>';
}

function changeElement(element, pk) {
    var parentId = '#' + $(element).parent().parent().attr('id');
    var input = '';

    $.each(editable, function(key, value) {
        input = '<input type="text" value="' + $(parentId + ' .yui3-datatable-col-' + value).html() + '" class="form-datatable-input" required name="update-' + value + '[' + pk + ']"/>';
        $(parentId + ' .yui3-datatable-col-' + value).html(input);
    });
}

function removeElement(element, pk) {
//    alert(deleteUrl);
//    $.getJSON(deleteUrl + pk, function(data) {
//        if(data.status){
//            $(element).parent().parent().remove();
//        } else {
//            alert(data.message);
//        }
//    });   

    $.ajax({
        type: "GET",
        dataType: "json",
        url: deleteUrl,
        data: {pk: pk},
        success: function(data) {
            if (data.status) {
                $(element).parent().parent().remove();
            } else {
                alert(data.message);
            }
        }
    });
}