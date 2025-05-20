<?php
$host = 'localhost'; // Usualmente localhost si usas XAMPP
$usuario = 'root';   // Usuario por defecto en XAMPP
$clave = '';         // Sin clave por defecto en XAMPP
$base_de_datos = 'gestion_productos';

try {
    $conexion = new PDO("mysql:host=$host;dbname=$base_de_datos", $usuario, $clave);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
}
?>
