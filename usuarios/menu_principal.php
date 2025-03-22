<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['alumnos', 'profesores'])) {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['dni'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: login.php");
    exit();
} else {
    $dni = $_SESSION['dni'];
}

// Eliminar respuestas guardadas.
include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/reset_respuestas.php';

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Redireccionar.
if ($_SESSION['rol'] === "alumnos") {
    $stmt = $mysqli->prepare("SELECT estado_solicitud, fecha_plan_trabajo FROM alumnos WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumno = $result->fetch_assoc();
    if (!empty($alumno['fecha_plan_trabajo'])) $href_pps = "alumnos/informes.php";
    elseif ($alumno['estado_solicitud'] === 'Confirmada') $href_pps = "alumnos/subir_plan_trabajo.php";
    elseif ($alumno['estado_solicitud'] === 'Pendiente') $href_pps = "alumnos/solicitud_pendiente.php";
    else $href_pps = "alumnos/solicitud_inicio_p1.php";
} elseif ($_SESSION['rol'] === "profesores") {
    $href_pps = "profesores/informes.php";
}
$href_lista_profesores = '/usuarios/lista_profesores.php';
$href_notificaciones = '/usuarios/notificaciones.php';
$href_modificar_perfil = '/usuarios/modificar_perfil.php';
$href_cerrar_sesion = '/cerrar_sesion.php';

// Validar mensajes.
if (!empty($_SESSION['mensaje_error'])) {
    $titulo = 'ERROR';
    $mensaje = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
} elseif (!empty($_SESSION['mensaje_exito'])) {
    $titulo = 'TERMINADO';
    $mensaje = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
} else {
    $titulo = '';
    $mensaje = '';
}
?>

<!doctype html>
<html lang="en">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body class="d-flex flex-column">
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/header.php';
    ?>
    <main class="d-flex justify-content-center align-items-center flex-fill border-top border-bottom">
        <div class="container br-class alt-background d-flex align-items-center p-5">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div style="height: 350px; width: auto; border-bottom: 1px solid black;">
                            <img class="object-fit-cover" style="height: 100%; width: 100%;" src="img1.jpg" alt="Solicitud de inicio completada">
                        </div>
                        <div class="card-body">
                            <p class="card-text">1. Completa la solicitud de inicio para indicar los datos esenciales y empezar tus PPS.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div style="height: 350px; width: auto; border-bottom: 1px solid black;">
                            <img class="object-fit-cover" style="height: 100%; width: 100%;" src="img2.jpg" alt="Carga de archivo en el sistema">
                        </div>
                        <div class="card-body">
                            <p class="card-text">2. Sube un archivo pdf sobre tu plan de trabajo. Incluye mas informacion de la organizacion y un esquema general de las actividades a realizar durante tus PPS.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div style="height: 350px; width: auto; border-bottom: 1px solid black;">
                            <img class="object-fit-cover" style="height: 100%; width: 100%;" src="img3.jpg" alt="Profesor ayudando a una alumna">
                        </div>
                        <div class="card-body">
                            <p class="card-text">3. Sigue semana a semana los avances de tus PPS, reflexionando sobre los aprendizajes adquiridos en cada uno de ellos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/footer.php';
    ?>
    <!-- Modal -->
    <?php
    if (!empty($mensaje)) {
    ?>
        <div class="modal fade" id="modal-message" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-message-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $titulo ?></h5>
                    </div>
                    <div class="modal-body"><?php echo $mensaje ?></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function() {
                const modal = new bootstrap.Modal(document.getElementById('modal-message'));
                modal.show();
            };
        </script>
    <?php
    }
    ?>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>