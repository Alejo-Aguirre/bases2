<?php

class ConexionOracle {
    private $conexion;

    // Constantes para la conexión
    private $DB_USER = 'ALEJO';
    private $DB_PASSWORD = 'root';
    private $DB_HOST = 'localhost/XE'; // Este es el servicio Oracle SID

    function __construct() {
        // Conexión persistente a la base de datos Oracle
        $this->conexion = oci_pconnect($this->DB_USER, $this->DB_PASSWORD, $this->DB_HOST);
        if (!$this->conexion) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
            die();
        }
    }

    function getProfesores() {
        $query = "BEGIN obtener_profesores(:profesores_cursor); END;";
        $stmt = oci_parse($this->conexion, $query);

        if (!$stmt) {
            $e = oci_error($this->conexion);
            echo json_encode(array("status" => "error", "message" => "Error en la preparación del statement: " . htmlentities($e['message'])));
            return [];
        }

        $cursor = oci_new_cursor($this->conexion);
        oci_bind_by_name($stmt, ":profesores_cursor", $cursor, -1, OCI_B_CURSOR);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(array("status" => "error", "message" => "Error al ejecutar el statement: " . htmlentities($e['message'])));
            return [];
        }

        if (!oci_execute($cursor)) {
            $e = oci_error($cursor);
            echo json_encode(array("status" => "error", "message" => "Error al ejecutar el cursor: " . htmlentities($e['message'])));
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
}

// Ejemplo de uso:

// Crear una instancia de la conexión
$conexion = new ConexionOracle();

// Obtener profesores
$profesores = $conexion->getProfesores();

// Mostrar resultado (por ejemplo, convertido a JSON)
header('Content-Type: application/json');
echo json_encode($profesores);

?>
