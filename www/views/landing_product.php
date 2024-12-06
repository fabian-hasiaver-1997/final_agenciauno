<?php
session_start();
include_once "../models/functions.php";
include_once "../controller/insert_landing_card.php";
include_once "../controller/edit_landing_card.php";
include_once "../controller/delete_landing_card.php";

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
    $product_id = $_POST['product_id']; // ID del producto desde el formulario
    $description = $_POST['description']; // Descripción del producto
    $price = $_POST['price'];  // Precio del producto

    // Verificar si se ha subido una imagen
    if (isset($_FILES['image'])) {
        // Llamar a la función para validar y subir la imagen
        $image_url = validate_and_upload_image($_FILES['image'], $product_id);

        // Verificar si la imagen se subió correctamente
        if (strpos($image_url, '.') !== false) {
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

        <div class="content-wrapper">
            <section class="content-header">
                <h2>Gestión de Tarjetas de Productos</h2>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Crear tarjeta para el producto</h5>
                                <br>
                                <?php
                                // Obtener todos los productos y tarjetas existentes
                                $products = get_all_products2();
                                $cards = get_all_landing(); // Obtener todas las tarjetas existentes
                                if (empty($products)) {
                                    echo '<p>No hay productos disponibles.</p>';
                                } else {
                                ?>
                                    <form id="productForm" method="POST" enctype="multipart/form-data" action="../controller/insert_landing_card.php">
                                        <input type="hidden" name="card_id" id="card_id"> <!-- Campo oculto para ID de la tarjeta -->
                                        <input type="hidden" name="current_image" id="current_image"> <!-- Imagen actual -->
                                        <form id="productForm" method="POST" enctype="multipart/form-data" action="../controller/insert_landing_card.php">
                                            <input type="hidden" name="id_landing" id="id_landing"> <!-- Campo oculto para ID de la tarjeta -->
                                            <input type="hidden" name="current_image" id="current_image"> <!-- Imagen actual -->

                                            <div class="form-group">
                                                <label for="product_id">Seleccionar Producto:</label>
                                                <select name="product_id" id="product_id" class="form-control" required>
                                                    <option value="">Seleccione un producto</option>
                                                    <?php foreach ($products as $product) { ?>
                                                        <option value="<?php echo $product['id_product']; ?>">
                                                            <?php echo $product['name_product']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="price">Precio:</label>
                                                <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="image">Imagen:</label>
                                                <input type="file" name="image" id="image" class="form-control">
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" name="insert_landing" class="btn btn-primary">Crear carta</button>
                                            </div>
                                        </form>
                                    <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listado de tarjetas existentes -->
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <h3 class="text-center">Tarjetas Creadas</h3>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cards)) { ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No hay tarjetas creadas.</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php foreach ($cards as $card) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($card['name_product']); ?></td>
                                            <td><img src="<?php echo $card['image_url']; ?>" alt="Imagen" width="100"></td>
                                            <td><?php echo htmlspecialchars($card['description']); ?></td>
                                            <td><?php echo number_format($card['price'], 2); ?></td>
                                            <td>
                                                <a href="#editUserModal<?php echo $card['id_landing']; ?>" class="btn btn-secondary edit-user btn-sm" data-toggle="modal" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal_<?php echo $card['id_landing'] ?>" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>

                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <?php foreach ($cards as $card) { ?>
            <div class="modal fade cierreModal" id="editUserModal<?php echo $card['id_landing']; ?>" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header alert alert-primary">
                            <h5 class="modal-title" id="editUserModalLabel"><strong>Editar Tarjeta</strong></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="editCard" action="../controller/edit_landing_card.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id_card" value="<?php echo $card['id_landing']; ?>">
                                <input type="hidden" name="current_image" value="<?php echo $card['image_url']; ?>">
                                <input type="hidden" name="current_product" value="<?php echo $card['product_id']; ?>">

                                <div class="form-group">
                                    <label for="price">Precio</label>
                                    <input type="text" maxlength="50" class="form-control" id="price" name="price" value="<?php echo $card['price']; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="image">Imagen actual:</label>
                                    <div class="mb-2">
                                        <img src="<?php echo $card['image_url']; ?>" alt="Imagen actual" width="150" height="100">
                                    </div>
                                    <label for="image">Seleccionar nueva imagen:</label>
                                    <input type="file" name="image" id="image" class="form-control">
                                </div>

                                <button type="submit" name="update_landing" class="btn btn-primary">Guardar cambios</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php foreach ($cards as $card): ?>
            <div class="modal fade cierreModal" id="confirmDeleteModal_<?php echo $card['id_landing'] ?>" tabindex="-1"
                role="dialog" aria-labelledby="confirmDeleteModalLabel_<?php echo $card['id_landing'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="confirmDeleteModalLabel_<?php echo $card['id_landing'] ?>">Confirmar
                                Eliminación</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <p>¿Estás seguro de que deseas eliminar la siguiente materia?</p>
                            <h5 class="mt-4 mb-4 font-weight-bold"><?php echo $card['name_product'] ?></h5>
                            <p class="alert alert-danger">Seugro que desea eliminar esto, se eliminara de la pagina principal</p>
                            <p>Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <form method="post" action="../controller/delete_landing_card.php">
                                <input type="hidden" name="id_landing" value="<?php echo $card['id_landing'] ?>">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger" name="delete_landing">Eliminar</button>
                            </form>
                            <div class="response-message text-center"></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>




        <!-- Script para cargar datos de la tarjeta en el formulario -->
        <script>
            function editCard(cardId) {
                // Cargar datos de la tarjeta seleccionada
                const cards = <?php echo json_encode($cards); ?>;
                const card = cards.find(c => c.id === cardId);

                if (card) {
                    document.getElementById('card_id').value = card.id;
                    document.getElementById('price').value = card.price;
                    alert('Cargue una nueva imagen si desea modificarla.');
                }
            }
        </script>

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