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
$href_modificar_perfil = '';
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

// Lógica.
$stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE dni = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$nombre_original = $usuario['nombre'];
$apellido_original = $usuario['apellido'];
$fecha_nacimiento_original = $usuario['fecha_nacimiento'];
$email_original = $usuario['email'];
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
        <div class="container br-class alt-background form-container">
            <h1 class="text-center pt-3 mb-3">Modificar Perfil</h1>
            <form method="POST" action="modificar_perfil_action.php" class="mx-auto" style="width: 280px;">
                <div class="mb-3">
                    <label for="input-nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="input-nombre" value="<?php echo $usuario['nombre'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" name="apellido" id="input-apellido" value="<?php echo $usuario['apellido'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-fecha-nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" name="fecha-nacimiento" id="input-fecha-nacimiento" value="<?php echo $usuario['fecha_nacimiento'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-email" class="form-label">Correo Electronico</label>
                    <input type="email" class="form-control" name="email" id="input-email" value="<?php echo $usuario['email'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="contrasena" id="input-contrasena" placeholder="Aquí puedes cambiar tu contraseña">
                </div>
                <div class="mb-4">
                    <label for="input-repetir-contrasena" class="form-label">Repetir Nueva Contraseña</label>
                    <input type="password" class="form-control" name="repetir-contrasena" id="input-repetir-contrasena" disabled>
                </div>
                <div class="pb-4">
                    <button type="submit" class="btn btn-primary w-100" id="button-guardar" aria-label="Guardar cambios" disabled>Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/footer.php';
    ?>
    <!-- Modal -->
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const inputNombre = document.getElementById('input-nombre');
            const inputApellido = document.getElementById('input-apellido');
            const inputFechaNacimiento = document.getElementById('input-fecha-nacimiento');
            const inputEmail = document.getElementById('input-email');
            const inputContrasena = document.getElementById('input-contrasena');
            const inputRepetirContrasena = document.getElementById('input-repetir-contrasena');
            const buttonGuardar = document.getElementById('button-guardar');

            const nombreOriginal = '<?php echo $nombre_original ?>';
            const apellidoOriginal = '<?php echo $apellido_original ?>';
            const fechaNacimientoOriginal = '<?php echo $fecha_nacimiento_original ?>';
            const emailOriginal = '<?php echo $email_original ?>';

            function actualizarEstadoBoton() {
                if (inputNombre.value !== nombreOriginal || inputApellido.value !== apellidoOriginal || inputFechaNacimiento.value !== fechaNacimientoOriginal || inputEmail.value !== emailOriginal || (inputContrasena.value.trim() !== '' && inputRepetirContrasena.value.trim() !== '')) buttonGuardar.disabled = false;
                else buttonGuardar.disabled = true;
            }

            function habilitarRepetirContrasena() {
                if (inputContrasena.value.trim() !== '') inputRepetirContrasena.disabled = false;
                else inputRepetirContrasena.disabled = true;
            }

            inputNombre.addEventListener('input', actualizarEstadoBoton);
            inputApellido.addEventListener('input', actualizarEstadoBoton);
            inputFechaNacimiento.addEventListener('input', actualizarEstadoBoton);
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