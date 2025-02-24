<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['profesores', 'responsables'])) {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['dni']) && (empty($_SESSION['codigo']) || empty($_SESSION['nombre']) || empty($_SESSION['apellido']))) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: ../login.php");
    exit();
}

// Validar si se reciben datos por GET.
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    // Validación de campos obligatorios.
    if (!empty($_GET['nombre-archivo']) && isset($_GET['es-informe'])) {

        $nombre_archivo = $_GET['nombre-archivo'];
        $es_informe = $_GET['es-informe'];
        if ($es_informe) {
            $ruta_archivo = $_SERVER['DOCUMENT_ROOT'] . '/usuarios/alumnos/informes/' . $nombre_archivo;
            if (file_exists($ruta_archivo)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
                header('Content-Length: ' . filesize($ruta_archivo));
                readfile($ruta_archivo);
            } else {
                $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
            }
        } else {
            $ruta_archivo = $_SERVER['DOCUMENT_ROOT'] . '/usuarios/alumnos/planes-de-trabajo/' . $nombre_archivo;
            if (file_exists($ruta_archivo)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
                header('Content-Length: ' . filesize($ruta_archivo));
                readfile($ruta_archivo);
            } else {
                $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
            }
        }
        header("Location: http://entornosgraficospps.infinityfreeapp.com/");
        exit();
    } else {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: http://entornosgraficospps.infinityfreeapp.com/");
        exit();
    }
} else {
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}
