<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'profesores') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['dni'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: ../login.php");
    exit();
} else {
    $dni = $_SESSION['dni'];
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no estén vacíos.
    if (empty($_POST['id-ciclo-lectivo']) || empty($_POST['anio']) || empty($_POST['ciclo'])) {
        $_SESSION['mensaje_error'] = "Por favor, seleccione un ciclo lectivo de la lista.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Recuperar datos a través del método POST.
    $id_ciclo_lectivo = $_POST['id-ciclo-lectivo'];
    $anio = $_POST['anio'];
    $ciclo = $_POST['ciclo'];
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: ../menu_principal.php");
    exit();
}

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Lógica.
$stmt = $mysqli->prepare("SELECT * FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni WHERE dni_profesor = ? AND id_ciclo_lectivo = ?");
$stmt->bind_param("si", $dni, $id_ciclo_lectivo);
$stmt->execute();
$result = $stmt->get_result();

// PDF.
ob_start();
?>

<!doctype html>
<html lang="es">

<head>
    <title>Informe Alumnos por Ciclo Lectivo</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <h1 class="text-center">Alumnos tutorizados | Ciclo Lectivo <?php echo ' ' . $anio . ' - ' . $ciclo ?></h1>
    <table class="table text-center">
        <thead>
            <tr>
                <th scope="col">Nombre y Apellido</th>
                <th scope="col">Legajo</th>
                <th scope="col">Fecha Inicio</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($alumno = $result->fetch_assoc()) {
            ?>
                    <tr>
                        <td><?php echo $alumno['nombre'] . ' ' . $alumno['apellido'] ?></td>
                        <td><?php echo $alumno['legajo'] ?></td>
                        <td><?php echo $alumno['fecha_confirmacion_solicitud'] ?></td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="3">No se encontraron alumnos para el ciclo lectivo seleccionado.</td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>

<?php
$html = ob_get_clean();
require_once '../../librerias/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->set(array('isRemoteEnabled' => true));
$dompdf->setOptions($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter');
$dompdf->render();
$dompdf->stream("alumnos_ciclo_lectivo.pdf", array("Attachment" => true));
?>