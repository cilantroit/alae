$(document).on("ready", inicio);


function inicio() {

    $("#newaccount").validate({
	rules: {
	    username: {
		required: true
			//customvalidation: true
	    },
	    email: {required: true
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







}
