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

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Lógica.
$stmt = $mysqli->prepare("SELECT u.dni AS dni, nombre, apellido, legajo, fecha_confirmacion_solicitud FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni WHERE dni_profesor = ? ORDER BY apellido, nombre");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_alumnos = $stmt->get_result();
$alumnos = [];
if ($result_alumnos->num_rows > 0) {
    while ($alumno = $result_alumnos->fetch_assoc()) {

        // Recuperar el ultimo informe de cada alumno para conocer su situación actual.
        $stmt = $mysqli->prepare("SELECT MAX(id_informe) AS id FROM informes WHERE dni_alumno = ?");
        $stmt->bind_param("s", $alumno['dni']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $informe = $result->fetch_assoc();
            $id_informe = $informe['id'];
            $stmt = $mysqli->prepare("SELECT id_informe, estado, final FROM informes WHERE dni_alumno = ? AND id_informe = ? AND original = 1");
            $stmt->bind_param("si", $alumno['dni'], $id_informe);
            $stmt->execute();
            $result_ultimo_informe = $stmt->get_result();
            while ($ultimo_informe = $result_ultimo_informe->fetch_assoc()) {
                if ($ultimo_informe['final'] == 1 && $ultimo_informe['estado'] == 'PENDIENTE') $informe_final = 'Pendiente';
                elseif ($ultimo_informe['final'] == 1 && $ultimo_informe['estado'] == 'RECHAZADO') $informe_final = 'Rechazado';
                elseif ($ultimo_informe['final'] == 1 && $ultimo_informe['estado'] == 'APROBADO') $informe_final = 'Aprobado';
                else $informe_final = $ultimo_informe['id_informe'];
            }
        } else {
            $informe_final = '';
        }

        // Agregar alumno a la lista.
        $alumnos[] = [
            'apellido' => $alumno['apellido'],
            'nombre' => $alumno['nombre'],
            'legajo' => $alumno['legajo'],
            'fecha_confirmacion_solicitud' => $alumno['fecha_confirmacion_solicitud'],
            'informe_final' => $informe_final
        ];
    }
}

// PDF.
ob_start();
?>

<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <table class="table text-center">
        <thead>
            <tr>
                <th scope="col">Nombre y Apellido</th>
                <th scope="col">Legajo</th>
                <th scope="col">Fecha Inicio</th>
                <th scope="col">Instancia Actual</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($alumnos)) {
                foreach ($alumnos as $alumno) {
            ?>
                    <tr>
                        <td><?php echo $alumno['apellido'] . ' ' . $alumno['nombre'] ?></td>
                        <td><?php echo $alumno['legajo'] ?></td>
                        <td><?php echo $alumno['fecha_confirmacion_solicitud'] ?></td>
                        <td>
                            <?php
                            if ($alumno['informe_final'] === 'Pendiente') echo 'Informe Final -> Pendiente de revisión';
                            elseif ($alumno['informe_final'] === 'Rechazado') echo 'Informe Final -> Rechazado (debe realizar la corrección)';
                            elseif ($alumno['informe_final'] === 'Aprobado') echo 'Informe Final -> Aprobado';
                            elseif (!empty($alumno['informe_final'])) echo 'Ha entregado hasta el Informe Nro. ' . $alumno['informe_final'];
                            else echo 'No ha enviado ningún informe';
                            ?>
                        </td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center">No tienes a ningun alumno a cargo actualmente</td>
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
$dompdf->stream("alumnos_ciclo_actual.pdf", array("Attachment" => true));
?>