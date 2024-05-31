<?php
// Clase para manejar la conexión y consultas a Oracle
class ConexionOracle {
    private $conexion;
    
    // Constructor
    function __construct($DB_USER, $DB_PASSWORD, $DB_HOST) {
        $this->conexion = oci_connect($DB_USER, $DB_PASSWORD, $DB_HOST);
        if (!$this->conexion) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
    }

    // Método para obtener profesores
   // Método para obtener profesores
function getProfesores() {
    $query = "BEGIN llenar_profesores_combo(:profesores_cursor); END;";
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
        // Dividir la cadena "NOMBRE APELLIDO" en nombre y apellido
        $nombre_apellido = $row['NOMBRE_APELLIDO'];
        $parts = explode(' ', $nombre_apellido);
        $nombre = $parts[0];
        $apellido = implode(' ', array_slice($parts, 1));

        // Obtener el ID del profesor
        $id_profesor = $row['ID'];

        // Crear un array asociativo con el ID y el nombre completo del profesor
        $profesor = [
            'ID' => $id_profesor,
            'NOMBRE' => $nombre . ' ' . $apellido
        ];

        // Agregar el profesor al array de profesores
        $profesores[] = $profesor;
    }
    oci_free_statement($stmt);
    oci_free_statement($cursor);

    return $profesores;

    }

    // Método para obtener horarios utilizando el cursor en la base de datos
    function getHorarios() {
        $query = "BEGIN obtener_horarios(:horarios_cursor); END;";
        $stmt = oci_parse($this->conexion, $query);

        $horarios_cursor = oci_new_cursor($this->conexion);
        oci_bind_by_name($stmt, ":horarios_cursor", $horarios_cursor, -1, OCI_B_CURSOR);

        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(array("status" => "error", "message" => htmlentities($e['message'])));
            return [];
        }

        $horarios = [];
        oci_execute($horarios_cursor); // Ejecutar el cursor
        while ($row = oci_fetch_assoc($horarios_cursor)) {
            $horarios[] = $row;
        }

        oci_free_statement($stmt);
        oci_free_statement($horarios_cursor);

        return $horarios;
    }

    // Método para insertar una nueva materia utilizando un procedimiento almacenado
    function insertarMateria($id, $nombre, $id_profesor, $id_horario) {
        $query = "BEGIN crear_materia(:p_id, :p_nombre, :p_id_profesor, :p_id_horario); END;";
        $stmt = oci_parse($this->conexion, $query);

        // Vincular parámetros
        oci_bind_by_name($stmt, ':p_id', $id);
        oci_bind_by_name($stmt, ':p_nombre', $nombre);
        oci_bind_by_name($stmt, ':p_id_profesor', $id_profesor);
        oci_bind_by_name($stmt, ':p_id_horario', $id_horario);

        echo "Valores de los parámetros:\n";
        echo "ID: $id\n";
        echo "Nombre: $nombre\n";
        echo "ID del profesor: $id_profesor\n";
        echo "ID del horario: $id_horario\n";
        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            echo json_encode(array("status" => "error", "message" => htmlentities($e['message'])));
            return false;
        }

        oci_free_statement($stmt);

        return true;
    }


    // Destructor
    function __destruct() {
        oci_close($this->conexion);
    }
}

// Datos de conexión a Oracle
$DB_USER = 'ALEJO';    // Usuario de la base de datos Oracle
$DB_PASSWORD = 'root'; // Contraseña del usuario
$DB_HOST = 'localhost/XE'; // Nombre del host y nombre de servicio

// Crear instancia de la conexión a Oracle
$conexionOracle = new ConexionOracle($DB_USER, $DB_PASSWORD, $DB_HOST);

// Obtener la lista de profesores y horarios
$profesores = $conexionOracle->getProfesores();
$horarios = $conexionOracle->getHorarios();

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si los datos del formulario fueron enviados
    $id = (int)$_POST['id']; // Convertir a entero si es necesario
    $nombre = $_POST['nombre']; // Asumiendo que es VARCHAR2
    $id_profesor = (int)$_POST['profesor']; // Convertir a entero si es necesario
    $id_horario = (int)$_POST['horario']; // Convertir a entero si es necesario
    

    // Insertar la nueva materia en la base de datos
    if ($conexionOracle->insertarMateria($id, $nombre, $id_profesor, $id_horario)) {
        // Redirigir después de guardar
        header('Location: crear_materia.php?guardado=1');
        exit;
    } else {
        // Manejo de error
        echo json_encode(array("status" => "error", "message" => "Error al guardar la materia."));
        exit;
    }
}

// Generar las opciones de profesores y horarios para el formulario HTML
// Generar las opciones de profesores para el formulario HTML
$options_profesores = '';
foreach ($profesores as $profesor) {
    $options_profesores .= '<option value="' . $profesor['ID'] . '">' . $profesor['NOMBRE'] . '</option>';
}


$options_horarios = '';
foreach ($horarios as $horario) {
    $options_horarios .= '<option value="' . $horario['ID'] . '">' . $horario['HORARIO_COMPLETO'] . '</option>';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estudiantes</title>
    <!-- Incluye jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Luego incluye Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .table-wrapper {
            max-height: 500px;
            overflow-x: auto;
        }
        .create-button {
            text-align: center;
            margin-top: 20px;
        }
        .navbar .nav-item.active {
            background-color: #343a40; /* Color de fondo para el elemento activo */
        }
        .navbar .nav-item.active .nav-link {
            color: #ffffff !important; /* Texto en blanco para el elemento activo */
        }
        .navbar .ml-auto {
            margin-left: auto !important; /* Alineación a la derecha del contenido en la barra de navegación */
        }
        .navbar .usuario-info {
            color: #ffffff; /* Color del texto */
            margin-right: 10px; /* Espacio a la derecha */
        }
        .navbar .btn-usuario {
            color: #ffffff !important;
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            margin-left: 10px;
        }
        .navbar .btn-usuario:hover {
            background-color: #e0a800 !important;
            border-color: #d39e00 !important;
        }
        .navbar .btn-cerrar-sesion {
            color: #ffffff !important;
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            margin-left: 10px;
        }
        .navbar .btn-cerrar-sesion:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
        }
    </style>
</head>
<body>
    <!-- Encabezado con navegación -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <!-- Logo y nombre del programa -->
                <a class="navbar-brand" href="../index.html">
                    <img src="../class.png" width="30" height="30" class="d-inline-block align-top" alt="">
                    Classroom
                </a>
                <!-- Navegación -->
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../Profesor/mainProfesoresController.php">Profesores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Estudiante/mainEstudiantesController.php">Estudiantes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Materia/crear_materia.php">Materias</a>
                    </li>
                </ul>
                <!-- Información de usuario y botones -->
                <div class="ml-auto">
                    <a href="#" class="btn btn-usuario" id="miPerfilBtn">Mi Perfil</a>
                    <a href="#" class="btn btn-cerrar-sesion" id="cerrarSesionBtn">Cerrar Sesión</a>
                </div>
            </div>
        </nav>
    </header>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Materia</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Crear Nueva Materia</h2>
        <form action="crear_materia.php" method="post">
            <div class="form-group">
                <label for="id">ID:</label>
                <input type="text" class="form-control" id="id" name="id" required>
            </div>
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="profesor">Profesor:</label>
                <select class="form-control" id="profesor" name="profesor" required>
                    <option value="">Seleccione un profesor</option>
                    <?php echo $options_profesores; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="horario">Horario:</label>
                <select class="form-control" id="horario" name="horario" required>
                    <option value="">Seleccione un horario</option>
                    <?php echo $options_horarios; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>

    <!-- Bootstrap JS y dependencias opcionales (jQuery, Popper.js) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
