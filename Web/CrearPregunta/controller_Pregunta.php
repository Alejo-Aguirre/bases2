<?php
$db_user = 'ALEJO';
$db_password = 'root';
$db_host = '//localhost/XE';

$conn = oci_pconnect($db_user, $db_password, $db_host);
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
} else {
    echo "Conexión establecida correctamente.<br>";
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['question_type'])) {
        echo "Tipo de pregunta no especificado.";
        exit;
    }

    $question_type = $_POST['question_type'];
    $id = $_POST['id']; // El ID se ingresa manualmente

    switch ($question_type) {
        case 'multiple-choice':
            $mc_question = $_POST['mc_question'];
            $mc_option1 = $_POST['mc_option1'];
            $mc_option2 = $_POST['mc_option2'];
            $mc_option3 = $_POST['mc_option3'];
            $mc_option4 = $_POST['mc_option4'];
            $mc_correct = $_POST['mc_correct'];

            // Llamar al procedimiento almacenado
            $query = "BEGIN crear_pregunta_proc(:id, 'multiple-choice', :pregunta, :opciones, :correcta); END;";
            $stid = oci_parse($conn, $query);

            oci_bind_by_name($stid, ':id', $id);
            oci_bind_by_name($stid, ':pregunta', $mc_question);
            $opciones = json_encode([$mc_option1, $mc_option2, $mc_option3, $mc_option4]);
            oci_bind_by_name($stid, ':opciones', $opciones);
            oci_bind_by_name($stid, ':correcta', $mc_correct);

            break;

        case 'true-false':
            $tf_question = $_POST['tf_question'];
            $tf_correct = $_POST['tf_correct'];

            // Llamar al procedimiento almacenado
            $query = "BEGIN crear_pregunta_proc(:id, 'true-false', :pregunta, NULL, :correcta); END;";
            $stid = oci_parse($conn, $query);

            oci_bind_by_name($stid, ':id', $id);
            oci_bind_by_name($stid, ':pregunta', $tf_question);
            oci_bind_by_name($stid, ':correcta', $tf_correct);

            break;

        case 'short-answer':
            $sa_question = $_POST['sa_question'];
            $sa_answer = $_POST['sa_answer'];

            // Llamar al procedimiento almacenado
            $query = "BEGIN crear_pregunta_proc(:id, 'short-answer', :pregunta, NULL, :correcta); END;";
            $stid = oci_parse($conn, $query);

            oci_bind_by_name($stid, ':id', $id);
            oci_bind_by_name($stid, ':pregunta', $sa_question);
            oci_bind_by_name($stid, ':correcta', $sa_answer);

            break;

        default:
            echo "Tipo de pregunta no válido.";
            exit;
    }

    if (oci_execute($stid)) {
        echo "Pregunta creada exitosamente.";
    } else {
        $e = oci_error($stid);
        echo "Error al crear la pregunta: " . $e['message'];
    }

    oci_free_statement($stid);
    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Preguntas para Examen</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .question-form {
            display: none;
        }
        .show-form {
            display: block;
        }
    </style>
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
        <h1>Crear Preguntas para Examen</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="id">ID de Pregunta:</label>
                <input type="number" class="form-control" id="id" name="id" required>
            </div>

            <div class="form-group">
                <label for="question-type">Tipo de Pregunta:</label>
                <select class="form-control" id="question-type" name="question_type" onchange="showQuestionForm()" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="multiple-choice">Opción Múltiple</option>
                    <option value="true-false">Verdadero/Falso</option>
                    <option value="short-answer">Respuesta Corta</option>
                </select>
            </div>

            <div id="multiple-choice-form" class="question-form">
                <div class="form-group">
                    <label for="mc-question">Pregunta:</label>
                    <input type="text" class="form-control" id="mc-question" name="mc_question">
                </div>
                <div class="form-group">
                    <label for="mc-option1">Opción 1:</label>
                    <input type="text" class="form-control" id="mc-option1" name="mc_option1">
                </div>
                <div class="form-group">
                    <label for="mc-option2">Opción 2:</label>
                    <input type="text" class="form-control" id="mc-option2" name="mc_option2">
                </div>
                <div class="form-group">
                    <label for="mc-option3">Opción 3:</label>
                    <input type="text" class="form-control" id="mc-option3" name="mc_option3">
                </div>
                <div class="form-group">
                    <label for="mc-option4">Opción 4:</label>
                    <input type="text" class="form-control" id="mc-option4" name="mc_option4">
                </div>
                <div class="form-group">
                    <label for="mc-correct">Opción Correcta:</label>
                    <select class="form-control" id="mc-correct" name="mc_correct">
                        <option value="1">Opción 1</option>
                        <option value="2">Opción 2</option>
                        <option value="3">Opción 3</option>
                        <option value="4">Opción 4</option>
                    </select>
                </div>
            </div>

            <div id="true-false-form" class="question-form">
                <div class="form-group">
                    <label for="tf-question">Pregunta:</label>
                    <input type="text" class="form-control" id="tf-question" name="tf_question">
                </div>
                <div class="form-group">
                    <label for="tf-correct">Respuesta Correcta:</label>
                    <select class="form-control" id="tf-correct" name="tf_correct">
                        <option value="true">Verdadero</option>
                        <option value="false">Falso</option>
                    </select>
                </div>
            </div>

            <div id="short-answer-form" class="question-form">
                <div class="form-group">
                    <label for="sa-question">Pregunta:</label>
                    <input type="text" class="form-control" id="sa-question" name="sa_question">
                </div>
                <div class="form-group">
                    <label for="sa-answer">Respuesta Correcta:</label>
                    <input type="text" class="form-control" id="sa-answer" name
                    ="sa_answer">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Crear Pregunta</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
