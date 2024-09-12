const span_price = document.getElementById("price")
const can_puestos = document.getElementById("tickets")
const unique_price = 100000
const email = document.getElementById('email');
const cedula = document.getElementById('cedula');
const firstname = document.getElementById('name');
const lastname = document.getElementById('lastname');
const cellphone = document.getElementById('cellphone');
const tickets = document.getElementById('tickets');
const terms = document.getElementById('terms');
const form = document.getElementById('formulario');
const wallet_div = document.getElementById('wallet_container');
const id_payment = document.getElementById("id_payment");
const vendidos = document.getElementById("vendidos");
const action = document.getElementById("action");
const progressBar = document.getElementById('bar')
var available_tickets = false;

console.log(available_tickets)

//const mp = new MercadoPago('TEST-5ec0aefe-1128-4b23-bbe7-e8bfc67da5c6');
const mp = new MercadoPago('TEST-8908da28-7c6b-4131-9f46-2b265f901331');

const bricksBuilder = mp.bricks();

document.addEventListener("keypress", (e)=>{
    if (e.key === "Enter")
        e.preventDefault();
})

document.addEventListener("DOMContentLoaded", (e) => {

    if (action.value != "") {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        if (action.value == "success") {
            Command: toastr["success"]("Su pago ha sido recibido, pronto le enviaremos un email con los detalles.")


        } else {
            Command: toastr["error"]("Su pago ha sido rechazado, por favor revise la informacion e intentelo de nuevo.")

        }
    }

    setInterval(() => {


        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = (e) => {
            if (xhr.readyState !== 4)
                return
            if (xhr.status === 200) {
                response = JSON.parse(xhr.responseText)


                vendidos.value = response["can"]
                if (parseInt(vendidos.value) >= 0 && parseInt(vendidos.value) <= 9999)
                    available_tickets = true;
                else
                    available_tickets = false;




                var value_now = (response['can'] / 10000) * 100
                value_now = value_now.toFixed(7)

                progressBar.setAttribute('aria-valuenow', parseFloat(value_now))
                progressBar.innerText = parseFloat(value_now) + "%"
                progressBar.style.width = parseFloat(value_now) + "%"

                let left_tickets = 10000 - response['can']

                if (left_tickets >= 10)
                    can_puestos.setAttribute("max", 10)
                else
                    can_puestos.setAttribute("max", left_tickets)


            }
            else
                console.log("error")

        }
        xhr.open("GET", "server/n.php")
        xhr.setRequestHeader("Content-Type", "application/json")
        xhr.send()

    }, 6000);


})

mp.bricks().create("wallet", "wallet_container", {
    initialization: {
        redirectMode: "self",
        preferenceId: id_payment.value,
    },
    customization: {
        texts: {
            valueProp: 'smart_option',
        },
    }
});



form.addEventListener("change", (e) => {

    if (checkInputs() && available_tickets) {
        if (parseInt(vendidos.value) >= 0 && parseInt(vendidos.value) < 10000) {


            wallet_div.classList.replace("d-none", "d-block")

            updatePreferenceData();
        } else {
            alert("Lo sentimos pero ya se han vendido todos los ticket para este sorteo.")
        }
    }

    else
        if (wallet_div.classList.contains("d-block"))
            wallet_div.classList.replace("d-block", "d-none")
})



can_puestos.addEventListener("change", function (e) {
    number = can_puestos.value * unique_price
    span_price.innerText = '$' + number.toLocaleString('es-CO')
})


form.addEventListener("submit", function (e) {
    e.preventDefault()
})

email.addEventListener("change", (e) => {

    if (!validateEmail(email.value))
        Swal.fire({
            title: 'Error!',
            text: 'El correo es incorrecto, verifiquelo.',
            icon: 'error',
            confirmButtonText: 'OK'
        })

})

function checkEmail() {
    return validateEmail(email.value);
}



cedula.addEventListener("change", (e) => {
    if (!checkCedula())
        Swal.fire({
            title: 'Error!',
            text: 'La cedula es incorrecta, verifiquela.',
            icon: 'error',
            confirmButtonText: 'OK'
        })
})

function checkCedula() {

    if (cedula.value.length < 6 || cedula.value.length > 10)
        return false

    if (isNaN(cedula.value))
        return false


    return true
}

firstname.addEventListener("change", () => {
    if (!checkFirstname())
        Swal.fire({
            title: 'Error!',
            text: 'El nombre no puede estar vacio.',
            icon: 'error',
            confirmButtonText: 'OK'
        })

})

function checkFirstname() {

    console.log(firstname.value.length);

    if (firstname.value.length < 3)
        return false

    return true
}

lastname.addEventListener("change", () => {
    if (!checkLastname())
        Swal.fire({
            title: 'Error!',
            text: 'El apellido no puede estar vacio.',
            icon: 'error',
            confirmButtonText: 'OK'
        })

})

function checkLastname() {

    if (lastname.value.length < 3)
        return false


    return true
}

cellphone.addEventListener("change", () => {


    if (!checkCellphone())
        Swal.fire({
            title: 'Error!',
            text: 'El numero de telefono es incorrecto.',
            icon: 'error',
            confirmButtonText: 'OK'
        })


})

function checkCellphone() {
    /*if (cellphone.value != "")
        return true*/

    if (cellphone.value.length != 10)
        return false

    if (isNaN(cellphone.value))
        return false


    return true
}

tickets.addEventListener("change", () => {

    if (!checkTickets())
        Swal.fire({
            title: 'Error!',
            text: 'Revise que la cantidad de tickets sea correcta.',
            icon: 'error',
            confirmButtonText: 'OK'
        })
})

function checkTickets() {
    /* if (tickets.value == "")
 
         return false*/

    if (tickets.value < 1 || tickets.value > 10)
        return false


    return true
}

terms.addEventListener("change", () => {
    if (terms.checked)
        return true
    else {
        Swal.fire({
            title: 'Importante!',
            text: 'Debe de aceptar los terminos y condiciones para continuar.',
            icon: 'warning',
            confirmButtonText: 'OK'
        })
        return false
    }
})

function validateEmail(email) {
    return String(email).toLocaleLowerCase().match(/^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i)
}


function checkInputs() {

    console.log(checkCedula(), checkCellphone(), checkEmail(), checkFirstname(), checkLastname(), checkTickets())
    try {
        if (checkCedula() && checkCellphone() && checkEmail() && checkFirstname() && checkLastname() && checkTickets() && terms.checked)
            return true
        else
            return false

    } catch (error) {
        return false
    }
}

function updatePreferenceData() {
    let fd = new FormData(form);
    let req = Object.fromEntries(fd)
    req = JSON.stringify(req)
    var xhr = new XMLHttpRequest()
    return new Promise((resolve, reject) => {
        xhr.onreadystatechange = (e) => {
            if (xhr.readyState !== 4)
                return
            if (xhr.status === 200)
                resolve(JSON.parse(xhr.responseText))
            else
                resolve("Error")

        }
        xhr.open("POST", "server/main.php")
        xhr.setRequestHeader("Content-Type", "application/json")
        xhr.send(req)


    })

}

