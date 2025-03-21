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
?>

<!doctype html>
<html lang="en">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/header.php';
    ?>
    <main>
        <div class="mt-5 container background-border" style="max-width: 800px;">
            <div class="row pt-4 mb-4">
                <div class="col text-center">
                    <h1>Mapa del Sitio Web</h1>
                </div>
            </div>

            <!-- Enlace a la página principal -->
            <div class="row mb-4">
                <div class="col text-center">
                    <a href="/usuarios/menu_principal.php" class="btn btn-outline-primary w-100">Página Principal</a>
                </div>
            </div>

            <!-- Enlaces a los 4 botones -->
            <div class="row pt-4 <?php echo ($_SESSION['rol'] === 'profesores') ? 'mb-4' : 'pb-4'; ?>">
                <div class="col-12 col-md-3 mb-3">
                    <a href="<?php echo $href_pps ?>" class="btn btn-outline-primary w-100">Trámites PPS</a>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <a href="<?php echo $href_lista_profesores ?>" class="btn btn-outline-primary w-100">Lista de Profesores</a>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <a href="<?php echo $href_notificaciones ?>" class="btn btn-outline-primary w-100">Notificaciones</a>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <a href="<?php echo $href_modificar_perfil ?>" class="btn btn-outline-primary w-100">Mi Perfil</a>
                </div>
            </div>

            <?php
            if ($_SESSION['rol'] === 'profesores') {
            ?>
                <div class="row pb-4">
                    <div class="col-12 col-md-3 text-center">
                        <a href="/usuarios/profesores/informe_alumnos_ciclo_lectivo.php" class="btn btn-outline-primary w-100">Buscar alumnos tutorizados en un ciclo lectivo determinado</a>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </main>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>