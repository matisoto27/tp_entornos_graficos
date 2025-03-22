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
} else {
    $codigo = $_SESSION['codigo'];
}

// Validar mensajes.
if (!empty($_SESSION['mensaje_error'])) {
    $titulo = 'ERROR';
    $mensaje = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
} else {
    $titulo = '';
    $mensaje = '';
}

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Lógica.
$stmt = $mysqli->prepare("SELECT * FROM responsables WHERE codigo = ?");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();
$responsable = $result->fetch_assoc();
$nombre_original = $responsable['nombre'];
$apellido_original = $responsable['apellido'];
$email_original = $responsable['email'];
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
            <h3 class="text-center my-4">Modificar Perfil</h3>
            <form method="POST" action="modificar_perfil_action.php" class="mx-auto" style="width: 280px;">
                <div class="mb-3">
                    <label for="input-nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="input-nombre" value="<?php echo $responsable['nombre'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" name="apellido" id="input-apellido" value="<?php echo $responsable['apellido'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-email" class="form-label">Correo Electronico</label>
                    <input type="email" class="form-control" name="email" id="input-email" value="<?php echo $responsable['email'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" id="input-contrasena" placeholder="Aquí puedes cambiar tu contraseña">
                </div>
                <div class="mb-4">
                    <label for="input-repetir-contrasena" class="form-label">Repetir Nueva Contraseña</label>
                    <input type="password" class="form-control" name="repetir-contrasena" id="input-repetir-contrasena" disabled>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success w-100" id="button-guardar" disabled>Guardar Cambios</button>
                </div>
                <div class="mb-4">
                    <button type="button" class="btn btn-primary w-100" onclick='window.location.href="menu_principal.php"'>Volver</button>
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputNombre = document.getElementById('input-nombre');
            const inputApellido = document.getElementById('input-apellido');
            const inputEmail = document.getElementById('input-email');
            const inputContrasena = document.getElementById('input-contrasena');
            const inputRepetirContrasena = document.getElementById('input-repetir-contrasena');
            const buttonGuardar = document.getElementById('button-guardar');
            const nombreOriginal = '<?php echo $nombre_original ?>';
            const apellidoOriginal = '<?php echo $apellido_original ?>';
            const emailOriginal = '<?php echo $email_original ?>';

            function actualizarEstadoBoton() {
                if (inputNombre.value !== nombreOriginal ||
                    inputApellido.value !== apellidoOriginal ||
                    inputEmail.value !== emailOriginal ||
                    (inputContrasena.value.trim() !== '' && inputRepetirContrasena.value.trim() !== '')) {
                    buttonGuardar.disabled = false;
                } else {
                    buttonGuardar.disabled = true;
                }
            }

            function habilitarRepetirContrasena() {
                if (inputContrasena.value.trim() !== '') {
                    inputRepetirContrasena.disabled = false;
                } else {
                    inputRepetirContrasena.disabled = true;
                }
            }
            inputNombre.addEventListener('input', actualizarEstadoBoton);
            inputApellido.addEventListener('input', actualizarEstadoBoton);
            inputEmail.addEventListener('input', actualizarEstadoBoton);
            inputContrasena.addEventListener('input', habilitarRepetirContrasena);
            inputRepetirContrasena.addEventListener('input', actualizarEstadoBoton);
        });
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>