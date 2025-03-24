<?php

// Inicializar sesión.
session_start();

// Validar si se recibe el rol por GET.
if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['rol'])) $_SESSION['rol'] = $_GET['rol'];

// Validar sesión y rol.
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['alumnos', 'profesores'])) {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Destruir variables de sesión.
unset($_SESSION['dni_su']);
unset($_SESSION['nombre_su']);
unset($_SESSION['apellido_su']);
unset($_SESSION['fecha_nacimiento_su']);
unset($_SESSION['email_su']);
unset($_SESSION['repetir_email_su']);

// Validar información de sesión.
if (!empty($_SESSION['dni'])) {
    header("Location: menu_principal.php");
    exit();
}

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

// Lógica.
$form_title = ($_SESSION['rol'] === 'alumnos') ? 'Alumno' : 'Profesor';
?>

<!doctype html>
<html lang="es">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <!-- Centrado vertical y horizontal -->
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="container br-class bg-white form-container min-vh-xs-100">
            <h1 class="text-center mt-4 mb-3">Iniciar Sesión</h1>
            <h2 class="text-center mb-4"><?php echo $form_title ?></h2>
            <form method="POST" action="login_validar.php" class="mx-auto" style="width: 250px;">
                <div class="mb-3">
                    <label for="input-dni" class="form-label">DNI</label>
                    <input type="text" class="form-control" name="dni" id="input-dni" required>
                </div>
                <div class="mb-4">
                    <label for="input-contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" id="input-contrasena" required>
                </div>
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary w-100" aria-label="Ingresar al menú principal">Ingresar</button>
                </div>
            </form>
            <div class="mb-4 d-flex justify-content-center">
                <p>¿Todavía no tienes una cuenta?</p>
                <a href="signup.php" class="ms-1" aria-label="Registrarme">Regístrate</a>
            </div>
        </div>
    </div>
    <?php
    if (!empty($mensaje)) {
    ?>
        <div class="modal fade" id="modal-message" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-message-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-message-title"><?php echo $titulo ?></h5>
                    </div>
                    <div class="modal-body"><?php echo $mensaje ?></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Cerrar mensaje">Cerrar</button>
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
</body>

</html>