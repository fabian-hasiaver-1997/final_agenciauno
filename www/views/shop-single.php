<?php
// Incluir el archivo functions.php donde está la función get_all_products2
include_once "../models/functions.php";
// Obtener todos los productos usando la función get_all_products2
$productos = get_all_products2();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Agencia uno - Producto Detallado</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="../../assets/img/Logo_Agencia1.png">
    <link rel="shortcut icon" type="image/x-icon" href="../../assets/img/Logo_Agencia1.png">
    <link rel="stylesheet" href="../../assets/css/boostrap.min.css">
    <link rel="stylesheet" href="../../assets/css/templatemo.css">
    <link rel="stylesheet" href="../../assets/css/custom.css">
    <!-- Load fonts style after rendering the layout styles -->
    <link rel="stylesheet" href="../../assets/css/fontawesome.min.css">
    <!-- Slick -->
    <link rel="stylesheet" type="text/css" href="../../assets/css/slick.min.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/slick-theme.css">
</head>

<body>
    <!-- Start Top Nav -->
    <nav class="navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block" id="templatemo_nav_top">
        <div class="container text-light">
            <div class="w-100 d-flex justify-content-between">
                <div>
                    <i class="fa fa-envelope mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none"
                        href="mailto:estudiounomerlo@gmail.com">estudiounomerlo@gmail.com</a>
                    <i class="fa fa-phone mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none" href="tel:02204836292">0220-4836292</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Close Top Nav -->

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <div class="container d-flex justify-content-between ">
            <a href="../index.html" class="brand-link bg-white text-decoration-none">
                <h3 class="h3"><b>Agencia</b>UNO</h3>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#templatemo_main_nav" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="align-self-center collapse navbar-collapse flex-fill  d-lg-flex justify-content-lg-between"
                id="templatemo_main_nav">
                <div class="flex-fill">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.html">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.html">Nosotros</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="shop-single.php">Productos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.html">Contáctanos</a>
                        </li>
                    </ul>
                </div>
                <div class="navbar align-self-center d-flex">
                    <div class="d-lg-none flex-sm-fill mt-3 mb-4 col-7 col-sm-auto pr-3">
                    </div>
                    <a class="nav-icon position-relative text-decoration-none" href="login.php" title="Ingresar">
                        <i class="fa fa-fw fa-lock text-dark mr-3"></i>
                        <span
                            class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark"></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Close Header -->

    <!-- Open Content -->
    <section class="bg-light">
        <div class="container pb-5">
            <div class="row">
                <div class="col-12">
                    <div class="slick-carousel">
                        <?php $productos = get_all_landing(); ?>
                        <?php foreach ($productos as $producto): ?>
                            <div>
                                <div class="card mx-2">
                                    <!-- Mostrar imagen del producto -->
                                    <img class="card-img-top img-fluid" src="<?php echo $producto['image_url']; ?>" alt="<?php echo $producto['name_product']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $producto['name_product']; ?></h5>
                                        <p class="card-text"><?php echo $producto['description']; ?></p>
                                        <ul class="list-unstyled">
                                            <li><strong>Precio:</strong> $<?php echo $producto['price']; ?></li>
                                            <li><strong>Stock disponible:</strong> <?php echo $producto['stock']; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--end-->

    <!-- Start Footer -->
    <footer class="bg-dark" id="tempaltemo_footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-success border-bottom pb-3 border-light logo">Agencia Uno</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li>
                            <i class="fas fa-map-marker-alt fa-fw"></i>
                            Calle 123, Merlo
                        </li>
                        <li>
                            <i class="fa fa-phone fa-fw"></i>
                            <a class="text-decoration-none" href="tel:0220-4836292">0220-4836292</a>
                        </li>
                        <li>
                            <i class="fa fa-envelope fa-fw"></i>
                            <a class="text-decoration-none"
                                href="mailto:estudiounomerlo@gmail.com">estudiounomerlo@gmail.com</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 pt-5">
                    <a class="text-decoration-none" href="shop-single.php">
                        <h2 class="h2 text-light border-bottom pb-3 border-light">Productos</h2>
                    </a>
                </div>
                <div class="col-md-4 pt-5">

                    <h2 class="h2 text-light border-bottom pb-3 border-light">Información adicional</h2>

                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="../index.html">Inicio</a></li>
                        <li><a class="text-decoration-none" href="../views/about.html">Sobre nosotros</a></li>
                        <li><a class="text-decoration-none" href="../views/contact.html">Contacto</a></li>
                    </ul>
                </div>
            </div>
            <div class="row text-light mb-4">
                <div class="col-12 mb-3">
                    <div class="w-100 my-3 border-top border-light"></div>
                </div>
            </div>
        </div>
        </div>
        <div class="w-100 bg-black py-3">
            <div class="container">
                <div class="row pt-2">
                    <div class="col-12">
                        <p class="text-left text-light">
                            Copyright &copy; 2024 Agencia Uno
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <button id="scrollToTopButton" class="scroll-to-top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Start Script -->
    <script src="../assets/js/jquery-1.11.0.min.js"></script>
    <script src="../assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/templatemo.js"></script>
    <script src="../assets/js/custom.js"></script>
    <link rel="stylesheet" type="text/css" href="../../assets/css/slick.min.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/slick-theme.css">
    <script src="../../assets/js/slick.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.slick-carousel').slick({
                infinite: true, // Hace que el carrusel sea infinito
                slidesToShow: 3, // Cantidad de cartas visibles al mismo tiempo
                slidesToScroll: 1, // Cuántas cartas avanza por vez
                dots: true, // Muestra los puntos de navegación
                arrows: true, // Muestra las flechas de navegación
                responsive: [{
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        });
    </script>
</body>

</html>