<?php
include('conexion.php');

// Obtener todas las ventas
$ventas = $conexion->query("SELECT * FROM ventas")->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los descuentos
$descuentos = [];
$metodos = $conexion->query("SELECT * FROM descuentos")->fetchAll(PDO::FETCH_ASSOC);
foreach ($metodos as $m) {
    $descuentos[$m['metodo_pago']] = abs($m['porcentaje_descuento']); // Asegura que sea positivo
}

// Filtrar ventas por fecha si se ha seleccionado
if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $ventas = array_filter($ventas, function ($venta) use ($fecha) {
        return substr($venta['fecha_venta'], 0, 10) === $fecha;  // Comparar la fecha en formato YYYY-MM-DD
    });
}

// Variables para los totales
$total_sin_descuento = 0;
$total_con_descuento = 0;
$total_diferencia = 0;
$total_cantidad_productos = 0;

// Exportar a CSV
if (isset($_GET['exportar_csv'])) {
    $filename = "ventas_" . date('Y-m-d_H-i-s') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID Venta', 'Producto', 'Cantidad', 'Fecha de Venta', 'Método de Pago', 'Total sin Descuento', 'Total con Descuento']);

    foreach ($ventas as $venta) {
        // Obtener info del producto
        $stmt = $conexion->prepare("SELECT nombre, precio_venta FROM productos WHERE id = ?");
        $stmt->execute([$venta['producto_id']]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        $subtotal = $producto['precio_venta'] * $venta['cantidad'];
        $porcentaje_descuento = $descuentos[$venta['metodo_pago']] ?? 0;
        $descuento = $subtotal * ($porcentaje_descuento / 100);
        $total_final = $subtotal - $descuento;

        // Escribir en el CSV
        fputcsv($output, [
            $venta['id'],
            $producto['nombre'],
            $venta['cantidad'],
            $venta['fecha_venta'],
            $venta['metodo_pago'],
            number_format($subtotal, 2),
            number_format($total_final, 2)
        ]);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Ventas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Historial de Ventas</h2>

    <!-- Formulario para seleccionar fecha -->
    <form method="GET" class="form-inline mb-3">
        <label for="fecha" class="mr-2">Seleccionar Fecha:</label>
        <input type="date" name="fecha" id="fecha" class="form-control mr-2" value="<?= isset($fecha) ? $fecha : '' ?>">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <!-- Botón para exportar a CSV -->
    <a href="?exportar_csv=true" class="btn btn-success mb-3">Exportar CSV</a>

    <!-- Tabla de ventas -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Fecha de Venta</th>
                <th>Método de Pago</th>
                <th>Total sin Descuento</th>
                <th>Total con Descuento</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($ventas)): ?>
                <?php foreach ($ventas as $venta): ?>
                    <?php
                        // Obtener info del producto
                        $stmt = $conexion->prepare("SELECT nombre, precio_venta FROM productos WHERE id = ?");
                        $stmt->execute([$venta['producto_id']]);
                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                        $subtotal = $producto['precio_venta'] * $venta['cantidad'];
                        $porcentaje_descuento = $descuentos[$venta['metodo_pago']] ?? 0;
                        $descuento = $subtotal * ($porcentaje_descuento / 100);
                        $total_final = $subtotal - $descuento;

                        // Acumulación de totales
                        $total_sin_descuento += $subtotal;
                        $total_con_descuento += $total_final;
                        $total_diferencia += ($subtotal - $total_final);
                        $total_cantidad_productos += $venta['cantidad'];
                    ?>
                    <tr>
                        <td><?= $venta['id']; ?></td>
                        <td><?= $producto['nombre']; ?></td>
                        <td><?= $venta['cantidad']; ?></td>
                        <td><?= $venta['fecha_venta']; ?></td>
                        <td><?= $venta['metodo_pago']; ?></td>
                        <td>$<?= number_format($subtotal, 2); ?></td>
                        <td>$<?= number_format($total_final, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No se encontraron ventas para la fecha seleccionada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Totales -->
    <table class="table table-bordered mt-4">
        <tr>
            <th>Total sin descuento</th>
            <td>$<?= number_format($total_sin_descuento, 2); ?></td>
        </tr>
        <tr>
            <th>Total con descuento</th>
            <td>$<?= number_format($total_con_descuento, 2); ?></td>
        </tr>
        <tr>
            <th>Diferencia (sin descuento - con descuento)</th>
            <td>$<?= number_format($total_diferencia, 2); ?></td>
        </tr>
        <tr>
            <th>Cantidad total de productos</th>
            <td><?= $total_cantidad_productos; ?></td>
        </tr>
    </table>

    <a href="index.php" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>
