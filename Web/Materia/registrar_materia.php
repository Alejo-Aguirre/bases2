<?php
// Datos de conexión a Oracle
$DB_USER = 'ALEJO';
$DB_PASSWORD = 'root';
$DB_HOST = 'localhost/XE';

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

    // Método para obtener materias disponibles
    function getMaterias() {
        $query = "SELECT id, nombre FROM materia";
        $stmt = oci_parse($this->conexion, $query);
        oci_execute($stmt);

        $materias = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $materias[] = $row;
        }
        oci_free_statement($stmt);
        return $materias;
    }
        // Método para obtener la conexión
        public function getConexion() {
            return $this->conexion;
        }

    // Método para obtener horarios de una materia
    function getHorariosMateria($id_materia) {
        $query = "SELECT h.id, h.dia_semana, h.hora_inicio, h.hora_fin
                  FROM horario h
                  INNER JOIN materia_horario mh ON h.id = mh.id_horario
                  WHERE mh.id_materia = :id_materia";
        $stmt = oci_parse($this->conexion, $query);
        oci_bind_by_name($stmt, ":id_materia", $id_materia);
        oci_execute($stmt);

        $horarios = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $horarios[] = [
                'ID' => $row['ID'],
                'HORARIO_COMPLETO' => $row['DIA_SEMANA'] . ' ' . $row['HORA_INICIO'] . '-' . $row['HORA_FIN']
            ];
        }
        oci_free_statement($stmt);
        return $horarios;
    }

    // Destructor
    function __destruct() {
        oci_close($this->conexion);
    }
}

// Crear instancia de la conexión a Oracle
$conexionOracle = new ConexionOracle($DB_USER, $DB_PASSWORD, $DB_HOST);

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['materia_id'])) {
        $materia_id = $_POST['materia_id'];
        $horarios = $conexionOracle->getHorariosMateria($materia_id);
        header('Content-Type: application/json');
        echo json_encode($horarios);
        exit;
    } else {
        $id_estudiante = (int)$_POST['id_estudiante'];
        $id_materia = (int)$_POST['materia'];
        $id_horario = (int)$_POST['horario'];

        $query = "INSERT INTO Materia_Estudiante (id_estudiante, id_materia)
                  VALUES (:id_estudiante, :id_materia)";
       $stmt = oci_parse($conexionOracle->getConexion(), $query);
        oci_bind_by_name($stmt, ":id_estudiante", $id_estudiante);
        oci_bind_by_name($stmt, ":id_materia", $id_materia);
        oci_execute($stmt);
        oci_free_statement($stmt);

        header('Location: registrar_materia.php?guardado=1');
        exit;
    }
}

// Obtener la lista de materias
$materias = $conexionOracle->getMaterias();
$options_materias = '';
foreach ($materias as $materia) {
    $options_materias .= '<option value="' . $materia['ID'] . '">' . $materia['NOMBRE'] . '</option>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Materia</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Registrar Materia</h2>
        <form action="registrar_materia.php" method="post">
            <div class="form-group">
                <label for="id_estudiante">ID Estudiante:</label>
                <input type="text" class="form-control" id="id_estudiante" name="id_estudiante" required>
            </div>
            <div class="form-group">
                <label for="materia">Materia:</label>
                <select class="form-control" id="materia" name="materia" required>
                    <option value="">Seleccione una materia</option>
                    <?php echo $options_materias; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="horario">Horario:</label>
                <select class="form-control" id="horario" name="horario" required>
                    <option value="">Seleccione un horario</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#materia').change(function() {
                var materia_id = $(this).val();
                $.ajax({
                    url: 'registrar_materia.php',
                    type: 'POST',
                    data: { materia_id: materia_id },
                    dataType: 'json',
                    success: function(response) {
                        var options_horarios = '<option value="">Seleccione un horario</option>';
                        $.each(response, function(index, item) {
                            options_horarios += '<option value="' + item.ID + '">' + item.HORARIO_COMPLETO + '</option>';
                        });
                        $('#horario').html(options_horarios);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                    }
                });
            });
        });
    </script>
</body>
</html>
