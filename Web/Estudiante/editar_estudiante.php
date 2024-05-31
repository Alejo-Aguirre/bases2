<?php
// Verificar si se recibieron datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Datos de conexión a Oracle
    $DB_USER = 'ALEJO';    // Usuario de la base de datos Oracle
    $DB_PASSWORD = 'root'; // Contraseña del usuario
    $DB_HOST = 'localhost/XE'; // Nombre del host y nombre de servicio

    // Establecer conexión a Oracle usando OCI8
    $conexion = oci_connect($DB_USER, $DB_PASSWORD, $DB_HOST);

    // Verificar la conexión
    if (!$conexion) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    // Obtener los datos del formulario
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];
    
    // Llamar al procedimiento almacenado
    $sql = "BEGIN editar_estudiante_proc(:p_id, :p_nombre, :p_apellido, :p_email, :p_usuario, :p_clave); END;";
    
    // Preparar la consulta
    $stmt = oci_parse($conexion, $sql);
    
    if (!$stmt) {
        $error = oci_error($conexion);
        $response = array(
            'status' => 'error',
            'message' => 'Error al preparar la consulta: ' . $error['message']
        );
    } else {
        // Vincular los parámetros
        oci_bind_by_name($stmt, ":p_id", $id);
        oci_bind_by_name($stmt, ":p_nombre", $nombre);
        oci_bind_by_name($stmt, ":p_apellido", $apellido);
        oci_bind_by_name($stmt, ":p_email", $email);
        oci_bind_by_name($stmt, ":p_usuario", $usuario);
        oci_bind_by_name($stmt, ":p_clave", $clave);
        
        // Ejecutar la consulta y verificar si se realizó correctamente
        if (oci_execute($stmt)) {
            // Si se actualizó correctamente, preparar la respuesta JSON
            $response = array(
                'status' => 'success',
                'message' => 'Estudiante actualizado correctamente'
            );
        } else {
            // Si ocurrió algún error
            $error = oci_error($stmt);
            $response = array(
                'status' => 'error',
                'message' => 'Error al actualizar el estudiante: ' . $error['message']
            );
        }
        
        // Liberar el statement
        oci_free_statement($stmt);
    }
    
    // Cerrar la conexión
    oci_close($conexion);
    
    // Enviar la respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
