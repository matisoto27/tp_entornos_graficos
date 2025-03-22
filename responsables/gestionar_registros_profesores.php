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

// Paginación.
// Definir la cantidad de registros por página.
$registros_por_pagina = 7;
// Determinar la página actual.
if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
    $pagina_actual = $_GET['pagina'];
} else {
    $pagina_actual = 1;
}
// Calcular el desplazamiento (offset) de los registros.
$offset = ($pagina_actual - 1) * $registros_por_pagina;
// Contar el total de registros.
$result_count = $mysqli->query("SELECT COUNT(*) AS total FROM usuarios WHERE contrasena IS NULL");
$row_count = $result_count->fetch_assoc();
$total_registros = $row_count['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
// Fin Paginación.

// Lógica.
$result = $mysqli->query("SELECT * FROM usuarios WHERE contrasena IS NULL ORDER BY apellido, nombre LIMIT $offset, $registros_por_pagina");
?>

<!doctype html>
<html lang="en">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <!-- Centrado vertical y horizontal -->
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="container br-class bg-white min-vh-xs-100">
            <div class="row pt-4 mb-3">
                <div class="col">
                    <h2 class="text-center">Solicitudes de Registro</h2>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="table-responsive">
                        <table class="table table-striped text-center" style="min-width: 1100px;">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-1">DNI</th>
                                    <th scope="col" class="col-2">Nombre</th>
                                    <th scope="col" class="col-2">Apellido</th>
                                    <th scope="col" class="col-2">Fecha de Nacimiento</th>
                                    <th scope="col" class="col-3">Correo Electrónico</th>
                                    <th scope="col" class="col-1">Rechazar</th>
                                    <th scope="col" class="col-1">Aprobar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $contador = 0;
                                if ($result->num_rows > 0) {
                                    while ($profesor = $result->fetch_assoc()) {
                                        $contador++;
                                ?>
                                        <tr>
                                            <td><?php echo $profesor['dni'] ?></td>
                                            <td><?php echo $profesor['nombre'] ?></td>
                                            <td><?php echo $profesor['apellido'] ?></td>
                                            <td><?php echo $profesor['fecha_nacimiento'] ?></td>
                                            <td><?php echo $profesor['email'] ?></td>
                                            <td>
                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-rechazar" onclick="rechazarSolicitud('<?php echo $profesor['nombre'] ?>', '<?php echo $profesor['apellido'] ?>', '<?php echo $profesor['dni'] ?>')">
                                                    <i class="bi bi-x-lg" style="color: white;"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-aprobar" onclick="aprobarSolicitud('<?php echo $profesor['nombre'] ?>', '<?php echo $profesor['apellido'] ?>', '<?php echo $profesor['dni'] ?>')">
                                                    <i class="bi bi-check-lg" style="color: white;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    // Rellenar las filas restantes si no hay suficientes registros.
                                    while ($contador < $registros_por_pagina) {
                                        $contador++;
                                    ?>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay solicitudes pendientes</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <nav class="d-flex flex-column justify-content-center h-100" aria-label="Paginación">
                        <ul class="pagination justify-content-center" style="margin-bottom: 0;">
                            <!-- Paginación: Anterior -->
                            <?php if ($pagina_actual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">Anterior</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Anterior</span>
                                </li>
                            <?php endif; ?>

                            <!-- Paginación: Mostrar páginas -->
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <?php if ($i == $pagina_actual): ?>
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link"><?php echo $i; ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Paginación: Siguiente -->
                            <?php if ($pagina_actual < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">Siguiente</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Siguiente</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col text-center">
                    <button type="button" class="btn btn-primary p-2" style="width: 250px;" onclick='window.location.href="menu_principal.php"'>Volver</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Rechazar -->
    <div class="modal fade" id="modal-rechazar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-rechazar-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h5 class="modal-title">Rechazar Solicitud</h5>
                </div>
                <div class="modal-body" id="modal-rechazar-body"></div>
                <div class="modal-footer justify-content-around px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="gestionar_registros_profesores_action.php">
                        <input type="hidden" value="rechazar" name="accion">
                        <input type="hidden" name="dni" id="hidden-input-rechazar-dni">
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Aprobar -->
    <div class="modal fade" id="modal-aprobar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-aprobar-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h5 class="modal-title">Aprobar Solicitud</h5>
                </div>
                <div class="modal-body" id="modal-aprobar-body"></div>
                <div class="modal-footer justify-content-around px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="gestionar_registros_profesores_action.php">
                        <input type="hidden" value="aprobar" name="accion">
                        <input type="hidden" name="dni" id="hidden-input-aprobar-dni">
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        function rechazarSolicitud(nombre, apellido, dni) {
            document.getElementById('modal-rechazar-body').innerText = '¿Está seguro(a) de que desea rechazar la solicitud de registro de ' + nombre + ' ' + apellido + '?';
            document.getElementById('hidden-input-rechazar-dni').value = dni;
        }

        function aprobarSolicitud(nombre, apellido, dni) {
            document.getElementById('modal-aprobar-body').innerText = '¿Está seguro(a) de que desea aprobar la solicitud de registro de ' + nombre + ' ' + apellido + '?';
            document.getElementById('hidden-input-aprobar-dni').value = dni;
        }
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>