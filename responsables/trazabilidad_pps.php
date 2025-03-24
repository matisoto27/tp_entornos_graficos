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

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Lógica.
$result = $mysqli->query("SELECT u.dni AS dni, nombre, apellido FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni WHERE dni_profesor IS NOT NULL AND estado_solicitud = 'Confirmada' ORDER BY apellido, nombre");
?>

<!doctype html>
<html lang="es">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <main>
        <div class="d-flex justify-content-center align-items-center min-vh-100" id="trazabilidad-pps">
            <div class="container br-class bg-white form-container">
                <h1 class="text-center my-4">Trazabilidad de PPS</h1>
                <form method="POST" action="trazabilidad_pps_action.php" class="mx-auto" style="width: 250px;">
                    <div class="mb-4">
                        <input list="lista-alumnos" class="form-control" <?php if ($result->num_rows === 0) echo 'placeholder="Error al cargar los alumnos" disabled';
                                                                            else echo 'placeholder="Escriba el nombre del alumno"'; ?>
                            name="alumno" id="input-alumno" onchange="actualizarDni()" required>
                        <datalist id="lista-alumnos">
                            <?php
                            if ($result->num_rows > 0) {
                                while ($alumno = $result->fetch_assoc()) {
                            ?>
                                    <option data-dni="<?php echo $alumno['dni'] ?>" data-apellido="<?php echo $alumno['apellido'] ?>" data-nombre="<?php echo $alumno['nombre'] ?>"><?php echo $alumno['apellido'] . ', ' . $alumno['nombre'] ?></option>
                            <?php
                                }
                            }
                            ?>
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-success w-100" id="submit-button" aria-label="Generar trazabilidad del alumno" disabled>Generar Trazabilidad</button>
                    </div>
                    <div class="mb-4">
                        <button type="button" class="btn btn-primary w-100" aria-label="Volver al menú principal" onclick='window.location.href="menu_principal.php"'>Volver</button>
                    </div>
                    <input type="hidden" name="dni" id="input-dni">
                    <input type="hidden" name="apellido" id="input-apellido">
                    <input type="hidden" name="nombre" id="input-nombre">
                </form>
            </div>
        </div>
    </main>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        document.getElementById('input-alumno').addEventListener('input', function() {
            var dni = '';
            var nombre = '';
            var apellido = '';
            var nombreProfesor = '';
            var apellidoProfesor = '';
            var inputValue = this.value.trim();
            var opciones = document.querySelectorAll('#lista-alumnos option');
            opciones.forEach(function(opcion) {
                if (opcion.value === inputValue) {
                    dni = opcion.getAttribute('data-dni');
                    apellido = opcion.getAttribute('data-apellido');
                    nombre = opcion.getAttribute('data-nombre');
                }
            });
            document.getElementById('input-dni').value = dni;
            document.getElementById('input-apellido').value = apellido;
            document.getElementById('input-nombre').value = nombre;
            // Habilitar el botón si se ha encontrado un valor.
            document.getElementById('submit-button').disabled = !dni || !apellido || !nombre;
        });
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>