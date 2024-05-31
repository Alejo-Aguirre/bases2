<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '/../Modelo/Estudiante.php'; // Ruta correcta al archivo Profesor.php
    $modelo = new Estudiante(); // Crear una instancia de la clase Profesor

    // Recibir datos del formulario
    $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : null;
    $apellido = isset($_POST['apellido']) ? $_POST['apellido'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : null;
    $usuario = isset($_POST['usuario']) ? $_POST['usuario'] : null;
    $clave = isset($_POST['clave']) ? $_POST['clave'] : null;

    // Verificar si se recibieron todos los datos necesarios
    if ($cedula && $nombre && $apellido && $email && $telefono && $usuario && $clave) {
        // Obtener el último ID registrado y sumarle 1
        $ultimo_id = $modelo->obtenerUltimoId();
        $id = str_pad($ultimo_id + 1, 3, '0', STR_PAD_LEFT); // Formatear el ID a tres dígitos

        // Guardar el profesor en la base de datos
        $guardado = $modelo->crearEstudiante($id,$cedula, $nombre, $apellido, $email, $telefono, $usuario, $clave );

    
        if ($guardado) {
            echo "<script>alert('Estudiante registrado correctamente.');</script>";
            header("Location: ../Web/index.html");
            exit();
        } else {
            echo "<script>alert('Error al registrar el estudiante.');</script>";
        }
    } else {
        echo "<script>alert('Por favor completa todos los campos.');</script>";
    }
}
?>
