<?php

date_default_timezone_set("America/New_York");
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\Client as Client;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig as MPC;
use MercadoPago\Resources\Preference;


$access_token = "";

MPC::setAccessToken($access_token);
//$preference = new MercadoPago\Resources\Preference();
$preference = new PreferenceClient();
$mpc = new Client\Preference\PreferenceClient();




//print_r($_POST);
$data = file_get_contents('php://input');


if (!empty($data)) {
    $input_stream = json_decode($data, true);
    $hour = date('H');
    $minute = date('m');
    $seconds = date('i');
    


    $post_data = json_encode([
        "items" => [
            [
                "title" => "Boleto Sorteo Tractomula",
                "description" => "Compra de los boletos para el sorteo de la tractomula.",
                "quantity" =>  intval($input_stream['tickets']),
                "unit_price" => 100000
            ]
        ],
        "payer" => [
            "name" => $input_stream['name'],
            "surname" => $input_stream['lastname'],
            "email" => $input_stream['email'],
            "phone" => array(
                "area_code" => "+57",
                "number" => $input_stream['cellphone']
            ),
            "identification" => array(
                "type" => "CC",
                "number" => $input_stream['cedula']
            ),
            "address" => array(
                "street_name" => "Street",
                "street_number" => 123,
                "zip_code" => "5570"
            )
        ],
        "back_urls" => [
            "success" => "https://tornilujosla40.com/?action=success",
            "failure" => "https://tornilujosla40.com/?action=declined",
            "pending" => "https://tornilujosla40.com/?action=pending"
        ],
        "payment_methods" => [
            "excluded_payment_types" => [
                [
                    "id" => "ticket"
                ]
            ],
            "installments" => 1,
            "default_installments" => 1
        ],
        "notification_url" => "https://webhook.site/a8bd2638-c8da-44f3-8a3a-93b51319caf3",
        "expires" => true,
        "expiration_date_from" => date(DATE_ATOM ,mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'))),
        "expiration_date_to" => date(DATE_ATOM ,mktime(date('H'), date('i')+30, date('s'), date('m'), date('d'), date('Y'))),

    ]);


    $url = "https://api.mercadopago.com/checkout/preferences/" . $input_stream["id_payment"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json; charset=utf-8",
        "Authorization: Bearer $access_token",
        "Accept: */*"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    

    print_r($response);

    if (!empty($response)) {
        if ($http_code == 200) {
            print_r($response);
        } else
            echo "Error al realizar la peticion";
    } else
        echo "La peticion no fue correcta";




    //echo json_encode(["id" => $preference->id]);
}
