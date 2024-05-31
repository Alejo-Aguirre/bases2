<?php

class MainEstudiantesController {
    private $conexion;

    // Constantes para la conexión
    private $DB_USER = 'ALEJO';
    private $DB_PASSWORD = 'root';
    private $DB_HOST = 'localhost/XE';

    function __construct() {
        // Conexión persistente a la base de datos Oracle
        $this->conexion = oci_pconnect($this->DB_USER, $this->DB_PASSWORD, $this->DB_HOST);
        if (!$this->conexion) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
    }

    function getEstudiantes() {
        $query = "BEGIN obtener_estudiantes(:estudiantes_cursor); END;";
        $stmt = oci_parse($this->conexion, $query);

        $cursor = oci_new_cursor($this->conexion);
        oci_bind_by_name($stmt, ":estudiantes_cursor", $cursor, -1, OCI_B_CURSOR);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(["status" => "error", "message" => htmlentities($e['message'])]);
            return [];
        }

        if (!oci_execute($cursor)) {
            $e = oci_error($cursor);
            echo json_encode(["status" => "error", "message" => htmlentities($e['message'])]);
            return [];
        }

        $estudiantes = [];
        while ($row = oci_fetch_assoc($cursor)) {
            $estudiantes[] = $row;
        }

        oci_free_statement($stmt);
        oci_free_statement($cursor);

        return $estudiantes;
    }

    /**
     * Método para editar un estudiante utilizando un procedimiento almacenado en la base de datos
     */
    public function editarEstudiante($id, $nombre, $apellido, $email, $usuario, $clave) {
        // Llamar al procedimiento almacenado en la base de datos
        $query = "BEGIN editar_estudiante_proc(:id, :nombre, :apellido, :email, :usuario, :clave); END;";
        $stmt = oci_parse($this->conexion, $query);

        // Vincular parámetros
        oci_bind_by_name($stmt, ':id', $id);
        oci_bind_by_name($stmt, ':nombre', $nombre);
        oci_bind_by_name($stmt, ':apellido', $apellido);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':usuario', $usuario);
        oci_bind_by_name($stmt, ':clave', $clave);

        // Ejecutar consulta
        $resultado = oci_execute($stmt);

        // Verificar errores
        if (!$resultado) {
            $e = oci_error($stmt);
            echo json_encode(["status" => "error", "message" => htmlentities($e['message'])]);
            return false;
        }

        // Liberar recursos
        oci_free_statement($stmt);

        return true; // Devolver el resultado de la ejecución del procedimiento almacenado
    }

    function getEstudianteById($id) {
        // Método para obtener un estudiante por su ID
        $query = "SELECT * FROM estudiante WHERE ID = :id";
        $stmt = oci_parse($this->conexion, $query);
        oci_bind_by_name($stmt, ':id', $id);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(["status" => "error", "message" => htmlentities($e['message'])]);
            return false;
        }

        $estudiante = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        return $estudiante;
    }

    /**
     * Función para eliminar un estudiante
     */
    function deleteEstudiante($id) {
        $query = "BEGIN eliminar_estudiante(:id); END;";
        $stmt = oci_parse($this->conexion, $query);
        oci_bind_by_name($stmt, ':id', $id);
        
        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(["status" => "error", "message" => htmlentities($e['message'])]);
            return false;
        }

        oci_free_statement($stmt);

        // Obtener la lista actualizada de estudiantes
        $estudiantes = $this->getEstudiantes();

        echo json_encode(["status" => "success", "estudiantes" => $estudiantes]);
        return true;
    }

    function closeConnection() {
        oci_close($this->conexion);
    }
}

// Crear una instancia de la clase MainEstudiantesController
$controller = new MainEstudiantesController();

// Verificar si se ha enviado el ID para eliminar un estudiante
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $deleted = $controller->deleteEstudiante($_POST['delete_id']);
    if (!$deleted) {
        echo json_encode(["status" => "error", "message" => "Error al eliminar el estudiante."]);
    }
    exit();
}

// Verificar si se ha enviado el formulario para editar un estudiante
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $edited = $controller->editarEstudiante(
        $_POST['id'],
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['email'],
        $_POST['usuario'],
        $_POST['clave']
    );
    if ($edited) {
        // Obtener la lista de estudiantes actualizada
        $estudiantes = $controller->getEstudiantes();
        echo json_encode(["status" => "success", "estudiantes" => $estudiantes]);
        exit();
    } else {
        // Manejo de error
        echo json_encode(["status" => "error", "message" => "Error al editar el estudiante."]);
    }
}

// Verificar si se ha enviado el ID para obtener los datos de un estudiante
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $estudiante = $controller->getEstudianteById($_GET['id']);
    if ($estudiante) {
        echo json_encode(["status" => "success", "estudiante" => $estudiante]);
        exit();
    } else {
        // Manejo de error
        echo json_encode(["status" => "error", "message" => "Error al obtener los datos del estudiante."]);
    }
}

// Obtener la lista de estudiantes
$estudiantes = $controller->getEstudiantes();

// Cerrar la conexión
$controller->closeConnection();

// Incluir el archivo HTML que muestra la tabla de estudiantes
include 'MainEstudiante.html';
?>
