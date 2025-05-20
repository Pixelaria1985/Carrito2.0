<?php
include('conexion.php');

// Eliminar producto si se recibe una solicitud GET con ?eliminar=ID
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    // Verificar si el producto tiene ventas asociadas
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM ventas WHERE producto_id = ?");
    $stmt->execute([$id]);
    $ventas_asociadas = $stmt->fetchColumn();

    if ($ventas_asociadas > 0) {
        // Si tiene ventas asociadas, mostrar una alerta
        echo "<script type='text/javascript'>
                alert('No se puede eliminar el producto porque tiene ventas asociadas.');
                window.location.href = 'productos.php';  // Redirigir a la página de productos
              </script>";
    } else {
        // Si no tiene ventas asociadas, proceder con la eliminación del producto
        try {
            // Eliminar el producto
            $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            echo "<script type='text/javascript'>
                    alert('Producto eliminado correctamente.');
                    window.location.href = 'productos.php';  // Redirigir a la página de productos
                  </script>";
        } catch (Exception $e) {
            echo "<script type='text/javascript'>
                    alert('Error al eliminar el producto: " . $e->getMessage() . "');
                    window.location.href = 'productos.php';  // Redirigir a la página de productos
                  </script>";
        }
    }
}

// Obtener todos los productos
$stmt = $conexion->prepare("SELECT * FROM productos ORDER BY id DESC");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        td, th {
            vertical-align: middle !important;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Productos Registrados</h2>
        <a href="agregar_producto.php" class="btn btn-primary mb-3">Agregar Nuevo Producto</a>
        <a href="index.php" class="btn btn-secondary mb-3">Volver al Inicio</a>

        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Precio Compra</th>
                    <th>Precio Venta</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($productos) > 0): ?>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= $producto['id']; ?></td>
                            <td><?= $producto['nombre']; ?></td>
                            <td><?= $producto['descripcion']; ?></td>
                            <td><?= $producto['categoria']; ?></td>
                            <td>$<?= number_format($producto['precio_compra'], 2); ?></td>
                            <td>$<?= number_format($producto['precio_venta'], 2); ?></td>
                            <td><?= $producto['stock']; ?></td>
                            <td>
                                <a href="editar_producto.php?id=<?= $producto['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a href="productos.php?eliminar=<?= $producto['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que quieres eliminar este producto?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No hay productos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
