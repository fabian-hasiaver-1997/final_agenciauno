<?php
include_once "../models/functions.php";

// Para eliminar tarjetas
if (isset($_POST['delete_landing'])) {
    $id_landing = $_POST['id_landing']; // ID de la tarjeta a eliminar


    // Eliminar tarjeta de la base de datos
    $delete = delete_card_data($id_landing);

    if ($delete) {
        echo '<script>
    localStorage.setItem("mensaje", "Producto eliminado con éxito");
    localStorage.setItem("tipo", "success");
    window.location.href = "../views/landing_product.php";
</script>';
    } else {
        echo '<script>
    localStorage.setItem("mensaje", "Error al eliminar el producto");
    localStorage.setItem("tipo", "error");
    window.location.href = "../views/landing_product.php";
</script>';
    }
}
