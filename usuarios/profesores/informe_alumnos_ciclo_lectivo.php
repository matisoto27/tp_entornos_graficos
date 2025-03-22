<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'profesores') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['dni'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: ../login.php");
    exit();
}

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Lógica.
$result = $mysqli->query("SELECT * FROM ciclos_lectivos");

// Redireccionar.
$href_pps = '';
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

<body class="d-flex flex-column">
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/header.php';
    ?>
    <main class="d-flex justify-content-center align-items-center flex-fill border-top border-bottom">
        <div class="container br-class alt-background form-container">
            <h2 class="text-center pt-4 mb-4">Buscar Alumnos</h2>
            <form method="POST" action="informe_alumnos_ciclo_lectivo_action.php" class="mx-auto" style="width: 230px;">
                <div class="mb-4">
                    <input list="lista-ciclos-lectivos" class="form-control" name="ciclo-lectivo" id="input-ciclo-lectivo" onchange="actualizarDni()" placeholder="Seleccionar un Ciclo Lectivo" required>
                    <datalist id="lista-ciclos-lectivos">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($ciclo_lectivo = $result->fetch_assoc()) {
                        ?>
                                <option data-id-ciclo-lectivo="<?php echo $ciclo_lectivo['id_ciclo_lectivo'] ?>" data-anio="<?php echo $ciclo_lectivo['anio'] ?>" data-ciclo="<?php echo $ciclo_lectivo['ciclo'] ?>"><?php echo $ciclo_lectivo['anio'] . ' - Ciclo ' . $ciclo_lectivo['ciclo'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </datalist>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success w-100">Descargar</button>
                </div>
                <div class="pb-4">
                    <button type="button" class="btn btn-primary w-100" onclick='window.location.href="../profesores/informes.php"'>Volver</button>
                </div>
                <input type="hidden" name="id-ciclo-lectivo" id="input-id-ciclo-lectivo">
                <input type="hidden" name="anio" id="input-anio">
                <input type="hidden" name="ciclo" id="input-ciclo">
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
        document.getElementById('input-ciclo-lectivo').addEventListener('input', function() {
            var inputValue = this.value.trim();
            var dataIdCicloLectivo = '';
            var anio = '';
            var ciclo = 0;
            var opciones = document.querySelectorAll('#lista-ciclos-lectivos option');
            opciones.forEach(function(opcion) {
                if (opcion.value === inputValue) {
                    dataIdCicloLectivo = opcion.getAttribute('data-id-ciclo-lectivo');
                    anio = opcion.getAttribute('data-anio');
                    ciclo = opcion.getAttribute('data-ciclo');
                }
            });
            document.getElementById('input-id-ciclo-lectivo').value = dataIdCicloLectivo;
            document.getElementById('input-anio').value = anio;
            document.getElementById('input-ciclo').value = ciclo;
        });
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>