$(document).on("ready", inicio);
function inicio() {

    $(".login_form").validate({
        rules: {
            password: {
                required: true,
                minlength: 8
            },
            email: {
                required: true,
                email: true
            }


        },
        messages: {
            password: {
                required: "Debe introducir su contraseña",
                minlength: "Su contraseña debe tener al menos 8 caracteres"
            },
            email: "Debe introducir un formato de correo valido "

        }
    });







}