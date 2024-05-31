<?php

class MainProfesoresController {
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

    function getProfesores() {
        $query = "BEGIN obtener_profesores(:profesores_cursor); END;";
        $stmt = oci_parse($this->conexion, $query);

        $cursor = oci_new_cursor($this->conexion);
        oci_bind_by_name($stmt, ":profesores_cursor", $cursor, -1, OCI_B_CURSOR);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(array("status" => "error", "message" => htmlentities($e['message'])));
            return [];
        }

        if (!oci_execute($cursor)) {
            $e = oci_error($cursor);
            echo json_encode(array("status" => "error", "message" => htmlentities($e['message'])));
            return [];
        }

        $profesores = [];
        while ($row = oci_fetch_assoc($cursor)) {
            $profesores[] = $row;
        }

        oci_free_statement($stmt);
        oci_free_statement($cursor);

        return $profesores;
    }

    /**
     * Método para editar un profesor utilizando un procedimiento almacenado en la base de datos
     */
    public function editarProfesor($id, $nombre, $apellido, $email, $usuario, $clave) {
        // Llamar al procedimiento almacenado en la base de datos
        $query = "BEGIN editar_profesor_proc(:id, :nombre, :apellido, :email, :usuario, :clave); END;";
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
    

    function getProfesorById($id)
    {
        // Método para obtener un profesor por su ID
        $query = "SELECT * FROM profesores WHERE ID = :id";
        $stmt = oci_parse($this->conexion, $query);
        oci_bind_by_name($stmt, ':id', $id);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(["status" => "error", "message" => htmlentities($e['message'])]);
            return false;
        }

        $profesor = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        return $profesor;
    }
    /**
     * funcion para eliminar un profesor
     */
    function deleteProfesor($id) {
        $query = "BEGIN eliminar_profesor(:id); END;";
        $stmt = oci_parse($this->conexion, $query);
        oci_bind_by_name($stmt, ':id', $id);
        
        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(array("status" => "error", "message" => htmlentities($e['message'])));
            return false;
        }

        oci_free_statement($stmt);

        // Obtener la lista actualizada de profesores
        $profesores = $this->getProfesores();

        echo json_encode(array("status" => "success", "profesores" => $profesores));
        return true;
    }

    function closeConnection() {
        oci_close($this->conexion);
    }
}

// Crear una instancia de la clase MainProfesoresController
$controller = new MainProfesoresController();

// Verificar si se ha enviado el ID para eliminar un profesor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $deleted = $controller->deleteProfesor($_POST['delete_id']);
    if (!$deleted) {
        echo json_encode(array("status" => "error", "message" => "Error al eliminar el profesor."));
    }
    exit();
}

//Verificar si se ha enviado el formulario para editar un profesor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $edited = $controller->editarProfesor($_POST['id'], $_POST['nombre'], $_POST['apellido'], $_POST['email'], $_POST['usuario'],$_POST['clave']);
    if ($edited) {
        // Obtener la lista de profesores actualizada
        $profesores = $controller->getProfesores();
        echo json_encode(["status" => "success", "profesores" => $profesores]);
        exit();
    } else {
        // Manejo de error
        echo json_encode(["status" => "error", "message" => "Error al editar el profesor."]);
    }
}

// Verificar si se ha enviado el ID para obtener los datos de un profesor
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $profesor = $controller->getProfesorById($_GET['id']);
    if ($profesor) {
        echo json_encode(["status" => "success", "profesor" => $profesor]);
        exit();
    } else {
        // Manejo de error
        echo json_encode(["status" => "error", "message" => "Error al obtener los datos del profesor."]);
    }
}


// Obtener la lista de profesores
$profesores = $controller->getProfesores();

// Cerrar la conexión
$controller->closeConnection();

// Incluir el archivo HTML que muestra la tabla de profesores
include 'MainProfesor.html';
?>
