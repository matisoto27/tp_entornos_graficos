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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar que los campos obligatorios no estén vacíos.
    if (!isset($_POST['subir'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Validar que el archivo se haya subido correctamente.
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {

        // Validar tipo de archivo.
        if ($_FILES['archivo']['type'] !== 'application/pdf') {
            $_SESSION['mensaje_error'] = "Solo se admiten archivos en formato PDF.";
            header("Location: ../menu_principal.php");
            exit();
        }

        // Abrir la conexión a la base de datos.
        include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

        // Recuperar nombre y apellido del alumno.
        $stmt = $mysqli->prepare("SELECT nombre, apellido FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $_SESSION['dni']);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        // Generar nombre y ruta del archivo.
        $archivo_plan_trabajo = 'plan_de_trabajo_' . strtolower($usuario['apellido']) . '_' . strtolower($usuario['nombre']) . '.pdf';
        $ruta_archivo = 'planes-de-trabajo/' . $archivo_plan_trabajo;

        // Validar si el archivo se movió correctamente.
        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_archivo)) {
            $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
            header("Location: ../menu_principal.php");
            exit();
        }

        // Establecer variables para la consulta.
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha_plan_trabajo = date("Y-m-d H:i:s");

        // Prepared statement.
        $stmt = $mysqli->prepare("UPDATE alumnos SET archivo_plan_trabajo = ?, fecha_plan_trabajo = ? WHERE dni = ?");
        $stmt->bind_param("sss", $archivo_plan_trabajo, $fecha_plan_trabajo, $dni);
        $stmt->execute();

        // Establecer mensaje de éxito y cerrar la conexión a la base de datos.
        $_SESSION['mensaje_exito'] = "El archivo se ha subido correctamente.";
        $mysqli->close();
    }
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
}

// Redireccionar y finalizar el script actual.
header("Location: ../menu_principal.php");
exit();
