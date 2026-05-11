(function() {
    'use strict';
    
    // ========== PARALLAX DEL BANNER HERO ==========
    var hero = document.getElementById('parallaxHero');
    if (hero) {
        var layers = document.querySelectorAll('.parallax-layer');
        
        function handleHeroParallax() {
            var scrolled = window.pageYOffset;
            var heroRect = hero.getBoundingClientRect();
            var heroTop = heroRect.top + window.scrollY;
            var heroHeight = heroRect.height;
            var yOffset = scrolled - heroTop;
            
            if (yOffset < 0) yOffset = 0;
            if (yOffset > heroHeight) yOffset = heroHeight;
            var factor = yOffset / heroHeight;
            
            layers.forEach(function(layer) {
                var speed = parseFloat(layer.getAttribute('data-speed') || 0.3);
                var translateY = factor * 70 * speed;
                if (layer.classList.contains('layer-particles')) {
                    translateY = factor * 40 * speed;
                }
                layer.style.transform = 'translateY(' + translateY + 'px)';
            });
        }
        
        window.addEventListener('scroll', handleHeroParallax);
        handleHeroParallax();
        
        // Efecto parallax con mouse en el banner
        hero.addEventListener('mousemove', function(e) {
            var layersHero = document.querySelectorAll('.parallax-layer');
            var xVal = (e.clientX / window.innerWidth) * 20;
            var yVal = (e.clientY / window.innerHeight) * 10;
            layersHero.forEach(function(layer) {
                if (layer.classList.contains('layer-bg')) {
                    layer.style.transform = 'translate(' + (xVal * 0.05) + 'px, ' + (yVal * 0.03) + 'px)';
                } else if (layer.classList.contains('layer-particles')) {
                    layer.style.transform = 'translate(' + (xVal * 0.1) + 'px, ' + (yVal * 0.05) + 'px)';
                }
            });
        });
        
        hero.addEventListener('mouseleave', function() {
            document.querySelectorAll('.parallax-layer').forEach(function(layer) {
                if (!layer.style.transform) return;
                if (layer.classList.contains('layer-bg') || layer.classList.contains('layer-particles')) {
                    layer.style.transform = '';
                }
            });
        });
    }
    
    // ========== EFECTO DE FLOTACIÓN EN IMÁGENES ==========
    var cards = document.querySelectorAll('.card');
    
    cards.forEach(function(card) {
        var imageWrapper = card.querySelector('.card__image-wrapper');
        var image = card.querySelector('.card__image');
        if (!imageWrapper || !image) return;
        
        card.addEventListener('mousemove', function(e) {
            var rect = card.getBoundingClientRect();
            var mouseX = (e.clientX - rect.left) / rect.width;
            var mouseY = (e.clientY - rect.top) / rect.height;
            
            // Movimiento sutil de la imagen
            var moveX = (mouseX - 0.5) * 15;
            var moveY = (mouseY - 0.5) * 10;
            var rotateValue = (mouseX - 0.5) * 4;
            
            imageWrapper.style.transform = `translateX(calc(-50% + ${moveX}px)) translateY(${moveY - 8}px)`;
            image.style.transform = `rotate(${rotateValue}deg) scale(1.03)`;
        });
        
        card.addEventListener('mouseleave', function() {
            imageWrapper.style.transform = 'translateX(-50%) translateY(0px)';
            image.style.transform = 'rotate(0deg) scale(1)';
        });
    });
    
    // ========== ANIMACIÓN DE ENTRADA ==========
    var cardsNodes = document.querySelectorAll('.card');
    cardsNodes.forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.15}s, transform 0.6s ease ${index * 0.15}s`;
        
        setTimeout(function() {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
    
})();