<?php

// Inicializar sesión.
session_start();

// Validar mensajes.
if (!empty($_SESSION['mensaje_error'])) {
    $mensaje_error = $_SESSION['mensaje_error'];
}

// Destruir sesión.
session_destroy();
?>

<!doctype html>
<html lang="es">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <!-- Centrado vertical y horizontal -->
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="container list-container">
            <div class="list-group text-center">
                <a href="usuarios/login.php?rol=alumnos" class="list-group-item list-group-item-action mb-3" aria-label="Soy un alumno">Mi rol es: ALUMNO</a>
                <a href="usuarios/login.php?rol=profesores" class="list-group-item list-group-item-action mb-3" aria-label="Soy un profesor">Mi rol es: PROFESOR</a>
                <a href="responsables/login.php" class="list-group-item list-group-item-action" aria-label="Soy un administrador del sitio web">Mi rol es: RESPONSABLE PPS</a>
            </div>
        </div>
    </div>
    <?php
    if (!empty($mensaje_error)) {
    ?>
        <div class="modal fade" id="modal-error" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-error-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-error-title">ERROR</h5>
                    </div>
                    <div class="modal-body">
                        <?php echo $mensaje_error ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Cerrar">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function() {
                const modal = new bootstrap.Modal(document.getElementById('modal-error'));
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