<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['alumnos', 'profesores'])) {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar mensajes.
if (!empty($_SESSION['mensaje_error'])) {
    $mensaje = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
} else {
    $titulo = '';
    $mensaje = '';
}

// Lógica.
$form_title = ($_SESSION['rol'] === 'alumnos') ? 'Alumno' : 'Profesor';

// Respuestas guardadas.
$dni = isset($_SESSION['dni_su']) ? $_SESSION['dni_su'] : '';
$nombre = isset($_SESSION['nombre_su']) ? $_SESSION['nombre_su'] : '';
$apellido = isset($_SESSION['apellido_su']) ? $_SESSION['apellido_su'] : '';
$fecha_nacimiento = isset($_SESSION['fecha_nacimiento_su']) ? $_SESSION['fecha_nacimiento_su'] : '';
$email = isset($_SESSION['email_su']) ? $_SESSION['email_su'] : '';
$repetir_email = isset($_SESSION['repetir_email_su']) ? $_SESSION['repetir_email_su'] : '';
?>

<!doctype html>
<html lang="en">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <!-- Centrado vertical y horizontal -->
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="container br-class bg-white form-container min-vh-xs-100">
            <h3 class="text-center my-4">Registrar <?php echo $form_title ?></h3>
            <form method="POST" action="signup_action.php" class="mx-auto" style="width: 250px;">
                <div class="mb-3">
                    <label for="input-dni" class="form-label">DNI</label>
                    <input type="text" class="form-control" name="dni" id="input-dni" value="<?php echo $dni ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="input-nombre" value="<?php echo $nombre ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" name="apellido" id="input-apellido" value="<?php echo $apellido ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-fecha-nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" name="fecha-nacimiento" id="input-fecha-nacimiento" value="<?php echo $fecha_nacimiento ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" name="email" id="input-email" value="<?php echo $email ?>" required>
                </div>
                <div class="mb-4">
                    <label for="input-repetir-email" class="form-label">Confirmar Correo Electrónico</label>
                    <input type="email" class="form-control" name="repetir-email" id="input-repetir-email" value="<?php echo $repetir_email ?>" required>
                </div>
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary w-100">Finalizar</button>
                </div>
            </form>
            <div class="mb-4 d-flex justify-content-center">
                <p>¿Ya tienes una cuenta?</p>
                <a href="login.php?rol=<?php echo $_SESSION['rol'] ?>" class="ms-1">Ir al inicio</a>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <?php
    if (!empty($mensaje)) {
    ?>
        <div class="modal fade" id="modal-message" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-message-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ERROR</h5>
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
</body>

</html>