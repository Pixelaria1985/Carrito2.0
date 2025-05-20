<?php
session_start();
include('conexion.php');

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito_venta'])) {
    $_SESSION['carrito_venta'] = [];
}

// Obtener métodos de pago y descuentos
$metodos = $conexion->query("SELECT * FROM descuentos")->fetchAll(PDO::FETCH_ASSOC);
$descuentos = [];
foreach ($metodos as $m) {
    $descuentos[$m['metodo_pago']] = $m['porcentaje_descuento'];
}

// Obtener método de pago seleccionado
$metodo_seleccionado = $_POST['metodo_pago'] ?? 'Efectivo';
$porcentaje_descuento = $descuentos[$metodo_seleccionado] ?? 0;

// Agregar producto por nombre
if (isset($_POST['agregar'])) {
    $nombre_producto = trim($_POST['nombre_producto']);

    $stmt = $conexion->prepare("SELECT * FROM productos WHERE nombre = ? AND stock > 0 LIMIT 1");
    $stmt->execute([$nombre_producto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        $_SESSION['carrito_venta'][] = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'precio_venta' => $producto['precio_venta'],
            'cantidad' => 1
        ];
    } else {
        $mensaje = "Producto no encontrado o sin stock.";
    }
}

// Eliminar producto del carrito
if (isset($_GET['eliminar'])) {
    $index = $_GET['eliminar'];
    unset($_SESSION['carrito_venta'][$index]);
    $_SESSION['carrito_venta'] = array_values($_SESSION['carrito_venta']);
    header("Location: registrar_venta.php");
    exit;
}

// Finalizar venta
if (isset($_POST['finalizar'])) {
    if (!empty($_SESSION['carrito_venta'])) {
        try {
            $conexion->beginTransaction();
            $metodo_pago = $_POST['metodo_pago'];

            foreach ($_SESSION['carrito_venta'] as $item) {
                // Insertar en ventas
                $stmt = $conexion->prepare("INSERT INTO ventas (producto_id, cantidad, fecha_venta, metodo_pago)
                                            VALUES (?, ?, NOW(), ?)");
                $stmt->execute([$item['id'], $item['cantidad'], $metodo_pago]);

                // Actualizar stock
                $stmt = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['cantidad'], $item['id']]);
            }

            $conexion->commit();
            $_SESSION['carrito_venta'] = [];
            $mensaje = "Venta registrada con método '$metodo_pago'.";
        } catch (Exception $e) {
            $conexion->rollBack();
            $mensaje = "Error al registrar la venta: " . $e->getMessage();
        }
    } else {
        $mensaje = "El carrito está vacío.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Venta</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Registrar Venta</h2>

    <?php if (isset($mensaje)): ?>
        <div class="alert alert-info"><?= $mensaje; ?></div>
    <?php endif; ?>

    <!-- Formulario para agregar producto al carrito -->
    <form method="POST" class="form-inline mb-3">
        <label class="mr-2">Nombre del Producto:</label>
        <input type="text" name="nombre_producto" class="form-control mr-2" required placeholder="Ej: Remera Roja">
        <button type="submit" name="agregar" class="btn btn-primary">Agregar</button>
    </form>

    <!-- Selección del método de pago -->
    <form method="POST">
        <div class="form-group">
            <label for="metodo_pago">Método de Pago:</label>
            <select name="metodo_pago" id="metodo_pago" class="form-control" onchange="this.form.submit()">
                <?php foreach ($descuentos as $metodo => $valor): ?>
                    <option value="<?= $metodo ?>" <?= ($metodo == $metodo_seleccionado) ? 'selected' : '' ?>>
                        <?= $metodo ?> (<?= $valor ?>% descuento)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <!-- Tabla del carrito -->
    <?php if (!empty($_SESSION['carrito_venta'])): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Venta</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['carrito_venta'] as $index => $item):
                    $subtotal = $item['precio_venta'] * $item['cantidad'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= $item['nombre']; ?></td>
                        <td>$<?= number_format($item['precio_venta'], 2); ?></td>
                        <td><?= $item['cantidad']; ?></td>
                        <td>$<?= number_format($subtotal, 2); ?></td>
                        <td><a href="?eliminar=<?= $index; ?>" class="btn btn-danger btn-sm">X</a></td>
                    </tr>
                <?php endforeach; ?>

                <?php
                    $descuento = $total * ($porcentaje_descuento / 100);
                    $total_final = $total - $descuento;
                ?>

                <tr>
                    <th colspan="3">Total sin descuento</th>
                    <th colspan="2">$<?= number_format($total, 2); ?></th>
                </tr>
                <tr>
                    <th colspan="3">Descuento (<?= $porcentaje_descuento ?>%)</th>
                    <th colspan="2">-$<?= number_format($descuento, 2); ?></th>
                </tr>
                <tr class="table-success">
                    <th colspan="3">Total con descuento</th>
                    <th colspan="2">$<?= number_format($total_final, 2); ?></th>
                </tr>
            </tbody>
        </table>

        <!-- Finalizar venta -->
        <form method="POST">
            <input type="hidden" name="metodo_pago" value="<?= $metodo_seleccionado ?>">
            <button type="submit" name="finalizar" class="btn btn-success">Finalizar Venta</button>
        </form>
    <?php else: ?>
        <p>No hay productos en el carrito de venta.</p>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
</div>
</body>
</html>
