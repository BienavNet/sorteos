<?php

require_once __DIR__.'/database.php';

$db = new Database();

$num_vendidos = $db->getAllTickets();

echo json_encode(["can"=> count($num_vendidos['tickets'])]);

