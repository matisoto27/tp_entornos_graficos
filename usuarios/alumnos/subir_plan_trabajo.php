<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'alumnos') {
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

// Validar acceso autorizado.
$stmt = $mysqli->prepare("SELECT * FROM alumnos WHERE dni = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();
if (!empty($alumno['fecha_plan_trabajo']) || $alumno['estado_solicitud'] !== 'Confirmada') {
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: ../menu_principal.php");
    exit();
}

// Redireccionar.
$href_pps = '';
$href_lista_profesores = '/usuarios/lista_profesores.php';
$href_notificaciones = '/usuarios/notificaciones.php';
$href_modificar_perfil = '/usuarios/modificar_perfil.php';
$href_cerrar_sesion = '/cerrar_sesion.php';
?>

<!doctype html>
<html lang="es">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body class="d-flex flex-column">
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/header.php';
    ?>
    <main class="d-flex justify-content-center align-items-center flex-fill border-top border-bottom">
        <div class="container br-class alt-background upload-container">
            <h1 class="text-center pt-4 mb-4">Subir Plan de Trabajo</h1>
            <form method="POST" action="subir_plan_trabajo_action.php" enctype="multipart/form-data" class="mx-auto">
                <div class="mb-4">
                    <input type="file" class="form-control" name="archivo" id="input-archivo" required>
                </div>
                <div class="pb-4 text-center">
                    <button type="submit" class="btn btn-primary" name="subir" id="submit-button" aria-label="Subir archivo del plan de trabajo" disabled>Subir archivo</button>
                </div>
            </form>
        </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/footer.php';
    ?>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        document.getElementById('input-archivo').addEventListener('change', function() {
            var submitButton = document.getElementById('submit-button');
            if (this.files.length > 0) {
                submitButton.removeAttribute('disabled');
            } else {
                submitButton.setAttribute('disabled', 'true');
            }
        });
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>