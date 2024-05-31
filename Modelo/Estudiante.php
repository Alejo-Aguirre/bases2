<?php
class Estudiante {
    private $conexion;

    // Constantes para la conexión
    private $DB_USER = 'ALEJO';
    private $DB_PASSWORD = 'root';
    private $DB_HOST = '//localhost/XE';

    function __construct() {
        // Conexión persistente a la base de datos Oracle
        $this->conexion = oci_pconnect($this->DB_USER, $this->DB_PASSWORD, $this->DB_HOST);
        if (!$this->conexion) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        } else {
            echo "Conexión establecida correctamente.<br>";
        }
    }
   
    public function autenticar($usuario, $clave) {
        // Escapar las variables no es necesario en Oracle
        // Consulta para buscar al profesor en la base de datos
        $consulta = "SELECT * FROM estudiante WHERE usuario='$usuario' AND contra='$clave'";
    
        // Preparar y ejecutar la consulta
        $stmt = oci_parse($this->conexion, $consulta);
        oci_execute($stmt);
        
        // Verificar si se encontró al profesor
        if ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
            // El profesor fue encontrado
            return true;
        } else {
            // El profesor no fue encontrado
            return false;
        }
    }
    
    // Método para obtener el último ID registrado en la tabla de profesores
    public function obtenerUltimoId()
    {
        $sql = "SELECT MAX(id) AS ultimo_id FROM estudiante";
        $stmt = oci_parse($this->conexion, $sql);
        
        if (!$stmt) {
            $e = oci_error($this->conexion);
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        
        oci_execute($stmt);
        
        $row = oci_fetch_array($stmt, OCI_ASSOC);
        
        if ($row) {
            return $row['ULTIMO_ID']; // Suponiendo que el nombre de la columna es ULTIMO_ID
        } else {
            return 0; // Si no hay registros, devolvemos 0
        }
    }
    
    // Método para cerrar la conexión a la base de datos
    public function __destruct() {
        // Cerrar la conexión a Oracle
        oci_close($this->conexion);
    }
    
 /**
     * Función para crear un profesor llamando un procedimiento almacenado
     */
    public function crearEstudiante($id, $nombre, $apellido, $email, $usuario, $clave, $cedula, $telefono) {
        // Llamar al procedimiento almacenado en la base de datos
        $query = "BEGIN crear_estudiante_proc(:id, :nombre, :apellido, :email, :usuario, :clave, :cedula, :telefono); END;";
        $stmt = oci_parse($this->conexion, $query);

        // Vincular parámetros
        oci_bind_by_name($stmt, ':id', $id);
        oci_bind_by_name($stmt, ':nombre', $nombre);
        oci_bind_by_name($stmt, ':apellido', $apellido);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':usuario', $usuario);
        oci_bind_by_name($stmt, ':clave', $clave);
        oci_bind_by_name($stmt, ':cedula', $cedula);
        oci_bind_by_name($stmt, ':telefono', $telefono);

        // Ejecutar consulta
        $resultado = oci_execute($stmt);

        // Verificar errores
        if (!$resultado) {
            $e = oci_error($stmt);
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }

        // Liberar recursos
        oci_free_statement($stmt);

        return $resultado; // Devolver el resultado de la ejecución del procedimiento almacenado
    }
}
?>
