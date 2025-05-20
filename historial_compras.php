<?php
include('conexion.php');

// Obtener todas las compras realizadas
$sql_compras = "
    SELECT c.id, p.nombre AS producto, c.cantidad, p.precio_compra, c.fecha_compra
    FROM compras c
    INNER JOIN productos p ON c.producto_id = p.id
    ORDER BY c.fecha_compra DESC
";

$stmt_compras = $conexion->prepare($sql_compras);
$stmt_compras->execute();
$compras = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);

// Exportar a CSV
if (isset($_POST['exportar_csv'])) {
    $filename = "historial_compras_" . date('Y-m-d_H-i-s') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID Compra', 'Producto', 'Cantidad', 'Precio Compra', 'Fecha Compra']);

    foreach ($compras as $compra) {
        fputcsv($output, [
            $compra['id'],
            $compra['producto'],
            $compra['cantidad'],
            number_format($compra['precio_compra'], 2),
            $compra['fecha_compra']
        ]);
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Historial de Compras</h2>
        <form method="POST">
            <button type="submit" name="exportar_csv" class="btn btn-success mb-3">Exportar a CSV</button>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Compra</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Compra</th>
                    <th>Fecha Compra</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td><?= $compra['id']; ?></td>
                        <td><?= $compra['producto']; ?></td>
                        <td><?= $compra['cantidad']; ?></td>
                        <td>$<?= number_format($compra['precio_compra'], 2); ?></td>
                        <td><?= $compra['fecha_compra']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
