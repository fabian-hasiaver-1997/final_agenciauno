<?php
include_once "../models/functions.php";
if (isset($_POST['insert_landing'])) {

    $product_id = $_POST['product_id']; // ID del producto
    $price = $_POST['price']; // Precio del producto
    $file = $_FILES['image']; // Archivo de imagen

    // Llamar a la función para validar y subir la imagen
    $image_url = validate_and_upload_image($file, $product_id);

    // Si la imagen se subió correctamente (retorna la ruta del archivo)
    if (strpos($image_url, '../assets/img/') !== false) {
        // Llamar a la función para guardar los datos del producto en la base de datos
        $insert = save_card_data($product_id, $image_url, $price);

        if ($insert) {
            echo '<script>
    localStorage.setItem("mensaje", "Producto creado con éxito");
    localStorage.setItem("tipo", "success");
    window.location.href = "../views/landing_product.php";
</script>';
        } else {
            echo '<script>
    localStorage.setItem("mensaje", "Error al crear el producto");
    localStorage.setItem("tipo", "error");
    window.location.href = "../views/landing_product.php";
</script>';
        }
    } else {
        echo '<script>
    localStorage.setItem("mensaje", "' . $image_url . '");
    localStorage.setItem("tipo", "error");
    window.location.href = "../views/landing_product.php";
</script>';
    }
}
