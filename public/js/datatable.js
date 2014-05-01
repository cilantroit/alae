var table;
YUI().use('datatable', function(Y) {
    table = new Y.DataTable({
	columns: columns,
	data: data,
	scrollable: "y",
        height: 400
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
            else if (value == 'analyte')
            {
                $('#analyte > select').attr("name", "update-" + value + "[" + pk + "]");
                input = $('#analyte').html();
                $(parentId + ' .yui3-datatable-col-' + value).html(input);
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
        
        $(parentId + " .form-datatable-change").hide();
        $(parentId + " .form-datatable-delete").hide();
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
            var name = "tabla_de_analitos";
            break;
        case 2:
            var name = "estudios_registrados";
            break;
        case 3:
            var name = "parametros_del_sistema";
            break;
        case 4:
            var name = "codigos_de_error_no_automatizables";
            break;
        case 5:
            var name = "lotes_sin_asignar";
            break;
        case 6:
            var name = "administracion_de_usuarios";
            break;
        case 7:
            var name = "transacciones_del_sistema";
            break;
        default:
            var name = "listado";
            break;
    }

    var date = new Date();    
    var month = parseInt(date.getMonth()) + 1;
    name = name + date.getFullYear() + month + date.getDate() + date.getHours() + date.getMinutes();
    
    var headers = '';
    $(".yui3-datatable-columns tr").each(function (index) {
         headers += '<tr>';
         $(this).children("th").each(function (index2) {
            if($(this).children("div").is(':visible')){
                headers += '<th>' + $(this).children("div").text() + '</th>';
            }else if($(this).text() == "Nivel de Acceso"){
                headers += '<th>' + $(this).text() + '</th>';
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
                if($(this).hasClass("yui3-datatable-col-profile")){
                    rows += '<td>'+$(this).find('option:selected').text()+'</td>';
                }else if($(this).hasClass("yui3-datatable-col-password")){
                    rows += '';
                } else {
                    rows += '<td>'+$(this).text()+'</td>';
                }                
            });
            rows += '</tr>';
         }
     });

    var content = '<table><thead>'+headers+'</thead><tbody>'+rows+'</tbody></table>';
    window.open(basePath + '/excel.php?data=' + encodeURIComponent(content)+'&name='+name);
}


function utf8_decode (str_data) {
  // From: http://phpjs.org/functions
  // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
  // +      input by: Aman Gupta
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Norman "zEh" Fuchs
  // +   bugfixed by: hitwork
  // +   bugfixed by: Onno Marsman
  // +      input by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: kirilloid
  // *     example 1: utf8_decode('Kevin van Zonneveld');
  // *     returns 1: 'Kevin van Zonneveld'

  var tmp_arr = [],
    i = 0,
    ac = 0,
    c1 = 0,
    c2 = 0,
    c3 = 0,
    c4 = 0;

  str_data += '';

  while (i < str_data.length) {
    c1 = str_data.charCodeAt(i);
    if (c1 <= 191) {
      tmp_arr[ac++] = String.fromCharCode(c1);
      i++;
    } else if (c1 <= 223) {
      c2 = str_data.charCodeAt(i + 1);
      tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
      i += 2;
    } else if (c1 <= 239) {
      // http://en.wikipedia.org/wiki/UTF-8#Codepage_layout
      c2 = str_data.charCodeAt(i + 1);
      c3 = str_data.charCodeAt(i + 2);
      tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
      i += 3;
    } else {
      c2 = str_data.charCodeAt(i + 1);
      c3 = str_data.charCodeAt(i + 2);
      c4 = str_data.charCodeAt(i + 3);
      c1 = ((c1 & 7) << 18) | ((c2 & 63) << 12) | ((c3 & 63) << 6) | (c4 & 63);
      c1 -= 0x10000;
      tmp_arr[ac++] = String.fromCharCode(0xD800 | ((c1>>10) & 0x3FF));
      tmp_arr[ac++] = String.fromCharCode(0xDC00 | (c1 & 0x3FF));
      i += 4;
    }
  }

  return tmp_arr.join('');
}

function utf8_encode (argString) {
  // From: http://phpjs.org/functions
  // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: sowberry
  // +    tweaked by: Jack
  // +   bugfixed by: Onno Marsman
  // +   improved by: Yves Sucaet
  // +   bugfixed by: Onno Marsman
  // +   bugfixed by: Ulrich
  // +   bugfixed by: Rafal Kukawski
  // +   improved by: kirilloid
  // +   bugfixed by: kirilloid
  // *     example 1: utf8_encode('Kevin van Zonneveld');
  // *     returns 1: 'Kevin van Zonneveld'

  if (argString === null || typeof argString === "undefined") {
    return "";
  }

  var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
  var utftext = '',
    start, end, stringl = 0;

  start = end = 0;
  stringl = string.length;
  for (var n = 0; n < stringl; n++) {
    var c1 = string.charCodeAt(n);
    var enc = null;

    if (c1 < 128) {
      end++;
    } else if (c1 > 127 && c1 < 2048) {
      enc = String.fromCharCode(
         (c1 >> 6)        | 192,
        ( c1        & 63) | 128
      );
    } else if (c1 & 0xF800 != 0xD800) {
      enc = String.fromCharCode(
         (c1 >> 12)       | 224,
        ((c1 >> 6)  & 63) | 128,
        ( c1        & 63) | 128
      );
    } else { // surrogate pairs
      if (c1 & 0xFC00 != 0xD800) { throw new RangeError("Unmatched trail surrogate at " + n); }
      var c2 = string.charCodeAt(++n);
      if (c2 & 0xFC00 != 0xDC00) { throw new RangeError("Unmatched lead surrogate at " + (n-1)); }
      c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
      enc = String.fromCharCode(
         (c1 >> 18)       | 240,
        ((c1 >> 12) & 63) | 128,
        ((c1 >> 6)  & 63) | 128,
        ( c1        & 63) | 128
      );
    }
    if (enc !== null) {
      if (end > start) {
        utftext += string.slice(start, end);
      }
      utftext += enc;
      start = end = n + 1;
    }
  }

  if (end > start) {
    utftext += string.slice(start, stringl);
  }

  return utftext;
}