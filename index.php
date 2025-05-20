<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - Gestión de Inventario</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Sistema de Gestión de Inventario</h1>
        
        <div class="row">
            <!-- Registrar Compra -->
            <div class="col-md-4 mb-3">
                <a href="registrar_compra.php" class="btn btn-primary btn-block">Registrar Compra</a>
            </div>

            <!-- Registrar Venta -->
            <div class="col-md-4 mb-3">
                <a href="registrar_venta.php" class="btn btn-success btn-block">Registrar Venta</a>
            </div>

            <!-- Ver Productos -->
            <div class="col-md-4 mb-3">
                <a href="productos.php" class="btn btn-info btn-block">Ver Productos</a>
            </div>

            <!-- Historial de Compras -->
            <div class="col-md-4 mb-3">
                <a href="historial_compras.php" class="btn btn-warning btn-block">Historial de Compras</a>
            </div>

            <!-- Historial de Ventas -->
            <div class="col-md-4 mb-3">
                <a href="historial_ventas.php" class="btn btn-warning btn-block">Historial de Ventas</a>
            </div>

            <!-- Exportar Compras CSV -->
            <div class="col-md-4 mb-3">
                <a href="exportar_csv.php?tipo=compras" class="btn btn-outline-primary btn-block">Exportar Compras a CSV</a>
            </div>

            <!-- Exportar Ventas CSV -->
            <div class="col-md-4 mb-3">
                <a href="exportar_csv.php?tipo=ventas" class="btn btn-outline-success btn-block">Exportar Ventas a CSV</a>
            </div>

            <!-- Agregar producto -->
            <div class="col-md-4 mb-3">
            <a href="agregar_producto.php" class="btn btn-outline-dark btn-block">Agregar Producto</a>
            </div>

            
        </div>
    </div>
</body>
</html>
