<?php
include('conexion.php');

// Actualizar descuentos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['descuentos'] as $metodo => $valor) {
        $valor = abs(floatval($valor)); // Asegura que sea positivo
        $stmt = $conexion->prepare("UPDATE descuentos SET porcentaje_descuento = ? WHERE metodo_pago = ?");
        $stmt->execute([$valor, $metodo]);
    }
    $mensaje = "Descuentos actualizados correctamente.";
}

// Obtener descuentos actuales
$descuentos = $conexion->query("SELECT * FROM descuentos")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurar Descuentos</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Configurar Descuentos por Método de Pago</h2>

    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success"><?= $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th>Descuento (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($descuentos as $d): ?>
                    <tr>
                        <td><?= $d['metodo_pago']; ?></td>
                        <td>
                            <input type="number" name="descuentos[<?= $d['metodo_pago']; ?>]" 
                                   value="<?= abs($d['porcentaje_descuento']); ?>" 
                                   min="0" max="100" step="0.01" class="form-control" required>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="index.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
