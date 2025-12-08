<?php
session_start();

// Se NÃO estiver logado → volta pro login
// if (!isset($_SESSION['id'])) {
//     header("Location: indexlogin.php");
//     exit;
// }
?>
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

    <!-- CABEÇALHO -->
    <?php include 'include/header.php'; ?>

    <main>
        <!-- HERO SECTION -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>BEM-VINDO À <span>TECHFIT</span></h1>
                <p>Transforme seu corpo, eleve sua mente. O futuro do fitness começa aqui!</p>
                <div class="hero-buttons">
                    <a href="/planos" class="btn-primary">VER PLANOS</a>
                    <a href="/agendaraulas" class="btn-secondary">AGENDAR AULA</a>
                </div>
            </div>
        </section>

        <!-- SERVIÇOS -->
        <section class="services-section">
            <h2 class="section-title">NOSSAS MODALIDADES</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-fist-raised"></i>
                    </div>
                    <h3>Boxe</h3>
                    <p>Aulas em grupo de boxe para desenvolver técnica, força e condicionamento físico completo.</p>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-hand-rock"></i>
                    </div>
                    <h3>Muay Thai</h3>
                    <p>Arte marcial tailandesa em grupo para melhorar flexibilidade, resistência e autodefesa.</p>
                </div>
            </div>
        </section>

        <!-- BUSCAR FILIAIS -->
        <section class="search-section">
            <h2 class="section-title">ENCONTRE UMA FILIAL</h2>
            <p>Procure a unidade TechFit mais próxima de você:</p>
            <div class="search-bar-container">
                <input type="text" placeholder="Digite sua cidade ou bairro" class="search-input">
                <button class="search-button"><i class="fas fa-search"></i></button>
            </div>
        </section>

        <!-- FILIAIS -->
        <section class="filiais-section">
            <div class="filiais-container">
                <div class="filial-card highlight-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia1.png" alt="TechFit Centro">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Centro</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Principal, 1000 - Centro</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 23h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia2.png" alt="TechFit Norte">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Norte</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Rua das Flores, 500 - Zona Norte</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 22h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia3.png" alt="TechFit Sul">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Sul</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Sul, 2000 - Zona Sul</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 5h às 23h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia4.jpg" alt="TechFit Leste">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Leste</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Rua Leste, 750 - Zona Leste</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 22h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia5.jpg" alt="TechFit Oeste">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Oeste</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Oeste, 1200 - Zona Oeste</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 7h às 21h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia1.png" alt="TechFit Jardim">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Jardim</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Rua Jardim, 300 - Jardim das Flores</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 23h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia2.png" alt="TechFit Vila">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Vila</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Vila Nova, 850 - Vila Esperança</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 5h às 22h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia3.png" alt="TechFit Parque">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Parque</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Rua do Parque, 600 - Parque Central</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 23h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia4.jpg" alt="TechFit Praia">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Praia</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Beira Mar, 1500 - Praia do Sol</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 7h às 21h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia5.jpg" alt="TechFit Shopping">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Shopping</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Shopping Center, 2º Piso - Loja 205</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 8h às 22h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia1.png" alt="TechFit Industrial">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Industrial</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Industrial, 2500 - Distrito Industrial</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 5h às 23h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia2.png" alt="TechFit Universitário">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Universitário</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Rua Universitária, 400 - Campus</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 22h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia3.png" alt="TechFit Residencial">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Residencial</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Condomínio Residencial, Bloco A - Apto 101</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 7h às 21h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia4.jpg" alt="TechFit Comercial">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Comercial</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Comercial, 1800 - Centro Comercial</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 23h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia5.jpg" alt="TechFit Esportivo">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Esportivo</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Complexo Esportivo, Setor 3 - Quadra 5</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 5h às 22h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>

                <div class="filial-card">
                    <div class="card-image-wrapper">
                        <img src="View/img/ImagemAcademia1.png" alt="TechFit Premium">
                    </div>
                    <div class="card-content">
                        <h3 class="filial-title">TechFit Premium</h3>
                        <p class="address"><i class="fas fa-map-marker-alt"></i> Localização:</p>
                        <p class="address-detail">Av. Premium, 3000 - Bairro Premium</p>
                        <p class="address"><i class="fas fa-clock"></i> Horário:</p>
                        <p class="address-detail">Seg-Sex: 6h às 23h</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                            <a href="#"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BANNER PROMOCIONAL -->
        

        <!-- DEPOIMENTOS -->
        <section class="testimonials-section">
            <h2 class="section-title">O QUE NOSSOS ALUNOS DIZEM</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"A TechFit mudou minha vida! Em 6 meses perdi 15kg e ganhei muita disposição. Os professores são incríveis!"</p>
                    <span class="client-name">- Maria Silva</span>
                </div>

                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Melhor academia da região! Equipamentos novos, ambiente limpo e equipe super atenciosa."</p>
                    <span class="client-name">- João Santos</span>
                </div>

                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="testimonial-text">"As aulas em grupo são demais! O ambiente é muito motivador e os horários são flexíveis."</p>
                    <span class="client-name">- Ana Oliveira</span>
                </div>
            </div>
        </section>

        <!-- ESTATÍSTICAS -->
        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <h3>5.000+</h3>
                    <p>Alunos Ativos</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-building"></i>
                    <h3>15</h3>
                    <p>Unidades</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-user-check"></i>
                    <h3>50+</h3>
                    <p>Professores</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-trophy"></i>
                    <h3>10</h3>
                    <p>Anos de Experiência</p>
                </div>
            </div>
        </section>
    </main>

    <!-- RODAPÉ -->
    <?php include 'include/footer.php'; ?>

</body>
</html>
