<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Examen</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .question-form {
            display: none;
        }
        .show-form {
            display: block;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function showQuestionForm() {
            const type = document.getElementById('question-type').value;
            document.querySelectorAll('.question-form').forEach(form => form.classList.remove('show-form'));
            document.getElementById(type + '-form').classList.add('show-form');
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1>Crear Examen</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="id_examen">ID del Examen:</label>
                <input type="text" class="form-control" id="id_examen" name="id_examen">
            </div>
            <div class="form-group">
                <label for="materia">Materia:</label>
                <input type="text" class="form-control" id="materia" name="materia" required>
            </div>
            <div class="form-group">
                <label for="profesor">Profesor:</label>
                <input type="text" class="form-control" id="profesor" name="profesor" required>
            </div>
            <div class="form-group">
                <label for="tema">Tema:</label>
                <input type="text" class="form-control" id="tema" name="tema">
            </div>
            <div class="form-group">
                <label for="nombre_examen">Nombre del Examen:</label>
                <input type="text" class="form-control" id="nombre_examen" name="nombre_examen">
            </div>
            <div class="form-group">
                <label for="numero_preguntas">Número de Preguntas:</label>
                <input type="number" class="form-control" id="numero_preguntas" name="numero_preguntas" min="1">
            </div>
            <div class="form-group">
                <label for="fecha">Fecha del Examen:</label>
                <input type="date" class="form-control" id="fecha" name="fecha">
            </div>


            <h2>Agregar Preguntas</h2>
            <?php
            // Conexión a la base de datos
            $db_user = 'ALEJO';
            $db_password = 'root';
            $db_host = '//localhost/XE';

            $conn = oci_pconnect($db_user, $db_password, $db_host);
            if (!$conn) {
                $e = oci_error();
                trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
            }

            // Consultas para obtener preguntas por tipo
            $query_multiple = "SELECT id, pregunta, opciones FROM preguntas WHERE tipo = 'multiple'";
            $query_verdadero_falso = "SELECT id, pregunta FROM preguntas WHERE tipo = 'verdadero_falso'";
            $query_abiertas = "SELECT id, pregunta FROM preguntas WHERE tipo = 'abiertas'";

            $stid_multiple = oci_parse($conn, $query_multiple);
            oci_execute($stid_multiple);

            $stid_verdadero_falso = oci_parse($conn, $query_verdadero_falso);
            oci_execute($stid_verdadero_falso);

            $stid_abiertas = oci_parse($conn, $query_abiertas);
            oci_execute($stid_abiertas);
            ?>

            <div class="form-group">
                <label for="preguntas_multiple">Preguntas Múltiples:</label>
                <select multiple class="form-control" id="preguntas_multiple" name="preguntas_multiple[]">
                    <?php
                    while ($row = oci_fetch_array($stid_multiple, OCI_ASSOC+OCI_RETURN_NULLS)) {
                        $id = $row['ID'];
                        $pregunta = $row['PREGUNTA'];
                        $opciones = json_decode($row['OPCIONES'], true); // Decodificar el JSON
                        echo "<option value='$id'>$pregunta - Opciones: " . implode(", ", $opciones) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="preguntas_verdadero_falso">Preguntas de Verdadero o Falso:</label>
                <select multiple class="form-control" id="preguntas_verdadero_falso" name="preguntas_verdadero_falso[]">
                    <?php
                    while ($row = oci_fetch_array($stid_verdadero_falso, OCI_ASSOC+OCI_RETURN_NULLS)) {
                        $id = $row['ID'];
                        $pregunta = $row['PREGUNTA'];
                        echo "<option value='$id'>$pregunta</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="preguntas_abiertas">Preguntas Abiertas:</label>
                <select multiple class="form-control" id="preguntas_abiertas" name="preguntas_abiertas[]">
                    <?php
                    while ($row = oci_fetch_array($stid_abiertas, OCI_ASSOC+OCI_RETURN_NULLS)) {
                        $id = $row['ID'];
                        $pregunta = $row['PREGUNTA'];
                        echo "<option value='$id'>$pregunta</option>";
                    }
                    ?>
                </select>
            </div>

            <?php
            oci_free_statement($stid_multiple);
            oci_free_statement($stid_verdadero_falso);
            oci_free_statement($stid_abiertas);
            oci_close($conn);
            ?>

            <button type="submit" class="btn btn-primary mt-3">Crear Examen</button>
        </form>
    </div>

    <?php
    // Procesar el formulario cuando se envía
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Conexión a la base de datos
        $db_user = 'ALEJO';
        $db_password = 'root';
        $db_host = '//localhost/XE';

        $conn = oci_pconnect($db_user, $db_password, $db_host);
        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }

        // Validar que se haya seleccionado al menos una pregunta
        if (
            (!isset($_POST['preguntas_multiple']) || empty($_POST['preguntas_multiple'])) &&
            (!isset($_POST['preguntas_verdadero_falso']) || empty($_POST['preguntas_verdadero_falso'])) &&
            (!isset($_POST['preguntas_abiertas']) || empty($_POST['preguntas_abiertas']))
        ) {
            echo "<div class='container mt-5'>";
            echo "<h1>Error al crear examen</h1>";
            echo "<p>Debe seleccionar al menos una pregunta para el examen.</p>";
            echo "</div>";
            exit;
        }

        $materia = $_POST['materia'];
        $profesor = $_POST['profesor'];
        $tema = $_POST['tema'];
        $nombre_examen = $_POST['nombre_examen'];
        $fecha = $_POST['fecha'];
        $numero_preguntas = $_POST['numero_preguntas'];
        $preguntas_multiple = isset($_POST['preguntas_multiple']) ? $_POST['preguntas_multiple'] : [];
        $preguntas_verdadero_falso = isset($_POST['preguntas_verdadero_falso']) ? $_POST['preguntas_verdadero_falso'] : [];
        $preguntas_abiertas = isset($_POST['preguntas_abiertas']) ? $_POST['preguntas_abiertas'] : [];
        $id_examen = $_POST['id_examen'];

        // Insertar examen
        $query_insert_examen = "INSERT INTO examen (id, materia, profesor, tema, nombre_examen, fecha, numero_preguntas) 
                                VALUES (:id_examen, :materia, :profesor, :tema, :nombre_examen, TO_DATE(:fecha, 'YYYY-MM-DD'), :numero_preguntas)";
        $stid_insert_examen = oci_parse($conn, $query_insert_examen);

        oci_bind_by_name($stid_insert_examen, ':id_examen', $id_examen);
        oci_bind_by_name($stid_insert_examen, ':materia', $materia);
        oci_bind_by_name($stid_insert_examen, ':profesor', $profesor);
        oci_bind_by_name($stid_insert_examen, ':tema', $tema);
        oci_bind_by_name($stid_insert_examen, ':nombre_examen', $nombre_examen);
        oci_bind_by_name($stid_insert_examen, ':fecha', $fecha);
        oci_bind_by_name($stid_insert_examen, ':numero_preguntas', $numero_preguntas);

        if (!oci_execute($stid_insert_examen)) {
            $e = oci_error($stid_insert_examen);
            echo "<div class='container mt-5'>";
            echo "<h1>Error al crear examen</h1>";
            echo "<p>" . htmlentities($e['message'], ENT_QUOTES) . "</p>";
            echo "</div>";
            oci_free_statement($stid_insert_examen);
            oci_close($conn);
            exit;
        }

        // Obtener el ID del examen recién insertado
        $examen_id = $id_examen;

        // Insertar preguntas seleccionadas en la tabla examen_pregunta
        $query_insert_examen_pregunta = "INSERT INTO examen_pregunta (examen_id, pregunta_id) VALUES (:examen_id, :pregunta_id)";
        $stid_insert_examen_pregunta = oci_parse($conn, $query_insert_examen_pregunta);

        foreach (array_merge($preguntas_multiple, $preguntas_verdadero_falso, $preguntas_abiertas) as $pregunta_id) {
            oci_bind_by_name($stid_insert_examen_pregunta, ':examen_id', $examen_id);
            oci_bind_by_name($stid_insert_examen_pregunta, ':pregunta_id', $pregunta_id);

            if (!oci_execute($stid_insert_examen_pregunta)) {
                $e = oci_error($stid_insert_examen_pregunta);
                echo "<div class='container mt-5'>";
                echo "<h1>Error al asociar preguntas con examen</h1>";
                echo "<p>" . htmlentities($e['message'], ENT_QUOTES) . "</p>";
                echo "</div>";
                oci_free_statement($stid_insert_examen_pregunta);
                oci_close($conn);
                exit;
            }
        }

        echo "<div class='container mt-5'>";
        echo "<h1>Examen creado exitosamente</h1>";
        echo "<p>El examen ha sido creado correctamente.</p>";
        echo "</div>";

        oci_free_statement($stid_insert_examen_pregunta);
        oci_close($conn);
    }
    ?>
</body>
</html>
