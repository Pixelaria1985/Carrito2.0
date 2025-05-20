<?php
include('conexion.php');

// Si se ha enviado el formulario de compra
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $fecha_compra = date('Y-m-d H:i:s'); // Fecha actual

    // Obtener el precio de compra y el stock actual del producto
    $sql = "SELECT precio_compra, stock FROM productos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        // Aumentar el stock del producto
        $nuevo_stock = $producto['stock'] + $cantidad;
        
        // Actualizar el stock del producto en la base de datos
        $sql_update = "UPDATE productos SET stock = ? WHERE id = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->execute([$nuevo_stock, $producto_id]);

        // Registrar la compra en la tabla de compras
        $sql_compra = "INSERT INTO compras (producto_id, cantidad, fecha_compra) VALUES (?, ?, ?)";
        $stmt_compra = $conexion->prepare($sql_compra);
        $stmt_compra->execute([$producto_id, $cantidad, $fecha_compra]);

        // Registrar en el historial de compras
        $sql_historial = "INSERT INTO historial_compras (producto_id, cantidad, fecha) VALUES (?, ?, ?)";
        $stmt_historial = $conexion->prepare($sql_historial);
        $stmt_historial->execute([$producto_id, $cantidad, $fecha_compra]);

        echo "Compra registrada con éxito. El stock ha sido actualizado.";
    } else {
        echo "Producto no encontrado.";
    }
}

// Obtener todos los productos para el formulario de compra
$sql_productos = "SELECT id, nombre FROM productos";
$stmt_productos = $conexion->prepare($sql_productos);
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Compra</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Registrar Compra</h2>
        <form method="POST">
            <div class="form-group">
                <label for="producto_id">Seleccionar Producto:</label>
                <select class="form-control" id="producto_id" name="producto_id" required>
                    <option value="">Seleccione un producto</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?= $producto['id']; ?>"><?= $producto['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="cantidad">Cantidad:</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Compra</button>
        </form>
    </div>
</body>
</html>
