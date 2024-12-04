<?php

include_once "../models/functions.php";

if (isset($_POST['update_landing'])) {
    $id_landing = $_POST['id_card'] ?? null;
    $product_id = $_POST['product_id'];  // Producto seleccionado

    $price = $_POST['price'] ?? null;
    $file = $_FILES['image'] ?? null;
    $current_image = $_POST['current_image'] ?? null;
    $current_product = $_POST['current_product'] ?? null;  // Valor actual del producto

    if (empty($id_landing)) {
        die("Error: ID de tarjeta no encontrado.");
    }

    // Si no se selecciona un nuevo producto, usar el ID actual
    if (empty($product_id)) {
        $product_id = $current_product;  // Usar el valor actual si no se selecciona otro
    }

    // Si $product_id sigue vacío, se asigna un valor por defecto (puedes poner un valor que haga sentido en tu lógica)
    if (empty($product_id)) {
        die("Error: Producto no encontrado.");
    }

    // Usar la imagen actual si no se sube una nueva
    $image_url = !empty($file['name']) ? validate_and_upload_image($file, $product_id) : $current_image;

    // Actualizar en la base de datos
    $update = update_card_data($id_landing, $product_id, $image_url, $price);

    if ($update) {
        echo '<script>
            localStorage.setItem("mensaje", "Producto actualizado con éxito");
            localStorage.setItem("tipo", "success");
            window.location.href = "../views/landing_product.php";
        </script>';
    } else {
        echo '<script>
            localStorage.setItem("mensaje", "Error al actualizar el producto");
            localStorage.setItem("tipo", "error");
            window.location.href = "../views/landing_product.php";
        </script>';
    }
}
