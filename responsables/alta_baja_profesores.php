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
$result_count = $mysqli->query("SELECT COUNT(*) AS total FROM profesores");
$row_count = $result_count->fetch_assoc();
$total_registros = $row_count['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
// Fin Paginación.

// Lógica.
$result = $mysqli->query("SELECT * FROM usuarios u INNER JOIN profesores p ON u.dni = p.dni ORDER BY apellido, nombre LIMIT $offset, $registros_por_pagina");
?>

<!doctype html>
<html lang="en">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <!-- Centrado vertical y horizontal -->
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="container background-border min-vh-xs-100">
            <div class="row pt-4 mb-3">
                <div class="col">
                    <h2 class="text-center">Profesores en el Sistema</h2>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-2">Dni</th>
                                    <th scope="col" class="col-2">Nombre</th>
                                    <th scope="col" class="col-2">Apellido</th>
                                    <th scope="col" class="col-4">Correo Electrónico</th>
                                    <th scope="col" class="col-2">Estado</th>
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
                                            <td><?php echo $profesor["dni"] ?></td>
                                            <td><?php echo $profesor["nombre"] ?></td>
                                            <td><?php echo $profesor["apellido"] ?></td>
                                            <td><?php echo $profesor["email"] ?></td>
                                            <?php
                                            if ($profesor["activo"] == 1) {
                                            ?>
                                                <td><button type="button" class="btn btn-success btn-md" data-bs-toggle="modal" data-bs-target="#modal-profesor" onclick="abrirModal('<?php echo $profesor['dni'] ?>', '<?php echo $profesor['nombre'] ?>', '<?php echo $profesor['apellido'] ?>', '<?php echo $profesor['activo'] ?>')">Activo</button></td>
                                            <?php
                                            } else {
                                            ?>
                                                <td><button type="button" class="btn btn-danger btn-md" data-bs-toggle="modal" data-bs-target="#modal-profesor" onclick="abrirModal('<?php echo $profesor['dni'] ?>', '<?php echo $profesor['nombre'] ?>', '<?php echo $profesor['apellido'] ?>', '<?php echo $profesor['activo'] ?>')">Dado de Baja</button></td>
                                            <?php
                                            }
                                            ?>
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
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="5">Ha ocurrido un error al cargar los profesores</td>
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
    <!-- Modal -->
    <div class="modal fade" id="modal-profesor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-profesor-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-profesor-title"></h5>
                </div>
                <div class="modal-footer justify-content-center">
                    <form method="POST" action="alta_baja_profesores_action.php">
                        <input type="hidden" name="dni" id="input-dni">
                        <input type="hidden" name="activo" id="input-activo">
                        <button type="submit" class="btn btn-success">Confirmar</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

    <script>
        function abrirModal(dni, nombre, apellido, activo) {

            // Establecer el título del modal.
            const modalProfesorTitle = document.getElementById('modal-profesor-title');
            if (activo == 0) modalProfesorTitle.innerText = '¿Está seguro de que desea reactivar en el sistema al profesor(a) ' + apellido + ' ' + nombre + '?';
            else modalProfesorTitle.innerText = '¿Está seguro de que desea dar de baja en el sistema al profesor(a) ' + apellido + ' ' + nombre + '?';

            // Asignar valores a los campos ocultos del formulario.
            document.getElementById('input-dni').value = dni;
            document.getElementById('input-activo').value = activo;
        }
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>