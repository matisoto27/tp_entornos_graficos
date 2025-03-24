<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'responsables') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['codigo']) || empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: login.php");
    exit();
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['dni']) || empty($_POST['nombre']) || empty($_POST['apellido'])) {
        $_SESSION['mensaje_error'] = "Por favor, seleccione un alumno de la lista.";
        header("Location: menu_principal.php");
        exit();
    }

    // Recuperar datos del formulario.
    $dni_alumno = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Recuperar nombre y apellido del profesor.
$stmt = $mysqli->prepare("SELECT nombre, apellido FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni_profesor WHERE a.dni = ?");
$stmt->bind_param("s", $dni_alumno);
$stmt->execute();
$result = $stmt->get_result();
$profesor = $result->fetch_assoc();
$nombre_profesor = $profesor['nombre'];
$apellido_profesor = $profesor['apellido'];

// Recuperar solicitud de inicio, plan de trabajo (tanto el archivo como la fecha en que se subió) y fecha_pps_aprobadas del alumno.
$stmt = $mysqli->prepare("SELECT fecha_confirmacion_solicitud, archivo_plan_trabajo, fecha_plan_trabajo, fecha_pps_aprobadas FROM alumnos WHERE dni = ?");
$stmt->bind_param("s", $dni_alumno);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();

// Recuperar todos los informes del alumno, luego distinguirlos en dos grupos (fecha_subida y fecha_calificacion).
$stmt = $mysqli->prepare("SELECT id_informe, original, nombre_archivo, fecha_subida, estado, final, fecha_calificacion, correcciones FROM informes WHERE dni_alumno = ?");
$stmt->bind_param("s", $dni_alumno);
$stmt->execute();
$result = $stmt->get_result();
$informes = [];
if ($result->num_rows > 0) {
    while ($informe = $result->fetch_assoc()) {
        $informes[] = [
            'id_informe' => $informe['id_informe'],
            'original' => $informe['original'],
            'nombre_archivo' => $informe['nombre_archivo'],
            'estado' => $informe['estado'],
            'correcciones' => $informe['correcciones'],
            'fecha' => $informe['fecha_subida'],
            'alumno' => 1
        ];
        $informes[] = [
            'id_informe' => $informe['id_informe'],
            'original' => $informe['original'],
            'nombre_archivo' => $informe['nombre_archivo'],
            'estado' => $informe['estado'],
            'correcciones' => $informe['correcciones'],
            'fecha' => $informe['fecha_calificacion'],
            'alumno' => 0
        ];
    }
    usort($informes, function ($a, $b) {
        $dateA = DateTime::createFromFormat('Y-m-d H:i:s', $a['fecha']);
        $dateB = DateTime::createFromFormat('Y-m-d H:i:s', $b['fecha']);
        return $dateA <=> $dateB;
    });
}

// Recuperar el último informe del alumno.
$stmt = $mysqli->prepare("SELECT MAX(id_informe) AS id_informe FROM informes WHERE dni_alumno = ?");
$stmt->bind_param("s", $dni_alumno);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $informe = $result->fetch_assoc();
    $id_informe = $informe['id_informe'];
    $stmt = $mysqli->prepare("SELECT * FROM informes WHERE dni_alumno = ? AND id_informe = ?");
    $stmt->bind_param("si", $dni_alumno, $id_informe);
    $stmt->execute();
    $result2 = $stmt->get_result();
    $ultimo_informe = $result2->fetch_assoc();
}
?>

<!doctype html>
<html lang="en">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <div class="mt-sm-5 container br-class bg-white min-vh-xs-100 p-4" style="max-width: 700px;">
        <div class="d-flex flex-row justify-content-center mb-4">
            <div class="d-flex flex-column">
                <h2>Trazabilidad de PPS</h2>
            </div>
        </div>
        <div class="d-flex flex-row justify-content-between mb-4">
            <div class="d-flex flex-column align-items-start">
                <h3>Alumno</h3>
                <h3><?php echo $nombre . ' ' . $apellido ?></h3>
            </div>
            <div class="d-flex flex-column align-items-end">
                <h3>Profesor</h3>
                <h3><?php echo $nombre_profesor . ' ' . $apellido_profesor ?></h3>
            </div>
        </div>
        <?php
        if (!empty($alumno['fecha_confirmacion_solicitud'])) {
        ?>
            <div class="d-flex flex-row mb-4">
                <div class="d-flex flex-column text-center" style="background-color: lightblue; border-radius: 10px;">
                    <div class="p-2">Solicitud de Inicio</div>
                    <div class="p-2">
                        Nombre del archivo:
                        <a href="../solicitud_inicio_pdf.php?dni=<?php echo $dni_alumno ?>" target="_blank">
                            <?php echo 'solicitud_inicio_' . strtolower($apellido) . '_' . strtolower($nombre) . '.pdf' ?>
                        </a>
                    </div>
                    <div class="p-2">Fecha de confirmación: <?php echo $alumno['fecha_confirmacion_solicitud'] ?></div>
                </div>
            </div>
        <?php
        }
        if (!empty($alumno['fecha_plan_trabajo'])) {
        ?>
            <div class="d-flex flex-row mb-4">
                <div class="d-flex flex-column text-center" style="background-color: lightblue; border-radius: 10px;">
                    <div class="p-2">Plan de Trabajo</div>
                    <div class="p-2">
                        Nombre del archivo:
                        <a href="../descargar_archivo.php?nombre-archivo=<?php echo $alumno['archivo_plan_trabajo'] ?>&es-informe=0">
                            <?php echo $alumno['archivo_plan_trabajo'] ?>
                        </a>
                    </div>
                    <div class="p-2">Fecha de publicacion: <?php echo $alumno['fecha_plan_trabajo'] ?></div>
                </div>
            </div>
            <?php
        }
        foreach ($informes as $i) {
            if ($i['alumno'] === 1) {
            ?>
                <div class="d-flex flex-row mb-4">
                    <div class="d-flex flex-column text-center" style="background-color: lightblue; border-radius: 10px;">
                        <div class="p-2">
                            Informe N<?php echo $i['id_informe'];
                                        if ($i['original'] === 0) echo ' Corregido'; ?></div>
                        <div class="p-2">
                            Nombre del archivo:
                            <a href="../descargar_archivo.php?nombre-archivo=<?php echo $i['nombre_archivo'] ?>&es-informe=1">
                                <?php echo $i['nombre_archivo'] ?>
                            </a>
                        </div>
                        <div class="p-2">Fecha de publicación: <?php echo $i['fecha'] ?></div>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php
            if ($i['alumno'] === 0 && !empty($i['fecha'])) {
            ?>
                <div class="d-flex flex-row-reverse mb-4">
                    <div class="d-flex flex-column text-center align-items-center" style="background-color: pink; border-radius: 10px;">
                        <div class="p-2">Calificación del Informe N<?php echo $i['id_informe'];
                                                                    if ($i['original'] === 0) echo ' Corregido'; ?></div>
                        <div class="p-2"><?php echo $i['estado'] ?></div>
                        <div class="p-2">Fecha de calificación: <?php echo $i['fecha'] ?></div>
                        <div class="p-2">
                            <?php if ($i['estado'] === "RECHAZADO") { ?>
                                <button type="button" class="btn btn-light btn-sm" style="border: 1px solid black;" data-bs-toggle="modal" data-bs-target="#modal-correcciones<?php echo $i['id_informe'] ?>">VER CORRECCIONES</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php
            if (!empty($i['correcciones'])) {
            ?>
                <!-- Modal -->
                <div class="modal fade" id="modal-correcciones<?php echo $i['id_informe'] ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-correcciones-label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-md" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Correcciones del Informe N<?php echo $i['id_informe'] ?></h5>
                            </div>
                            <div class="modal-body">
                                <p><?php echo $i['correcciones'] ?></p>
                            </div>
                            <div class="modal-footer">
                                <div class="container">
                                    <div class="row">
                                        <div class="col d-flex justify-content-center">
                                            <button type="button" class="btn btn-secondary m-2" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fin Modal -->
            <?php
            }
            ?>
        <?php
        }
        ?>
        <div class="d-flex flex-row justify-content-center mb-4">
            <div class="d-flex flex-column">
                <p class="mb-0">FIN DE TRAZABILIDAD</p>
            </div>
        </div>
        <?php
        if ($ultimo_informe['final'] == 1 && !empty($ultimo_informe['estado']) && $ultimo_informe['estado'] === 'APROBADO') {
        ?>
            <div class="d-flex flex-row justify-content-center pt-4 my-4" style="border-top: 1px solid black;">
                <div class="d-flex flex-column">
                    <form method="POST" action="action_aprobar_alumno.php">
                        <input type="hidden" name="dni-alumno" value="<?php echo $dni_alumno ?>">
                        <?php
                        if (empty($alumno['fecha_pps_aprobadas'])) {
                        ?>
                            <button type="submit" class="btn btn-success px-5 py-2">Registrar Aprobación</button>
                        <?php
                        } else {
                        ?>
                            <div class="px-5 py-2" style="color: #fff; background-color: #28a745; border-color: #28a745;">PPS APROBADAS</div>
                        <?php
                        }
                        ?>
                    </form>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="d-flex flex-row justify-content-center">
            <div class="d-flex flex-column">
                <button type="button" class="btn btn-primary px-5 py-2" onclick='window.location.href="trazabilidad_pps.php"'>Volver</button>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>