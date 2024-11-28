<?php
session_start();
include_once "../models/functions.php";
include_once "../controller/insert_landing_card.php";

// Verificar si el usuario tiene permisos
$show = show_state("brands");
if (isset($_SESSION["id_rol"]) && ($_SESSION["id_rol"] == 1 || $_SESSION["id_rol"] == 2)) {
} else {
    header("Location: login.php");
    exit();
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $product_id = $_POST['product_id' . $i];  // ID del producto desde el formulario
    $description = $_POST['description' . $i]; // Descripción del producto
    $price = $_POST['price' . $i];  // Precio del producto

    // Verificar si se ha subido una imagen
    if (isset($_FILES['image' . $i])) {
        // Llamar a la función para validar y subir la imagen
        $image_url = validate_and_upload_image($_FILES['image' . $i], $product_id);

        // Verificar si la imagen se subió correctamente
        if (strpos($image_url, '.') !== false) {
            // Obtener los datos del producto desde la tabla `products`
            // $product = get_product_by_id($product_id);
            $product_description = $product['description'];
            $product_price = $product['price'];
            $product_stock = $product['stock'];

            // Si la imagen se subió correctamente, guardar los datos en la base de datos
            if (save_card_data($product_id, $description, $price, $image_url)) {
                // Redirigir a shop-single.php con el ID del producto recién creado
                header("Location: shop-single.php?product_id=" . $product_id);
                exit; // Asegurarse de que no se siga ejecutando el código después de la redirección
            } else {
                echo "Error al guardar los datos del producto.";  // Error al guardar
            }
        } else {
            echo $image_url;  // Mostrar el error de subida de imagen
        }
    } else {
        echo "No se ha subido ninguna imagen.";  // Error si no se sube imagen
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agencia UNO</title>
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
</head>

<body class="sidebar-mini layout-fixed" style="height: auto;">
    <div class="wrapper">
        <?php include "header.php"; ?>
        <?php include "menu.php"; ?>

        <!-- Contenido principal con container-fluid para ajustar ancho -->
        <!-- Contenido principal con container-fluid para ajustar ancho -->
        <div class="content-wrapper">
            <section class="content-header">
                <h2>Agregar Producto</h2>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Producto</h5>
                                <br>
                                <?php
                                // Obtener todos los productos de la base de datos
                                $products = get_all_products2();
                                // Si no hay productos, mostrar un mensaje
                                if (empty($products)) {
                                    echo '<p>No hay productos disponibles.</p>';
                                } else {
                                    // Solo cargamos un producto por vez (el primero)
                                    $product = $products[0];  // Cambia esto para mostrar el producto seleccionado dinámicamente si es necesario
                                ?>
                                    <form method="POST" enctype="multipart/form-data" action="../controller/insert_landing_card.php">
                                        <div class="form-group">
                                            <label for="product_id">Seleccionar Producto:</label>
                                            <select name="product_id" id="product_id" class="form-control" onchange="updateDescription()" required>
                                                <option value="">Seleccione un producto</option>
                                                <?php foreach ($products as $product) { ?>
                                                    <option value="<?php echo $product['id_product']; ?>" data-description="<?php echo htmlspecialchars($product['description']); ?>">
                                                        <?php echo $product['name_product']; ?> - Stock: <?php echo $product['stock']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>

                                        </div>
                                        <div class="form-group">
                                            <label for="description">Descripción:</label>
                                            <textarea name="description" id="description" class="form-control" readonly></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="price">Precio:</label>
                                            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="image">Imagen:</label>
                                            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg, image/png" required>
                                        </div>
                                        <div class="text-center">
                                            <button name="insert_landing" class="btn btn-primary">Guardar Producto</button>
                                        </div>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <script>
            function updateDescription() {
                const select = document.getElementById('product_id');
                const textarea = document.getElementById('description');
                const selectedOption = select.options[select.selectedIndex];
                const description = selectedOption.getAttribute('data-description') || '';
                textarea.value = description;

                // Depura el value del product_id
                console.log("Product ID seleccionado:", select.value);
            }
        </script>

        <?php include "footer.php"; ?>
        <script src="../assets/plugins/jquery/jquery.min.js"></script>
        <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/dist/js/adminlte.min.js"></script>
</body>

</html>