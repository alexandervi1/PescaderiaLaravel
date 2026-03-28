@extends('layouts.app')

@section('title', 'Ayuda y Contacto')

@section('content')
<div class="row my-5 justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-5">
            <h2 class="fw-bold display-6 mb-3">¿Cómo podemos ayudarte?</h2>
            <p class="lead text-muted">Encuentra respuestas a preguntas frecuentes o contáctanos directamente.</p>
        </div>

        <div class="accordion shadow-sm" id="accordionHelp">
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header">
                    <button class="accordion-button fs-5 fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        <i class="fas fa-question-circle text-info me-3"></i> ¿Cómo realizo un pedido?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionHelp">
                    <div class="accordion-body">
                        Para realizar un pedido debes crear una cuenta, iniciar sesión, navegar por el catálogo agregando productos a tu carrito de compras y finalmente ir al carrito y confirmar el pedido. Un administrador se pondrá en contacto para el pago y la entrega.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fs-5 fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                        <i class="fas fa-truck text-info me-3"></i> ¿Tienen servicio a domicilio?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionHelp">
                    <div class="accordion-body">
                        Sí, contamos con servicio a domicilio para la ciudad. Los costos dependen de la distancia y el monto de tu compra. Para pedidos grandes, el envío puede ser gratuito.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fs-5 fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                        <i class="fas fa-credit-card text-info me-3"></i> ¿Qué métodos de pago aceptan?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionHelp">
                    <div class="accordion-body">
                        Por el momento aceptamos pago en efectivo al momento de la entrega o retiro, y transferencias bancarias directas. Los datos se proporcionan al confirmar el pedido.
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-5 border-0 shadow-lg  rounded-4 bg-info bg-opacity-10 text-center p-5">
            <h4 class="fw-bold mb-4">¿Necesitas hablar con nosotros?</h4>
            <div class="d-flex justify-content-center gap-4 flex-wrap">
                <a href="https://wa.me/593992470053" class="btn btn-success btn-lg rounded-pill shadow-sm px-4">
                    <i class="fab fa-whatsapp fa-lg me-2"></i> Escríbenos al WhatsApp
                </a>
                <a href="tel:0992470053" class="btn btn-outline-dark btn-lg rounded-pill shadow-sm px-4">
                    <i class="fas fa-phone fa-lg me-2"></i> Llámanos: 0992470053
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
