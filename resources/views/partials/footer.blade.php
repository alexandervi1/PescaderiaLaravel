<footer class="site-footer pt-5 pb-4 mt-5">
    <div class="container text-center text-md-start">
        <div class="row align-items-center">
            <div class="col-md-7 col-lg-8">
                <p>&copy; {{ date('Y') }} {{ $negocio['nombre'] }}. Todos los derechos reservados.</p>
                <div class="small footer-meta">
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i>{{ $negocio['direccion'] }}</p>
                    <p><i class="fas fa-phone me-2"></i>{{ $negocio['telefono'] }}</p>
                </div>
            </div>
            <div class="col-md-5 col-lg-4 text-center text-md-end">
                <ul class="list-unstyled d-flex justify-content-center justify-content-md-end gap-3 fs-3 mb-0">
                    <li><a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a></li>
                    <li><a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
