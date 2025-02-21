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
    if (empty($_POST['apellido']) || empty($_POST['nombre']) || !isset($_POST['subir-informe'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Obtener datos a través del método POST.
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];

    // Validar que el archivo se haya subido correctamente.
    if (isset($_FILES['informe']) && $_FILES['informe']['error'] === 0) {

        // Validar tipo de archivo.
        if ($_FILES['informe']['type'] !== 'application/pdf') {
            $_SESSION['mensaje_error'] = "Solo se admiten archivos en formato PDF.";
            header("Location: ../menu_principal.php");
            exit();
        }

        // Abrir la conexión a la base de datos.
        include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

        // Establecer ID del informe.
        $stmt = $mysqli->prepare("SELECT COALESCE(MAX(id_informe), 0) + 1 AS id FROM informes WHERE dni_alumno = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $result = $stmt->get_result();
        $informe = $result->fetch_assoc();
        $id_informe = $informe['id'];

        // Generar nombre y ruta del archivo.
        $nombre_archivo = strtolower($apellido) . '_' . strtolower($nombre) . '_informe' . $id_informe . '.pdf';
        $ruta_archivo = 'informes/' . $nombre_archivo;

        // Validar si el archivo se movió correctamente.
        if (!move_uploaded_file($_FILES['informe']['tmp_name'], $ruta_archivo)) {
            $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
            header("Location: ../menu_principal.php");
            exit();
        }

        // Establecer variables para la consulta.
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha_subida = date("Y-m-d H:i:s");
        $estado = 'PENDIENTE';

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO informes (dni_alumno, id_informe, original, nombre_archivo, ruta_archivo, fecha_subida, estado, final) VALUES (?, ?, 1, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sissss", $dni, $id_informe, $nombre_archivo, $ruta_archivo, $fecha_subida, $estado);
        $stmt->execute();

        // Establecer mensaje de éxito y cerrar la conexión a la base de datos.
        $_SESSION['mensaje_exito'] = "Se ha registrado el informe con éxito.";
        $mysqli->close();
    } else {

        // Establecer mensaje de error.
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
    }
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
}

// Redireccionar y finalizar el script actual.
header("Location: ../menu_principal.php");
exit();
