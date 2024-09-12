<?php

use MercadoPago\Client as Client;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig as MPC;
use MercadoPago\Resources\Preference;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMAILER\PHPMAILER\SMTP;
use PHPMailer\PHPMailer\Exception;


date_default_timezone_set("America/New_York");
require __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/database.php';

require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/SMTP.php";
require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/PHPMailer.php";
require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/Exception.php";


$access_token = "APP_USR-7208056047642010-012918-52a257a9492ca36c794be5d3529e7b37-194955219";
//$access_token = "APP_USR-2541754497073709-011518-6a04376f440243b9982ff82c3f458066-1186797523";

try {

    $mail = new PHPMailer(true);
    $incoming_data = json_decode(file_get_contents("php://input"), true);
    if (key_exists('data', $incoming_data)) {
        $payment_id = $incoming_data["data"]["id"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $access_token"
        ]);

        $response = curl_exec($ch);
        $response = json_decode($response, true);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if (!empty($response_code) && $response_code == "200") {
            //empezar a crear las variables para recoger la informacion y luego guardarla en la base de datos

            if (key_exists("status", $response) && $response["status"] == "approved" && $response['status_detail'] == 'accredited') {
                $order_id = (key_exists("order", $response)) ? $response["order"]["id"] : "";
                $date_approved = (key_exists("date_approved", $response)) ? $response["date_approved"] : "";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/merchant_orders/$order_id");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Authorization: Bearer $access_token"
                ]);
                $response = curl_exec($ch);
                $response = json_decode($response, true);
                $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (!empty($response_code) && $response_code == "200") {
                    $preference_id = (key_exists("preference_id", $response)) ? $response["preference_id"] : "";


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/checkout/preferences/$preference_id");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Content-Type: application/json",
                        "Authorization: Bearer $access_token"
                    ]);
                    $response = curl_exec($ch);
                    $response = json_decode($response, true);
                    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if (!empty($response_code) && $response_code == "200") {

                        $tickets = [];
                        $can_tickets = $response["items"][0]["quantity"];

                        $used_tickets = [];
                        $data = [
                            "payer" => [
                                "cedula" => $response["payer"]["identification"]["number"],
                                "name" => $response["payer"]["name"],
                                "surname" => $response["payer"]["surname"],
                                "phone" => $response["payer"]["phone"]["number"],
                                "email" => $response["payer"]["email"],
                            ],
                            "sell" => [
                                "order" => $order_id,
                                "canTicket" => $can_tickets,
                                "dateOrder" => $date_approved
                            ]
                        ];

                        $db = new Database($data);
                        $req = $db->existsComprador();
                        print_r($req);
                        if (!$req["error"]) {
                            if (!$req["exists"]) {
                                $req = $db->createComprador();
                                if (!$req["error"]) {
                                    $req = $db->createVenta();
                                    if (!$req["error"]) {

                                        #$req = $db->getAllTickets();
                                        $req = $db->noUsedTickets();

                                        if (!$req['error']) {
                                            $used_tickets = !empty($req['tickets']) ? $req['tickets'] : [];
                                            $formated_tickets = [];

                                            if (count($used_tickets) > 0)
                                                for ($i = 0; $i < count($used_tickets); $i++) {
                                                    array_push($formated_tickets, $used_tickets[$i]['ticket']);
                                                }

                                            $tickets = [];

                                            if ($data["payer"]["cedula"] == "1129564256")
                                                array_push($tickets, str_pad(9999, 4, "0", STR_PAD_LEFT));
                                            else
                                                #$tickets = getTickets($data['sell']['canTicket'], 1, [], $formated_tickets);
                                                $tickets = getTickets($can_tickets, $formated_tickets);
                                            $req = $db->createTicket($tickets);
                                            $db->ticketSold($tickets);
                                            $raw_tickets = $tickets;

                                            if ($req['error'] == false) {


                                                $tickets = createTickets($req['tickets']);

                                                $info = [
                                                    $data["sell"]["order"],
                                                    $data["payer"]["cedula"],
                                                    $data["payer"]["name"],
                                                    $data["payer"]["surname"],
                                                    $data["payer"]["email"],
                                                    $data["payer"]["phone"],
                                                    $data["sell"]["canTicket"],
                                                    $data["sell"]["canTicket"] * 100000,
                                                    $tickets
                                                ];

                                                $email_body = setHtml($info);

                                                $alt_body = "Gracias por su compra<br>Orden #" . $data["sell"]["order"] . "<br>Cedula: " . strval($data["payer"]["cedula"]) . "<br>Nombre: " . $data["payer"]["name"] . " " . $data["payer"]["surname"] . "<br>Correo: " . $data["payer"]["email"] . "<br>Telefono: " . strval($data["payer"]["phone"]) . "<br>Boletos:" . implode("<br>", $raw_tickets) . "";


                                                #sendEmail($mail, $email_body, $data['payer']['email'], $alt_body);
                                            } else {
                                                print_r("Error al crear los tickets");
                                            }
                                        }
                                    }/* else {

                                        $req = $db->existsVenta();

                                        if (!$req["error"] && $req['exists'] == true) {

                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, "https://tornilujosla40.com/server/intermediate.php");
                                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                "Content-Type: application/json",
                                                "Authorization: Bearer $access_token"
                                            ]);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                                                "id" => $order_id
                                            ]));

                                            $response = curl_exec($ch);
                                            curl_close($ch);
                                        } else {
                                            echo "Un error critico ha ocurrido, por favor contactese con el administrador del sitio web.";
                                        }
                                    }*/
                                }
                            } else {
                                $req = $db->createVenta();
                                if (!$req["error"]) {

                                    #$req = $db->getAllTickets();
                                    $req = $db->noUsedTickets();

                                    if (!$req['error']) {
                                        $used_tickets = !empty($req['tickets']) ? $req['tickets'] : [];
                                        $formated_tickets = [];

                                        if (count($used_tickets) > 0)
                                            for ($i = 0; $i < count($used_tickets); $i++) {
                                                array_push($formated_tickets, $used_tickets[$i]['ticket']);
                                            }

                                        if ($data["payer"]["cedula"] == "1129564256")
                                            array_push($tickets, str_pad(9999, 4, "0", STR_PAD_LEFT));
                                        else
                                            #$tickets = getTickets($data['sell']['canTicket'], 1, [], $formated_tickets);
                                            $tickets = getTickets($can_tickets, $formated_tickets);

                                        $raw_tickets = $tickets;
                                        $req = $db->createTicket($tickets);
                                        $db->ticketSold($tickets);

                                        if ($req['error'] == false) {

                                            $tickets = createTickets($req['tickets']);

                                            $info = [
                                                $data["sell"]["order"],
                                                $data["payer"]["cedula"],
                                                $data["payer"]["name"],
                                                $data["payer"]["surname"],
                                                $data["payer"]["email"],
                                                $data["payer"]["phone"],
                                                $data["sell"]["canTicket"],
                                                $data["sell"]["canTicket"] * 100000,
                                                $tickets
                                            ];

                                            $email_body = setHtml($info);

                                            $alt_body = "Gracias por su compra<br>Orden #" . $data["sell"]["order"] . "<br>Cedula: " . strval($data["payer"]["cedula"]) . "<br>Nombre: " . $data["payer"]["name"] . " " . $data["payer"]["surname"] . "<br>Correo: " . $data["payer"]["email"] . "<br>Telefono: " . strval($data["payer"]["phone"]) . "<br>Boletos:" . implode("<br>", $raw_tickets) . "";


                                            #sendEmail($mail, $email_body, $data['payer']['email'], $alt_body);
                                        } else {
                                            print_r("Error al crear los tickets");
                                        }
                                    }
                                } /*else {

                                    $req = $db->existsVenta();

                                    if (!$req["error"] && $req['exists'] == true) {

                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, "https://tornilujosla40.com/server/intermediate.php");
                                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                            "Content-Type: application/json",
                                            "Authorization: Bearer $access_token"
                                        ]);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                                            "id" => $order_id
                                        ]));

                                        $response = curl_exec($ch);
                                        curl_close($ch);
                                    } else {
                                        echo "Un error critico ha ocurrido, por favor contactese con el administrador del sitio web.";
                                    }
                                }*/
                            }
                        } else {
                            echo "No se pudo comprobar si el comprador existe";
                        }
                    } else
                        echo "Error al obtener los datos de la preferencia.";
                } else
                    echo "Error al obtener los datos de la orden";
            }
        } else
            print_r("No se pudo hacer la peticion");
    }
} catch (Exception $e) {
    echo "Hubo un error al procesar el webhook. " . $e->getMessage();
}





function createTickets($tickets)
{
    try {
        $boletos = "";

        for ($i = 0; $i < count($tickets); $i++) {
            $file = file_get_contents(__DIR__ . "/res/boletos.html");
            $file = str_replace("{BOLETO}", "" . $tickets[$i] . "", $file);
            $boletos .= $file;
        }


        return $boletos;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function setHtml($data)
{

    $to_replace = [
        "{ORDEN}",
        "{CEDULA}",
        "{NOMBRE}",
        "{APELLIDO}",
        "{CORREO}",
        "{TELEFONO}",
        "{CANTIDAD}",
        "{TOTAL}",
        "{BOLETOS}"
    ];

    $body = file_get_contents(__DIR__ . "/res/email.html");

    $body = str_replace($to_replace, $data, $body);

    return $body;
}


function sendEmail($mail, $body, $email, $alt_body)
{
    try {
        if (str_contains($email, "hotmail") || str_contains($email, "yahoo") || str_contains($email, "outlook"))
            $mail->Body = $alt_body;
        else
            $mail->Body = $body;


        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "testproof490@gmail.com";
        $mail->Password = "dwhp gwib sclz sfnp";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom("testproof490@gmail.com", "Tornilujos la 40");
        $mail->addAddress("$email");
        $mail->addAddress("testproof490@gmail.com");
        $mail->isHTML(true);
        $mail->Subject = "Entrega de Boletos";
        #$mail->Body = $body;
        $mail->AltBody = '';
        $mail->send();
        echo "No se produjo ningun error";
    } catch (Exception $e) {

        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "testproof490@gmail.com";
        $mail->Password = "dwhp gwib sclz sfnp";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom("testproof490@gmail.com", "Tornilujos la 40");
        $mail->addAddress("testproof490@gmail.com");
        $mail->isHTML(true);
        $mail->Subject = "Entrega de Boletos";
        $mail->Body = $body;
        $mail->AltBody = '';
        $mail->send();

        print_r($mail->ErrorInfo);
    }
}

function getTickets($cant = 1, $used_tickets)
{

    try {
        $tickets = ["8220"];

        if ($cant == 1 && in_array("8220", $used_tickets))
            return $tickets;
        else {
            if (in_array("8220", $used_tickets)) {
                for ($i = 1; $i < $cant; $i++) {
                    $random = random_int(0, count($used_tickets) - 1);
                    array_push($tickets, str_pad($used_tickets[$random], 4, "0", STR_PAD_LEFT));
                }
                return $tickets;
            } else {
                $tickets = [];
                for ($i = 0; $i < $cant; $i++) {
                    $random = random_int(0, count($used_tickets) - 1);
                    array_push($tickets, str_pad($used_tickets[$random], 4, "0", STR_PAD_LEFT));
                }
                return $tickets;
            }
        }
    } catch (\Throwable $th) {
        print_r($th->getMessage());
        return [];
    }
    //$banned_ticket = [8220, 32, 625, 1119, 9984, 1888, 7711, 2322, 1, 9999];
    //$porcentage  = round((count($used_tickets) / 10000) * 100);

    #$porcentage = 21;


    /*if ($actual_cant <= $cant) {
        $number = rand(1, 9998);
        if (in_array($number, $used_tickets))
            return getTickets($cant, $actual_cant, $tickets, $used_tickets);
        else {
            array_push($tickets, str_pad($number, 4, "0", STR_PAD_LEFT));
            array_push($used_tickets, str_pad($number, 4, "0", STR_PAD_LEFT));
            return getTickets($cant, $actual_cant + 1, $tickets, $used_tickets);
        }
    } else
        return $tickets;

    /*if ($porcentage >= 20) {
        $number = rand(1, 9998);
        if ($actual_cant <= $cant) {
            if (in_array($number, $used_tickets))
                return getTickets($cant, $actual_cant, $tickets, $used_tickets);
            else {
                array_push($tickets, str_pad($number, 4, "0", STR_PAD_LEFT));
                array_push($used_tickets, str_pad($number, 4, "0", STR_PAD_LEFT));
                return getTickets($cant, $actual_cant + 1, $tickets, $used_tickets);
            }
        } else
            return $tickets;
    } else {
        $number = rand(1, 9998);
        if ($actual_cant <= $cant)
            if (in_array($number, $banned_ticket))
                return getTickets($cant, $actual_cant, $tickets, $used_tickets);
            else {
                if (in_array($number, $used_tickets))
                    return getTickets($cant, $actual_cant, $tickets, $used_tickets);
                else {
                    array_push($tickets, str_pad($number, 4, "0", STR_PAD_LEFT));
                    array_push($used_tickets, str_pad($number, 4, "0", STR_PAD_LEFT));
                    return getTickets($cant, $actual_cant + 1, $tickets, $used_tickets);
                }
            }
        else
            return $tickets;
    }*/
}
