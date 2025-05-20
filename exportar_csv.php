<?php
include('conexion.php');

// Verificar que se recibió el tipo de exportación
if (!isset($_GET['tipo'])) {
    die("Error: tipo de exportación no especificado.");
}

$tipo = $_GET['tipo'];

if ($tipo === 'compras') {
    $sql = "
        SELECT c.id, p.nombre AS producto, c.cantidad, p.precio_compra AS precio, c.fecha_compra AS fecha
        FROM compras c
        INNER JOIN productos p ON c.producto_id = p.id
        ORDER BY c.fecha_compra DESC
    ";
    $nombre_archivo = "historial_compras_" . date('Y-m-d_H-i-s') . ".csv";
    $encabezados = ['ID Compra', 'Producto', 'Cantidad', 'Precio Compra', 'Fecha Compra'];
} elseif ($tipo === 'ventas') {
    $sql = "
        SELECT v.id, p.nombre AS producto, v.cantidad, p.precio_venta AS precio, v.fecha_venta AS fecha
        FROM ventas v
        INNER JOIN productos p ON v.producto_id = p.id
        ORDER BY v.fecha_venta DESC
    ";
    $nombre_archivo = "historial_ventas_" . date('Y-m-d_H-i-s') . ".csv";
    $encabezados = ['ID Venta', 'Producto', 'Cantidad', 'Precio Venta', 'Fecha Venta'];
} else {
    die("Error: tipo no válido. Usa 'ventas' o 'compras'.");
}

// Ejecutar la consulta
$stmt = $conexion->prepare($sql);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar headers de descarga
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');

$output = fopen('php://output', 'w');
fputcsv($output, $encabezados);

// Escribir los datos
foreach ($registros as $registro) {
    fputcsv($output, [
        $registro['id'],
        $registro['producto'],
        $registro['cantidad'],
        number_format($registro['precio'], 2),
        $registro['fecha']
    ]);
}

fclose($output);
exit;
