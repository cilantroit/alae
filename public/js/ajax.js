$(document).on("ready", inicio);
function inicio()
{
    /*
     var delay = (function() {
     var timer = 0;
     return function(callback, ms) {
     clearTimeout(timer);
     timer = setTimeout(callback, ms);
     };
     })();
     var email = $('#email').keyup(function(event) {
     var elemet = $(this);
     delay(function() {

     event.preventDefault();
     var email = $("#email").val();
     $.ajax
     ({
     type: "GET",
     dataType: "json",
     url: basePath + "/user/ajaxemailexist",
     data: {email: email},
     success: function(data)
     {
     if (data.success === false) {
     var error = elemet.parent().parent().next().find("#erroremail");
     error.empty();
     $('#erroremail').append("Email de usuario no disponible");
     error.empty();
     error.append("Email de usuario no disponible");
     } else {
     error.text();
     }


     }
     });
     //	}, 2000);
     //    }
     //    );
     //    $('#username').keyup(function(event) {
     //	var elemet = $(this)
     //	delay(function() {
     //	    // $("#enviar").click(function(event) {
     //	    event.preventDefault();
     //	    var usr = $("#username").val();
     $.ajax
     ({
     type: "GET",
     dataType: "json",
     url: basePath + "/user/ajaxuserexist",
     data: {usr: usr},
     success: function(data)
     {
     if (data.success === false) {
     var error = elemet.parent().parent().next().find("#usrerrorlabel");
     error.empty();
     $('#usrerrorlabel').append("Nombre de usuario no disponible");
     return false;
     } else {
     error.empty();
     return true;
     }

     }
     });
     }, 300);
     //    });


     $('#newaccount').submit(function(event) {


     });
     */
}