<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'alumnos') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['dni'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: ../login.php");
    exit();
} else {
    $dni = $_SESSION['dni'];
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no estén vacíos.
    if (empty($_POST['id-informe']) || empty($_POST['apellido']) || empty($_POST['nombre'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Obtener datos a través del método POST.
    $id_informe = $_POST['id-informe'];
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];

    // Validar que el archivo se haya subido correctamente.
    if (isset($_FILES['informe-corregido']) && $_FILES['informe-corregido']['error'] === 0) {

        // Validar tipo de archivo.
        if ($_FILES['informe-corregido']['type'] !== 'application/pdf') {
            $_SESSION['mensaje_error'] = "Solo se admiten archivos en formato PDF.";
            header("Location: ../menu_principal.php");
            exit();
        }

        // Generar nombre y ruta del archivo.
        $nombre_archivo = strtolower($apellido) . '_' . strtolower($nombre) . '_informe' . $id_informe . '_corregido.pdf';
        $ruta_archivo = 'informes/' . $nombre_archivo;

        // Validar si el archivo se movió correctamente.
        if (!move_uploaded_file($_FILES['informe-corregido']['tmp_name'], $ruta_archivo)) {
            $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
            header("Location: ../menu_principal.php");
            exit();
        }

        // Establecer variables para la consulta.
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha_actual = date("Y-m-d H:i:s");
        $fecha_subida = date("Y-m-d H:i:s", strtotime("-1 month", strtotime($fecha_actual)));
        $estado = 'PENDIENTE';

        // Abrir la conexión a la base de datos.
        include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO informes (dni_alumno, id_informe, original, nombre_archivo, ruta_archivo, fecha_subida, estado, final) VALUES (?, ?, 0, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sissss", $dni, $id_informe, $nombre_archivo, $ruta_archivo, $fecha_subida, $estado);
        $stmt->execute();

        // Cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
        $mysqli->close();
        header("Location: ../menu_principal.php");
        exit();
    } else {

        // Establecer mensaje de error, redireccionar y finalizar el script actual.
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: ../menu_principal.php");
        exit();
    }
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: ../menu_principal.php");
    exit();
}
