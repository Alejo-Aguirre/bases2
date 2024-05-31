<?php
session_start();
$response = array(
    'loggedIn' => false,
    'username' => '',
    'tipo_cuenta' => ''
);

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    $response['loggedIn'] = true;
    $response['username'] = $_SESSION['username'];
    $response['tipo_cuenta'] = $_SESSION['tipo_cuenta'];
}

echo json_encode($response);
?>
