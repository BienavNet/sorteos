<?php
require_once __DIR__ . '/server/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig as MPC;
use MercadoPago\Client\Preference\PreferenceClient;

require_once __DIR__ . '/server/database.php';

$access_token = "";

MPC::setAccessToken($access_token);
$preference = new PreferenceClient();

$preference = $preference->create([
    "items" => array(
        array(
            "title" => "Ticket Sorteo",
            "description" => "Compra de ticket, sorteo tractomula.",
            "quantity" => 1,
            "unit_price" => 100000
        )
    )
]);

$db = new Database();

$num_vendidos = $db->getAllTickets();



//$preference = "1186797523-49fd7f7c-f7d1-4a00-90e0-09138372c5e8";
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script src="https://kit.fontawesome.com/36ec4adb57.js" crossorigin="anonymous"></script>


    <title>Sorteo</title>
</head>

<body class="pt-4">

    <input type="hidden" id="action" value="<?php

                                            try {
                                                if (isset($_GET["action"]) && !empty($_GET["action"])) {
                                                    echo $_GET['action'];
                                                }
                                            } catch (\Throwable $th) {
                                                //throw $th;
                                            }
                                            ?>">




    <div class="container-fluid" id="main">
        <div class="redes-layout d-flex flex-column">
            <span class="redes whatsapp">
                <a href="https://wa.link/1bym1y"><i class="fa-brands fa-whatsapp fa-2xl" style="color: #ffffff;"></i></a>
            </span>
            <!--<span class="redes facebook">
                <a href="http://"><i class="fa-brands fa-facebook fa-2xl" style="color: #ffffff;"></i></a>
            </span>-->
            <span class="redes instagram">
                <a href="https://www.instagram.com/tornilujosla40_aguachica/?igsh=aGI3MHpjaTNxZzFp&utm_source=qr"><i class="fa-brands fa-instagram fa-2xl" style="color: #ffffff;"></i></i></a>
            </span>
        </div>


        <div class="row justify-content-center mt-md-1 mb-3 mb-md-0">
            <div class="col-md-7 mt-3 my-md-0 d-flex justify-content-center" id="info-container">
                <div class="container h-100 carousel slide" id="carousel" data-ride="carousel" data-interval="7000">
                    <ol class="carousel-indicators">
                        <li data-target="#carousel" data-slide-to="0" class="active"></li>
                        <li data-target="#carousel" data-slide-to="1"></li>
                        <li data-target="#carousel" data-slide-to="2"></li>
                        <li data-target="#carousel" data-slide-to="3"></li>
                    </ol>
                    <div class="carousel-inner h-100">
                        <div class="carousel-item active h-100">
                            <img src="img/img1.jpg" alt="..." class="rounded">
                            <div class="carousel-caption d-block h-75">
                            </div>
                        </div>
                        <div class="carousel-item h-100">
                            <img src="img/img2.jpg" alt="...">
                            <div class="carousel-caption d-block h-75">
                            </div>
                        </div>
                        <div class="carousel-item h-100">
                            <img src="img/img3.jpg" alt="...">
                            <div class="carousel-caption d-block h-75">
                            </div>
                        </div>
                        <div class="carousel-item h-100">
                            <img src="img/img4.jpg" alt="...">
                            <div class="carousel-caption d-block h-75">
                            </div>
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#carousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>

                </div>

            </div>
            <div class="col-sm-4 mx-sm-1 my-3 my-md-0 " id="payment-container">

                <div class="row">
                    <div class="container-fluid d-flex justify-content-center h-100 ">
                        <form class="container p-5 p-md-0" id="formulario">
                            <div class="container d-flex justify-content-center align-top my-2" id="title">
                                <div class="branding">
                                </div>
                            </div>
                            <div class="row">
                                <div class="container d-flex justify-content-center">
                                    <div class="container">
                                        <h4>Formulario de Pago</h4>

                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fa-solid fa-envelope"></i> Correo Electronico:</label>
                                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Ingresa tu correo">
                                <small id="emailHelp" class="form-text text-muted">Recuerda ingresar un correo real, AHI
                                    LLEGA TU CUPO.</small>
                            </div>
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col">
                                        <label for="cedula"><i class="fa-solid fa-id-card"></i> Cedula:</label>
                                        <input type="text" name="cedula" id="cedula" class="form-control" placeholder="1005020000">
                                        <small id="ccHelp" class="form-text text-muted">IMPORTANTE: Se solicitara para
                                            entregar el premio</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col">
                                        <label for="name"><i class="fa-solid fa-person"></i> Nombre:</label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="Ingresa tu nombre">
                                    </div>
                                    <div class="col">
                                        <label for="lastname"><i class="fa-solid fa-person"></i> Apellido:</label>
                                        <input type="text" name="lastname" id="lastname" class="form-control" placeholder="Ingresa tu apellido">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col">
                                        <label for="cellphone"><i class="fa-solid fa-mobile"></i> Numero telefonico:</label>
                                        <input type="text" name="cellphone" id="cellphone" class="form-control" placeholder="310 212 2222">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col my-1">
                                        <label for="tickets"><i class="fa-solid fa-ticket"></i> Cantidad de puestos:</label>
                                        <input type="number" name="tickets" id="tickets" value="1" min="1" max="10" class="form-control">
                                    </div>
                                    <div class="col">
                                        <div class="row d-flex justify-content-end mr-5">
                                            <span><b><i class="fa-solid fa-money-bill"></i> Subtotal</b></span>
                                        </div>
                                        <div class="row d-flex justify-content-end mr-5">
                                            <div class="col d-flex justify-content-end align-middle">
                                                <span id="price"> $100.000</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="form-check my-4">
                                <input type="checkbox" class="form-check-input" id="terms">
                                <label class="form-check-label" for="exampleCheck1">Aceptar los terminos y condiciones.
                                    <a href="http://">Puedes descargarlos aqui.</a></label>
                            </div>
                            <div class="form-check">
                                <div class="progress">
                                    <div class="progress-bar" id="bar" role="progressbar" style="color: black; width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small id="emailHelp" class="form-text text-muted">Boletos vendidos al dia de hoy.</small>
                            </div>
                            <div class="form-row">
                                <div class="col">
                                    <input type="hidden" name="id_payment" id="id_payment" value="<?php echo $preference->id ?>">
                                    <input type="hidden" id="vendidos" value="0">

                                    <div id="wallet_container" class="d-none"></div>
                                </div>
                            </div>
                        </form>



                    </div>
                </div>
            </div>
        </div>


    </div>






    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script src="https://sdk.mercadopago.com/js/v2"></script>

    <script src="main.js"></script>






    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lemon&family=Questrial&display=swap');

        body {
            min-height: 100vh;
            min-width: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /*background: #00246b;*/
            /*background: #8ab6f9;*/
        }


        h5 {
            font-size: 2.5rem;
            font-family: "Lemon", serif;
            font-weight: .8rem;
            font-style: normal;
            text-align: center;
        }

        h4 {
            font-size: 2.5rem;
            font-weight: bolder;
            font-family: "Questrial", serif;
            font-weight: normal;
            font-style: normal;
            text-align: center;

        }

        p {
            font-size: 1rem;
            font-family: "Lemon", serif;
            font-weight: normal;
            font-style: normal;
            text-align: justify;
        }

        label {
            font-style: normal;
            font-weight: bolder;
            border-bottom: 1px solid rgba(0, 0, 0, 0.2);
        }

        #title {
            margin-bottom: .5rem;
        }


        .branding {
            width: 6rem;
            height: 6rem;
            border-radius: 50%;
            position: relative;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            background: url("img/brand_logo.jpg");
            background-size: contain;
            z-index: 1;

        }

        .redes-layout {
            width: 10vh;
            height: 20vh;
            position: absolute;
            align-self: self-end;
            top: 50%;
            z-index: 12;
        }

        .redes {
            height: 3.5rem;
            width: 3.5rem;
            position: sticky;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            margin: .2rem;

        }

        .whatsapp {
            background-color: green;
        }

        .facebook {
            background-color: blue;
        }

        .instagram {
            background-color: #C13584;
        }


        .form-control {
            background-color: none;
            border-top: 10px;
            border-right: 10px;
            border-left: 10px;

        }

        .form-group {
            margin-top: .6rem;
            margin-bottom: .6rem;
            font-family: "Questrial", serif;
            font-weight: normal;
            font-style: normal;
            padding: .6rem;
            border: 2px gray;
            border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            /*background-color: #cadcfc;*/

        }

        .btn-success {
            width: 100%;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: invert(1);
        }

        .carousel-indicators li {
            background-color: black;
        }

        .carousel-caption {
            color: black;
        }

        .carousel-item {
            background-color: white;
            background-size: cover;
            overflow: hidden;
        }


        .carousel-item img {
            flex-shrink: 0;
            width: 100%;
            height: 100%;
        }

        .carousel-inner {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            border-radius: 15px;
        }

        .carousel {
            background: rgba(128, 128, 128, 0) !important;
        }



        #info-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 95vh;
            background: rgba(128, 128, 128, 0);

        }

        #payment-container {
            min-height: 95vh;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            border-radius: 15px;
            background-color: white;

        }
    </style>
</body>

</html>
