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

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['dni']) || empty($_POST['accion'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: menu_principal.php");
        exit();
    }

    // Recuperar datos del formulario.
    $dni = $_POST['dni'];
    $accion = $_POST['accion'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Validar si la acción es aprobar o rechazar.
    if ($accion === 'rechazar') {

        // Prepared statement.
        $stmt = $mysqli->prepare("DELETE FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
    } else {

        // Generar contraseña.
        function generarContrasena()
        {
            $caracteres = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $contrasena = '';
            for ($i = 0; $i < 8; $i++) {
                $index = rand(0, strlen($caracteres) - 1);
                $contrasena .= $caracteres[$index];
            }
            return $contrasena;
        }
        $contrasena = generarContrasena();

        // Prepared statement.
        $stmt = $mysqli->prepare("UPDATE usuarios SET contrasena = ? WHERE dni = ?");
        $stmt->bind_param("ss", $contrasena, $dni);
        $stmt->execute();

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO profesores (dni, activo) VALUES (?, 1)");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
    }

    // Cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
    $mysqli->close();
    header("Location: gestionar_registros_profesores.php");
    exit();
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}
