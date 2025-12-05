<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Página Inicial</title>
    <link rel="stylesheet" href="View/header-footer.css">
    <link rel="stylesheet" href="View/paginainicial.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <!-- CABEÇALHO PADRONIZADO -->
    <?php require_once 'include/header.php'; ?>

    <main>
        <div class="search-section">
            <p>Procure a Filial Mais Próxima:</p>
            <div class="search-bar-container">
                <input type="text" placeholder="Digite sua localização" class="search-input">
                <button class="search-button"><i class="fas fa-search"></i></button>
            </div>
        </div>

        <section class="filiais-container">
            <div class="filial-card">
                <div class="card-image-wrapper">
                    <img src="View/img/ImagemAcademia1.png" alt="Academia Filial 1">
                </div>
                <div class="card-content">
                    <h2 class="filial-title">FILIAL 1</h2>
                    <p class="address"><i class="fas fa-map-marker-alt"></i> Avenida dos Ipês, 456</p>
                    <p class="address-detail">Jardim Primavera</p>
                    <p class="address-detail">Limeira, SP</p>
                    <p class="address-detail">CEP: 13480-000</p>
                    <div class="social-icons">
                        <a href="#"><i class="fas fa-comment-alt"></i></a>
                        <a href="#"><i class="fas fa-calendar-alt"></i></a>
                        <a href="#"><i class="fas fa-map-marker-alt"></i></a>
                        <a href="#"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>

            <div class="promo-banner">
                <img src="/View/img/promocao.png" alt="Transforme seu corpo na TechFit">
            </div>

            <div class="filial-card">
                <div class="card-image-wrapper">
                    <img src="View/img/ImagemAcademia2.png" alt="Academia Filial 2">
                </div>
                <div class="card-content">
                    <h2 class="filial-title">FILIAL 2</h2>
                    <p class="address"><i class="fas fa-map-marker-alt"></i> Rua das Orquídeas, 789</p>
                    <p class="address-detail">Vila Nova</p>
                    <p class="address-detail">Limeira, SP</p>
                    <p class="address-detail">CEP: 13481-222</p>
                    <div class="social-icons">
                        <a href="#"><i class="fas fa-comment-alt"></i></a>
                        <a href="#"><i class="fas fa-camera"></i></a>
                        <a href="#"><i class="fas fa-map-marker-alt"></i></a>
                        <a href="#"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>

            <div class="filial-card highlight-card">
                <div class="card-image-wrapper">
                    <img src="View/img/ImagemAcademia3.png" alt="Academia Filial 3">
                </div>
                <div class="card-content">
                    <h2 class="filial-title">FILIAL 3</h2>
                    <p class="address"><i class="fas fa-map-marker-alt"></i> Praça do Limoeiro, 101</p>
                    <p class="address-detail">Centro</p>
                    <p class="address-detail">Limeira, SP</p>
                    <p class="address-detail">CEP: 13480-500</p>
                    <div class="social-icons">
                        <a href="#"><i class="fas fa-comment-alt"></i></a>
                        <a href="#"><i class="fas fa-camera"></i></a>
                        <a href="#"><i class="fas fa-map-marker-alt"></i></a>
                        <a href="#"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>

            <div class="filial-card highlight-card">
                <div class="card-image-wrapper">
                    <img src="View/img/ImagemAcademia4.jpg" alt="Academia Filial 4">
                </div>
                <div class="card-content">
                    <h2 class="filial-title">FILIAL 4</h2>
                    <p class="address"><i class="fas fa-map-marker-alt"></i> Avenida dos Ipês, 1230</p>
                    <p class="address-detail">Bairro das Palmeiras</p>
                    <p class="address-detail">Limeira, SP</p>
                    <p class="address-detail">CEP: 13481-200</p>
                    <div class="social-icons">
                        <a href="#"><i class="fas fa-comment-alt"></i></a>
                        <a href="#"><i class="fas fa-camera"></i></a>
                        <a href="#"><i class="fas fa-map-marker-alt"></i></a>
                        <a href="#"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>

            <div class="filial-card highlight-card">
                <div class="card-image-wrapper">
                    <img src="View/img/ImagemAcademia5.jpg" alt="Academia Filial 5">
                </div>
                <div class="card-content">
                    <h2 class="filial-title">FILIAL 5</h2>
                    <p class="address"><i class="fas fa-map-marker-alt"></i> Praça do Sol Poente, 42</p>
                    <p class="address-detail">Vila Horizonte</p>
                    <p class="address-detail">Limeira, SP</p>
                    <p class="address-detail">CEP: 13483-550</p>
                    <div class="social-icons">
                        <a href="#"><i class="fas fa-comment-alt"></i></a>
                        <a href="#"><i class="fas fa-camera"></i></a>
                        <a href="#"><i class="fas fa-map-marker-alt"></i></a>
                        <a href="#"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="testimonials-section">
            <h2 class="section-title">O que nossos clientes dizem</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Ambiente incrível e equipamentos de ponta! Os instrutores são super atenciosos e me ajudaram a alcançar meus objetivos mais rápido do que eu esperava."</p>
                    <p class="client-name">- Maria Silva, Aluna há 1 ano</p>
                </div>

                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="testimonial-text">"A TechFit mudou minha vida! Consegui sair do sedentarismo e me apaixonei pelos treinos funcionais. Recomendo muito!"</p>
                    <p class="client-name">- João Carlos, Aluno há 6 meses</p>
                </div>

                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Excelentes instalações e uma variedade de aulas que me mantém motivado. O plano anual vale muito a pena!"</p>
                    <p class="client-name">- Fernanda Lima, Aluna há 2 anos</p>
                </div>

                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"O melhor custo-benefício que encontrei na cidade. Sem falar na limpeza e organização que são impecáveis!"</p>
                    <p class="client-name">- Pedro Souza, Aluno há 8 meses</p>
                </div>
            </div>
            <a href="#" class="view-more-reviews">Ver mais avaliações <i class="fas fa-arrow-right"></i></a>
        </section>
    </main>

    <!-- RODAPÉ PADRONIZADO -->
    <?php require_once 'include/footer.php'; ?>

</body>
</html>