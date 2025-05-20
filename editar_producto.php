<?php
include('conexion.php');

// Verificar si se recibe el ID
if (!isset($_GET['id'])) {
    die("ID de producto no especificado.");
}

$id = $_GET['id'];

// Obtener datos del producto
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado.");
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $stock = $_POST['stock'];

    $stmt = $conexion->prepare("
        UPDATE productos
        SET nombre = ?, descripcion = ?, categoria = ?, precio_compra = ?, precio_venta = ?, stock = ?
        WHERE id = ?
    ");

    $resultado = $stmt->execute([
        $nombre, $descripcion, $categoria,
        $precio_compra, $precio_venta, $stock, $id
    ]);

    if ($resultado) {
        header("Location: productos.php");
        exit;
    } else {
        $mensaje = "Error al actualizar el producto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Editar Producto</h2>

    <?php if (isset($mensaje)): ?>
        <div class="alert alert-danger"><?= $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']); ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?= htmlspecialchars($producto['descripcion']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="categoria">Categoría</label>
            <input type="text" class="form-control" id="categoria" name="categoria" value="<?= htmlspecialchars($producto['categoria']); ?>">
        </div>

        <div class="form-group">
            <label for="precio_compra">Precio de Compra</label>
            <input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra" value="<?= $producto['precio_compra']; ?>" required>
        </div>

        <div class="form-group">
            <label for="precio_venta">Precio de Venta</label>
            <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" value="<?= $producto['precio_venta']; ?>" required>
        </div>

        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" class="form-control" id="stock" name="stock" value="<?= $producto['stock']; ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Guardar Cambios</button>
        <a href="productos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
