<?php
session_start();
include_once "../controller/insert_products.php";
include_once "../controller/edit_product.php";
include_once "../controller/delete_product.php";
include_once "../models/functions.php";
$show = show_state("products");
$brandData = show_state("brands");
$categoryData = show_state("categorys");
if (isset($_SESSION["id_rol"]) && ($_SESSION["id_rol"] == 1 || $_SESSION["id_rol"] == 2)) {
} else {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agencia UNO</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    </link>
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    </link>
    <link rel="stylesheet" href="../assets/css/style_lista_cliente.css">
    </link>
    <link rel="stylesheet" href="../assets/css/bootstrap.min5.3.css">
    </link>
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap5.css">
    </Link>
    <link rel="stylesheet" href="../assets/css/searchPanes.bootstrap5.css">
    </Link>
    <link rel="stylesheet" href="../assets/css/select.bootstrap5.css">
    </Link>
    <!-- Theme style -->
    <script src="../assets/js/sweetalert2@11.js"></script>
</head>

<body class="sidebar-mini" style="height: auto;">
    <!-- Site wrapper -->
    <div class="wrapper">
        <!-- HEADER -->
        <?php include "header.php" ?>
        <!-- MENU -->
        <?php include "menu.php" ?>
        <div class="content-wrapper" style="min-height: 1604.8px;">
            <main>
                <div class="container-fluid" style="padding:50px;">
                    <div class="card" style="margin-top:5px">
                        <div class="card-header">
                            <div class="row mb-12">
                                <div class="col-sm-6">
                                    <h4><b>Listado de Productos&nbsp&nbsp</b>
                                        <?php if (isset($_SESSION["id_rol"])) {
                                            if ($_SESSION["id_rol"] === 1) { ?>
                                                <button type="button" class="btn btn-success create_products_Btn"
                                                    data-toggle="modal" data-target="#create_products_Bt" data-action="add"
                                                    data-placement="right" title="Nuevo"><i
                                                        class="fas fa-plus-circle fa-lg"></i></button>
                                        <?php }
                                        } ?>
                                        <button type="button" class="btn btn-secondary landing_products_Btn" onclick="window.location.href='landing_product.php';">
                                            <i class="fas fa-image fa-lg"></i> Administrar Landing
                                        </button>
                                    </h4>
                                </div><!-- /.col -->
                                <div class="col-sm-6" id="botones" style="text-align: center;">
                                    <!-- Aquí se colocarán los botones de exportación de DataTables -->
                                </div>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.row -->
                    <div class="table-responsive">
                        <div class="table-wrapper">
                            <table id="table_products" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Número De Producto</th>
                                        <th>Nombre Producto</th>
                                        <th>Descripción</th>
                                        <th>Stock</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($show as $row) { ?>
                                        <tr>
                                            <td><?php echo $row->number_product; ?></td>
                                            <td><?php echo $row->name_product; ?></td>
                                            <td><?php echo $row->description; ?></td>
                                            <td><?php echo $row->stock; ?></td>
                                            <td>
                                                <a class="btn btn-success viewBtn long_letter text-white"
                                                    data-id_product="<?php echo $row->id_product ?>"
                                                    data-number_serial="<?php echo $row->number_serial ?>"
                                                    data-number_product="<?php echo $row->number_product ?>"
                                                    data-name_product="<?php echo $row->name_product ?>"
                                                    data-description="<?php echo $row->description ?>"
                                                    data-stock="<?php echo $row->stock ?>"><i
                                                        class="fa fa-binoculars"></i></a>
                                                <?php if (isset($_SESSION["id_rol"])) {
                                                    if ($_SESSION["id_rol"] === 1) { ?>
                                                        <a class="btn btn-warning editBtn long_letter text-white"
                                                            data-id_product="<?php echo $row->id_product ?>"
                                                            data-number_serial="<?php echo $row->number_serial ?>"
                                                            data-number_product="<?php echo $row->number_product ?>"
                                                            data-name_product="<?php echo $row->name_product ?>"
                                                            data-description="<?php echo $row->description ?>"
                                                            data-stock="<?php echo $row->stock ?>"><i class="fas fa-edit"></i></a>
                                                        <a class="btn btn-danger delete_Btn text-white long_letter"
                                                            data-id_products="<?php echo $row->id_product ?>"><i
                                                                class="fas fa-trash-alt"></i></a>
                                                <?php }
                                                } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?php include "footer.php" ?>
    </div>
    <!-- Modal para Crear Productos-->
    <div id="create_Modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title text-white">Crear Producto</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="../controller/insert_products.php" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name_product">Nombre</label>
                            <input type="text" name="name_product" class="form-control" required maxlength="200">
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="name_product">Número de Producto</label>
                            <input type="text" name="number_product" class="form-control" min-height="1"
                                maxlength="200">
                        </div>

                        <div class="form-group">
                            <label for="brand">Marca</label>
                            <select name="id_brand" id="brand" class="form-select" required>
                                <option option value="" selected disabled>-- Seleccione Marca --</option>
                                <?php foreach ($brandData as $brand) { ?>
                                    <option value="<?php echo $brand->id_brand; ?>">
                                        <?php echo $brand->detail; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="category">Categoría</label>
                            <select name="id_category" id="category" class="form-select" required>
                                <option option value="" selected disabled>-- Seleccione Categoría --</option>
                                <?php foreach ($categoryData as $category) { ?>
                                    <option value="<?php echo $category->id_category; ?>">
                                        <?php echo $category->detail; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="enviar" class="btn btn-success">Guardar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--Modal de Editar Productos-->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title text-white">Editar Producto</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"
                        style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="../controller/edit_product.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id_product" id="id_product" class="form-control" value="">

                        <div class="form-group">
                            <label for="edit_name">Nombre</label>
                            <input type="text" class="form-control" id="name_product" name="name_product" maxlength="200"
                                required value="">
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Descripción</label>
                            <input type="text" class="form-control" id="description" name="description" value="">
                        </div>

                        <div class="form-group">
                            <label for="edit_name">Número de producto</label>
                            <input type="text" class="form-control" id="number_product" name="number_product"
                                minlength="2" maxlength="200" value="">
                        </div>

                    </div>
                    <div class="modal-footer">

                        <button type="submit" name="save_data" class="btn btn-success">Guardar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div> <!-- Fin Modal para Editar Productos-->

    <!--Modal de Ver Productos-->
    <div id="viewModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title text-white">Detalles Producto</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <input type="hidden" name="id_product" id="id_product" class="form-control" value="">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="view_name_product">Nombre</label>
                                <input type="text" class="form-control" id="view_name_product" name="view_name_product"
                                    readonly value="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="view_description">Descripción</label>
                                <input type="text" class="form-control" id="view_description" name="view_description"
                                    value="" readonly>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="view_number_product">Número de producto</label>
                                    <input type="text" class="form-control" id="view_number_product" readonly
                                        name="view_number_product" value="">
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="view_stock">Stock</label>
                            <input type="number" class="form-control" id="view_stock" name="view_stock" value=""
                                readonly>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Volver</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
    <!-- Fin Modal para Ver Productos-->

    <!-- Eliminar Producto -->
    <div id="deleteModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-centered" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title text-white">Deshabilitar Producto</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="color: white"
                        ;>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="../controller/delete_product.php" method="post">
                    <div class="modal-body">
                        <br>
                        <h5 style="text-align:center">Estas seguro que desea Deshabilitar el Producto</h5>
                        <br>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                            </div>
                            <div class="form-group col-md-6" style="text-align:center">
                                <input type="hidden" name="id_product" id="id_product_eliminate"
                                    value="<?php echo $row->id_product ?>">
                            </div>
                        </div>
                        <!-- number_product_eliminate-->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Volver</button>
                        <button type="submit" name="delete" class="btn btn-danger">Deshabilitar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Verifica si hay un mensaje en el almacenamiento local
        if (localStorage.getItem('mensaje') && localStorage.getItem('tipo')) {
            Swal.fire({
                title: 'Mensaje',
                text: localStorage.getItem('mensaje'),
                icon: localStorage.getItem('tipo'),
                confirmButtonText: 'Aceptar'
            });

            // Limpia el mensaje después de mostrarlo
            localStorage.removeItem('mensaje');
            localStorage.removeItem('tipo');
        }
    </script>
    <!-- jQuery -->
    <script src="../assets/plugins/jquery/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.bumdle-v5.3.js"></script>
    <!-- AdminLTE App -->
    <script src="../assets/dist/js/adminlte.min.js"></script>
    <!-- Modal Products JS -->
    <script src="../assets/js/modal_products.js"></script>
    <!-- DataTables JS -->
    <script src="../assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/js/datatables.bootstrap5.min.js"></script>
    <script src="../assets/js/dataTables.js"></script>
    <script src="../assets/js/dataTables.bootstrap5.js"></script>
    <script src="../assets/js/dataTables.searchPanes.js"></script>
    <script src="../assets/js/searchPanes.bootstrap5.js"></script>
    <script src="../assets/js/dataTables.select.js"></script>
    <script src="../assets/js/select.bootstrap5.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="../assets/js/datatable.buttons2.1.1.js"></script>
    <script src="../assets/js/buttons.bootstrap5.min.js"></script>
    <script src="../assets/plugins/jszip/jszip.min.js"></script>
    <script src="../assets/plugins/pdfmake/pdfmakev0.1.js"></script>
    <script src="../assets/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="../assets/js/buttons.html5.min.js"></script>
    <script src="../assets/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#table_products').DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50, 75, 100], // Opciones de cantidad de registros a mostrar
                language: {
                    url: "../assets/lang/spanish.json",
                },
                columns: [{
                        width: '20%'
                    },
                    {
                        width: '25%'
                    },
                    {
                        width: '25%'
                    },
                    {
                        width: '10%'
                    },
                    {
                        width: '20%'
                    }
                ],
                dom: '<"top"lf><"table-responsive"t><"bottom"ip>',
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-success',
                        text: '<i class="far fa-file-excel"></i> Excel',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluye la última columna (Acciones)
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row c[r^="A"]', sheet).each(function() {
                                $(this).attr('s', '2'); // Agrega estilo a la primera columna
                            });
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-danger',
                        text: '<i class="far fa-file-pdf"></i> PDF',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluye la última columna (Acciones)
                        },

                        pageSize: 'A4',
                        customize: function(doc) {


                            doc.content[1].table.widths = ['25%', '25%', '25%', '25%']; // Ajusta los anchos de columna
                        }
                    }
                ],
                initComplete: function() {
                    // Mover los botones al contenedor personalizado
                    table.buttons().container().appendTo('#botones');
                }
            });
        });
    </script>

</body>

</html>