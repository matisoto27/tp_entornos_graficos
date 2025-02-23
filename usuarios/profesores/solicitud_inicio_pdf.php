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
}

// Validar si se reciben datos por GET.
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    // Validación de campos obligatorios.
    if (empty($_GET['dni'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: /../menu_principal.php");
        exit();
    }

    // Recuperar datos enviados a través de los parámetros.
    $dni = $_GET['dni'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Lógica.
    $stmt = $mysqli->prepare("SELECT * FROM alumnos a INNER JOIN usuarios u ON a.dni = u.dni WHERE u.dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumno = $result->fetch_assoc();
}

// PDF.
ob_start();
?>

<!doctype html>
<html lang="en">

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #34495e;
        }

        td {
            background-color: #ecf0f1;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1c40f;
        }
    </style>
</head>

<body>
    <h1 class="text-center"><?php echo $alumno['nombre'] . ' ' . $alumno['apellido'] . ' | ' . $alumno['legajo'] ?></h1>
    <table class="table">
        <tbody>
            <tr>
                <td>Carrera</th>
                <td><?php echo $alumno['carrera']; ?></td>
            </tr>
            <tr>
                <td>Nombre empresa</th>
                <td><?php echo $alumno['nombre_empresa']; ?></td>
            </tr>
            <tr>
                <td>Dirección empresa</th>
                <td><?php echo $alumno['direccion_empresa']; ?></td>
            </tr>
            <tr>
                <td>Teléfono empresa</th>
                <td><?php echo $alumno['telefono_empresa']; ?></td>
            </tr>
            <tr>
                <td>Modalidad trabajo</th>
                <td><?php echo $alumno['modalidad_trabajo']; ?></td>
            </tr>
            <tr>
                <td>Nombre jefe</th>
                <td><?php echo $alumno['nombre_jefe']; ?></td>
            </tr>
            <tr>
                <td>Apellido jefe</th>
                <td><?php echo $alumno['apellido_jefe']; ?></td>
            </tr>
            <tr>
                <td>Email jefe</th>
                <td><?php echo $alumno['email_jefe']; ?></td>
            </tr>
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
$dompdf->stream('solicitud_inicio_' . strtolower($alumno['apellido']) . '_' . strtolower($alumno['nombre']) . '.pdf', array("Attachment" => true));
?>