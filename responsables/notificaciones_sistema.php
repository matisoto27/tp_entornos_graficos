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
$result_count = $mysqli->query("SELECT COUNT(*) AS total FROM notificaciones_sistema");
$row_count = $result_count->fetch_assoc();
$total_registros = $row_count['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
// Fin Paginación.

// Lógica.
$result = $mysqli->query("SELECT * FROM notificaciones_sistema ORDER BY id_notificacion DESC LIMIT $offset, $registros_por_pagina");
?>

<!doctype html>
<html lang="es">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <main>
        <div class="d-flex justify-content-center align-items-center min-vh-100">
            <div class="container br-class bg-white min-vh-xs-100">
                <div class="row pt-4 mb-3">
                    <div class="col">
                        <h1 class="text-center">Notificaciones del Sistema</h1>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover text-center">
                                <thead>
                                    <tr>
                                        <th scope="col" class="col-3">Fecha Envio</th>
                                        <th scope="col" class="col-6">Titulo</th>
                                        <th scope="col" class="col-3">Mensaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    if ($result->num_rows > 0) {
                                        while ($notificacion = $result->fetch_assoc()) {
                                            $contador++;
                                    ?>
                                            <tr data-bs-toggle="modal" data-bs-target="#modal-notificacion" onclick="handleRowClick('<?php echo $notificacion['titulo'] ?>', '<?php echo $notificacion['mensaje'] ?>')">
                                                <td><?php echo $notificacion['fecha_enviada'] ?></td>
                                                <td><?php echo $notificacion['titulo'] ?></td>
                                                <td><?php echo substr($notificacion['mensaje'], 0, 15) . (strlen($notificacion['mensaje']) > 15 ? '...' : '') ?></td>
                                                <form id="form-<?php echo $notificacion['id_notificacion'] ?>" action="update_notificacion.php" method="post">
                                                    <input type="hidden" name="id_notificacion" value="<?php echo $notificacion['id_notificacion'] ?>">
                                                </form>
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
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Todavia no tienes ninguna notificación</td>
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
                        <button type="button" class="btn btn-primary p-2" style="width: 250px;" aria-label="Volver al menú principal" onclick='window.location.href="menu_principal.php"'>Volver</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Modal -->
    <div class="modal fade" id="modal-notificacion" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-notificacion-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-notificacion-title"></h5>
                </div>
                <div class="modal-body" id="modal-mensaje"></div>
                <div class="modal-footer">
                    <div class="text-center w-100">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Cerrar notificicación">Recibido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        function handleRowClick(titulo, mensaje) {
            document.getElementById('modal-notificacion-title').innerText = titulo;
            document.getElementById('modal-mensaje').innerText = mensaje;
        }
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>