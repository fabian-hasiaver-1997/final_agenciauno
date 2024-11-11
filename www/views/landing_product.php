<?php
session_start();
include_once "../models/functions.php";

// Verificar si el usuario tiene permisos
$show = show_state("brands");
if (isset($_SESSION["id_rol"]) && ($_SESSION["id_rol"] == 1 || $_SESSION["id_rol"] == 2)) {
} else {
    header("Location: login.php");
    exit();
}

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadSuccess = true;
    for ($i = 0; $i < 3; $i++) {
        if (isset($_FILES["product_image_$i"]) && $_FILES["product_image_$i"]['name'] != "") {
            $description = $_POST["product_description_$i"];
            $price = $_POST["price_$i"];

            // Validar que el precio no sea negativo
            if ($price < 0) {
                $uploadSuccess = false;
                $message = "El precio no puede ser negativo.";
                break;
            }

            // Validar que la imagen sea JPG, PNG o JPEG
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $imageExtension = strtolower(pathinfo($_FILES["product_image_$i"]['name'], PATHINFO_EXTENSION));
            if (!in_array($imageExtension, $allowedExtensions)) {
                $uploadSuccess = false;
                $message = "La imagen debe ser en formato JPG, JPEG o PNG.";
                break;
            }

            // Obtener el stock de la tabla products
            $productId = $_POST["product_id_$i"];
            $stockQuery = "SELECT stock FROM products WHERE id = ?";
            $stockStmt = $db->prepare($stockQuery);
            $stockStmt->execute([$productId]);
            $stock = $stockStmt->fetchColumn();

            $targetDir = "../assets/img";
            $imageName = basename($_FILES["product_image_$i"]["name"]);
            $targetFilePath = $targetDir . "/" . $imageName;
            $imageUrl = "uploads/products/" . $imageName;

            if (move_uploaded_file($_FILES["product_image_$i"]["tmp_name"], $targetFilePath)) {
                // Redimensionar la imagen a 300x300 para que todas tengan el mismo tamaño
                list($width, $height) = getimagesize($targetFilePath);
                $newWidth = 300;
                $newHeight = 300;
                $src = imagecreatefromstring(file_get_contents($targetFilePath));
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($dst, $targetFilePath, 90);

                // Guardar los datos en la tabla landing_product
                $insertQuery = "INSERT INTO landing_product (image_url, description, price) VALUES (?, ?, ?)";
                $stmt = $db->prepare($insertQuery);
                $stmt->execute([$imageUrl, $description, $price]);
            } else {
                $uploadSuccess = false;
            }
        }
    }

    $message = $uploadSuccess ? "Productos agregados con éxito." : "Error al subir una o más imágenes. Intente nuevamente.";

    // Redireccionar a single-shop.php después de la carga exitosa
    if ($uploadSuccess) {
        header("Location: single-shop.php");
        exit();
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
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid mt-3">
                    <h2>Add New Products</h2>
                    <?php if (isset($message)): ?>
                        <div class="alert alert-info"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data" oninput="updatePreviews()">
                        <div class="row">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <div class="col-md-4">
                                    <div class="product-entry border p-3 mb-4">
                                        <h4>Product <?php echo $i + 1; ?></h4>
                                        <div class="form-group">
                                            <label for="product_image_<?php echo $i; ?>">Product Image</label>
                                            <input type="file" class="form-control" id="product_image_<?php echo $i; ?>" name="product_image_<?php echo $i; ?>" accept="image/*" onchange="showPreview(event, <?php echo $i; ?>)" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="product_description_<?php echo $i; ?>">Product Description</label>
                                            <input type="text" class="form-control" id="product_description_<?php echo $i; ?>" name="product_description_<?php echo $i; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="price_<?php echo $i; ?>">Price</label>
                                            <input type="number" class="form-control" id="price_<?php echo $i; ?>" name="price_<?php echo $i; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="product_id_<?php echo $i; ?>">Product ID</label>
                                            <input type="number" class="form-control" id="product_id_<?php echo $i; ?>" name="product_id_<?php echo $i; ?>" required>
                                        </div>
                                    </div>

                                    <h5>Preview for Product <?php echo $i + 1; ?></h5>
                                    <div class="card mb-4" style="width: 100%;">
                                        <img id="previewImage_<?php echo $i; ?>" class="card-img-top" alt="Image Preview" style="width: 100%; height: 300px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 id="previewDescription_<?php echo $i; ?>" class="card-title">Product Description</h5>
                                            <p id="previewPrice_<?php echo $i; ?>" class="card-text">Price: $0.00</p>
                                            <p id="previewStock_<?php echo $i; ?>" class="card-text">Available Stock: 0</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </section>
        </div>

        <?php include "footer.php"; ?>
    </div>

    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/dist/js/adminlte.min.js"></script>
    <script>
        function showPreview(event, index) {
            if (event.target.files.length > 0) {
                const src = URL.createObjectURL(event.target.files[0]);
                document.getElementById(`previewImage_${index}`).src = src;
            }
        }

        function updatePreviews() {
            for (let i = 0; i < 3; i++) {
                document.getElementById(`previewDescription_${i}`).textContent = document.getElementById(`product_description_${i}`).value;
                document.getElementById(`previewPrice_${i}`).textContent = "Price: $" + document.getElementById(`price_${i}`).value;
                document.getElementById(`previewStock_${i}`).textContent = "Available Stock: " + document.getElementById(`product_id_${i}`).value;
            }
        }
    </script>
</body>

</html>