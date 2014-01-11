$(document).on("ready", inicio);


function inicio() {

//    jQuery.validator.addMethod("email", function(value, element, params) {
//	return this.optional(element) || /^(((([a-z][\.\-\+_]?)*)[a-z0-9])+)@anapharmeurope.com$/.test(value);
//    }, "El formato del correo debe ser anapharmeurope");

    $("#newaccount").validate({
	rules: {
	    username: {
		required: true
			//customvalidation: true
	    },
	    email: {required: true,
		email: true
			//customemailvalidatorexist: true

	    }
	},
	messages: {
	    username: {
		required: "Este campo es de uso oblogatorio"
			//customvalidation: "hola",
	    },
	    email: {
		required: "Este campo es de uso oblogatorio"
	    }

	}
    });


    $("#loginusr").validate({
	rules: {
	    username: {
		required: true
			//customvalidation: true
	    },
	    password: {
		required: true
			//customemailvalidatorexist: true

	    }
	},
	messages: {
	    username: {
		required: "Este campo es de uso oblogatorio"
			//customvalidation: "hola",
	    },
	    password: {
		required: "Este campo es de uso oblogatorio"
	    }

	}
    });


    $("#register").validate({
	rules: {
	    pwd: {
		required: true
			//customvalidation: true
	    },
	    name: {
		required: true
			//customvalidation: "hola",
	    },
	    password: {
		required: true, equalTo: "#pwd",
		//customemailvalidatorexist: true
	    }
	},
	messages: {
	    pwd: {
		required: "Este campo es de uso oblogatorio"
			//customvalidation: true
	    },
	    name: {
		required: "Este campo es de uso oblogatorio"
			//customvalidation: "hola",
	    },
	    password: {
		required: "Este campo es de uso oblogatorio",
		equalTo: "Por favor ingrese la misma contraseña"
	    }

	}
    });







}
