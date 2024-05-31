<?php
session_start();

require_once('../Modelo/Profesor.php');
require_once('../Modelo/Estudiante.php');
require_once('../Modelo/Administrador.php');

$response = array('success' => false, 'message' => 'Usuario o contraseña incorrectos.');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['usuario']) && isset($_POST['clave']) && isset($_POST['tipo_cuenta'])) {
        $usuario = $_POST['usuario'];
        $clave = $_POST['clave'];
        $tipo_cuenta = $_POST['tipo_cuenta'];

        $user = null;

        if ($tipo_cuenta === 'profesor') {
            $user = new Profesor();
        } elseif ($tipo_cuenta === 'estudiante') {
            $user = new Estudiante();
        } elseif ($tipo_cuenta === 'admin') {
            $user = new Administrador();
        } else {
            $response['message'] = "Tipo de cuenta no válido.";
            echo json_encode($response);
            exit();
        }

        $autenticado = $user->autenticar($usuario, $clave);

        if ($autenticado) {
            $_SESSION['loggedIn'] = true;
            $_SESSION['username'] = $usuario;
            $_SESSION['tipo_cuenta'] = $tipo_cuenta;

            $response['success'] = true;
            $response['username'] = $usuario;
            $response['tipo_cuenta'] = $tipo_cuenta;
            echo json_encode($response);
            exit();
        } else {
            $response['message'] = 'Usuario o contraseña incorrectos.';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['message'] = "No se recibieron los datos completos del formulario.";
        echo json_encode($response);
        exit();
    }
}

echo json_encode($response);
?>
