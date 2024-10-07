$(document).ready(function() {
    // Asignar evento al botón de detalles dentro de la tabla
    $('#salesTable').on('click', '.btn-info', function() {
        const id_sale = $(this).data('id-sale'); // Obtener sale_number del botón
        loadHistoryDetails(id_sale); // Llamar a la función para cargar los detalles
    });
});
// Función para cargar los detalles de la venta
function loadHistoryDetails(sale_number) {
    $.ajax({
        url: '../controller/get_sales_history.php',  // Ruta del controlador (actualizada)
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ sale_number: sale_number }),  // Enviar el sale_number
        dataType: 'json',
        success: function(data) {
            if (data && data.products && data.products.length > 0) {
                fillSaleDetailsModal(data);  // Llenar el modal con los datos de productos
                
                // Crear y mostrar el modal con opciones que permiten cerrar con backdrop y teclado
                const saleModal = new bootstrap.Modal(document.getElementById("productHistoryModal"), {
                    backdrop: true,  // Permitir cierre al hacer click fuera
                    keyboard: true   // Permitir cierre con tecla escape
                });
                saleModal.show();  // Mostrar el modal
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'No se encontraron productos para esta venta.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud AJAX:', xhr.responseText || error);
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al obtener los detalles de la venta.',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

// Función para llenar el modal con los detalles de la venta
function fillSaleDetailsModal(data) {
    const modalBody = document.querySelector("#HistoryDetailsContent");
    const modalHeader = document.querySelector("#productHistoryModalLabel");
    
    // Actualizar el título del modal con el sale_number
    modalHeader.textContent = `Detalles de Venta - Venta N° ${data.sale_number}`; // Eliminar sale_date

    modalBody.innerHTML = '';  // Limpiar el contenido previo

    const table = document.createElement('table');
    table.className = 'table table-bordered';

    // Crear la cabecera de la tabla
    const thead = document.createElement('thead');
    thead.innerHTML = `
        <tr>
            <th>Nombre Producto</th>
            <th>Cantidad</th>
        </tr>
    `;
    table.appendChild(thead);

    const tbody = document.createElement('tbody');

    // Rellenar la tabla con los datos de los productos
    data.products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.name_product}</td>
            <td>${product.quantity}</td> 
        `;
        tbody.appendChild(row);
    });

    table.appendChild(tbody);
    modalBody.appendChild(table);  // Añadir la tabla al cuerpo del modal
}