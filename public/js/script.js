$(document).on("ready", inicio);


function inicio() {




//    $("#loginusr").validate({
//	rules: {
//	    username: {
//		required: true
//			//customvalidation: true
//	    },
//	    password: {
//		required: true
//			//customemailvalidatorexist: true
//
//	    }
//	},
//	messages: {
//	    username: {
//		required: "Este campo es de uso oblogatorio"
//			//customvalidation: "hola",
//	    },
//	    password: {
//		required: "Este campo es de uso oblogatorio"
//	    }
//
//	}
//    });





    $("#newpasswordusr").validate({
	rules: {
	    pwd: {
		required: true
			//customvalidation: true
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
	    password: {
		required: "Este campo es de uso oblogatorio",
		equalTo: "Por favor ingrese la misma contraseña"
	    }

	}
    });






}
