<?php
/**
 * Template Name: wwdo - section to test
 * Description: Section What We Do - Modern & Trendy Design
 */
get_header(); ?>

<section class="pp-hero-section">
    <div class="pp-hero-bg">
        <video autoplay loop muted playsinline class="pp-video-bg">
            <source src="https://videos.pexels.com/video-files/3129671/3129671-uhd_2560_1440_30fps.mp4"
                type="video/mp4">
        </video>
        <div class="pp-overlay-gradient"></div>
        <div class="pp-overlay-blur"></div>
    </div>

    <div class="pp-grid-pattern">
        <svg viewBox="0 0 1440 900" preserveAspectRatio="none">
            <defs>
                <pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse">
                    <path d="M 60 0 L 0 0 0 60" fill="none" stroke="white" stroke-width="0.5" opacity="0.3"></path>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"></rect>
            <line x1="0" y1="200" x2="600" y2="0" stroke="white" stroke-width="1" opacity="0.2"></line>
            <line x1="100" y1="400" x2="800" y2="0" stroke="white" stroke-width="1" opacity="0.15"></line>
            <line x1="200" y1="600" x2="1000" y2="0" stroke="white" stroke-width="1" opacity="0.1"></line>
            <line x1="800" y1="900" x2="1440" y2="300" stroke="white" stroke-width="1" opacity="0.15"></line>
            <line x1="600" y1="900" x2="1200" y2="400" stroke="white" stroke-width="1" opacity="0.1"></line>
        </svg>
    </div>

    <div class="pp-container">
        <div class="pp-content-wrapper animate-on-scroll">
            <span class="pp-subtitle">Thiết kế website</span>
            <h1 class="pp-title">PixelPerfect</h1>
            <p class="pp-description">
                Thiết kế website doanh nghiệp có thể giúp cho các công ty có thể xây dựng thương hiệu trên kênh Online,
                quảng bá sản phẩm đến với các khách hàng một cách nhanh chóng và dễ dàng.
            </p>

            <div class="pp-actions">
                <button class="pp-btn-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="animate-bounce">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </button>
                <button class="pp-btn-primary">Yêu cầu báo giá</button>
            </div>
        </div>
    </div>

    <div class="pp-bottom-divider">
        <svg viewBox="0 0 1440 450" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <defs>
                <linearGradient id="blendGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#e8f4f8" stop-opacity="0.3"></stop>
                    <stop offset="30%" stop-color="#f0f8fa" stop-opacity="0.6"></stop>
                    <stop offset="60%" stop-color="#f7fbfc" stop-opacity="0.85"></stop>
                    <stop offset="100%" stop-color="#ffffff" stop-opacity="1"></stop>
                </linearGradient>
            </defs>
            <path d="M0 450L1440 450L1440 80L0 450Z" fill="url(#blendGradient)"></path>
            <path d="M0 450L1440 450L1440 120L0 450Z" fill="#ffffff" fill-opacity="0.9"></path>
        </svg>
    </div>

    <div class="pp-floating-wrapper animate-on-scroll" style="transition-delay: 0.3s;">
        <div class="pp-float-bg-effect"></div>

        <div class="pp-image-container">
            <img src="https://v0-page-pp.vercel.app/images/image.png" alt="Website Desktop Tablet"
                class="pp-main-image">

            <div class="pp-image-fade-bottom"></div>

            <div class="pp-bubble bubble-1" style="animation-delay:0s">
                <span style="color:#1e4d8c">Savills</span>
            </div>
            <div class="pp-bubble bubble-2" style="animation-delay:0.5s">
                <span style="color:#c41e3a">DKRS</span>
            </div>
            <div class="pp-bubble bubble-3" style="animation-delay:1s">
                <span style="color:#00a651">Cenco</span>
            </div>
            <div class="pp-bubble bubble-4" style="animation-delay:1.5s">
                <span style="color:#2e7d32">Trans</span>
            </div>
        </div>
    </div>
</section>


<?php get_footer(); ?>