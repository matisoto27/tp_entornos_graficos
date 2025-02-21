<?php

// Inicializar sesión.
session_start();

// Establecer rol de la sesión.
$_SESSION['rol'] = 'responsables';

// Validar mensajes.
if (!empty($_SESSION['mensaje_error'])) {
    $titulo = 'ERROR';
    $mensaje = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
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

<body>
    <!-- Centrado vertical y horizontal -->
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="container background-border form-container min-vh-xs-100">
            <h3 class="text-center mt-4 mb-2">Responsable PPS</h3>
            <h3 class="text-center mb-4">Iniciar Sesión</h3>
            <form method="POST" action="login_validar.php" class="mx-auto" style="width: 250px;">
                <div class="mb-3">
                    <label for="input-dni" class="form-label">Codigo</label>
                    <input type="text" class="form-control" name="codigo" id="input-codigo" required>
                </div>
                <div class="mb-4">
                    <label for="input-contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" id="input-contrasena" required>
                </div>
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </div>
            </form>
        </div>
    </div>
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
</body>

</html>