<?php

include_once "../models/functions.php";

if (isset($_POST['update_landing'])) {
    $id_landing = $_POST['id_card'] ?? null;
    $price = $_POST['price'] ?? null;
    $file = $_FILES['image'] ?? null;
    $current_image = $_POST['current_image'] ?? null;

    if (empty($id_landing)) {
        die("Error: ID de tarjeta no encontrado.");
    }

    // Usar la imagen actual si no se sube una nueva
    $image_url = !empty($file['name']) ? validate_and_upload_image($file, $id_landing) : $current_image;

    // Actualizar en la base de datos
    $update = update_card_data($id_landing, $image_url, $price);

    if ($update) {
        echo '<script>
            localStorage.setItem("mensaje", "Tarjeta actualizada con éxito");
            localStorage.setItem("tipo", "success");
            window.location.href = "../views/landing_product.php";
        </script>';
    } else {
        echo '<script>
            localStorage.setItem("mensaje", "Error al actualizar la tarjeta");
            localStorage.setItem("tipo", "error");
            window.location.href = "../views/landing_product.php";
        </script>';
    }
}
