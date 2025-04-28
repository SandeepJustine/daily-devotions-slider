document.addEventListener('DOMContentLoaded', function() {
    const sliders = document.querySelectorAll('.devotions-slider');
    
     
    sliders.forEach(slider => {
        const wrapper = slider.querySelector('.slider-wrapper');
        const slides = slider.querySelectorAll('.devotion-slide:not(.no-devotion)');
        const prevBtn = slider.querySelector('.slider-prev');
        const nextBtn = slider.querySelector('.slider-next');
        const dotsContainer = slider.querySelector('.slider-dots');
        let currentIndex = 0;
        let autoplayInterval;
        let touchStartX = 0;
        let touchEndX = 0;
        
        // Only initialize if there are slides
        if (slides.length > 0) {
            initSlider();
        }
        
        function initSlider() {
            // Set initial active slide
            slides[currentIndex].classList.add('active');
            
            // Create dots if needed
            if (dotsContainer) {
                slides.forEach((_, index) => {
                    const dot = document.createElement('button');
                    dot.classList.add('slider-dot');
                    dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
                    dot.addEventListener('click', () => goToSlide(index));
                    dotsContainer.appendChild(dot);
                });
                updateDots();
            }
            
            // Handle autoplay
            if (slider.dataset.autoplay === 'true') {
                startAutoplay();
                
                // Pause on hover
                slider.addEventListener('mouseenter', pauseAutoplay);
                slider.addEventListener('mouseleave', startAutoplay);
            }
            
            // Touch events
            wrapper.addEventListener('touchstart', handleTouchStart, { passive: true });
            wrapper.addEventListener('touchend', handleTouchEnd, { passive: true });
        }
        
        // Navigation functions
        function nextSlide() {
            goToSlide(currentIndex < slides.length - 1 ? currentIndex + 1 : 0);
        }
        
        function prevSlide() {
            goToSlide(currentIndex > 0 ? currentIndex - 1 : slides.length - 1);
        }
        
        function goToSlide(index) {
            // Update classes
            slides[currentIndex].classList.remove('active');
            currentIndex = (index + slides.length) % slides.length;
            slides[currentIndex].classList.add('active');
            
            // Update wrapper position
            wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
            
            // Update dots
            updateDots();
            
            // Reset autoplay
            if (autoplayInterval) {
                resetAutoplay();
            }
        }
        
        function updateDots() {
            if (dotsContainer) {
                const dots = dotsContainer.querySelectorAll('.slider-dot');
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }
        }
        
        // Autoplay functions
        function startAutoplay() {
            if (autoplayInterval) return;
            const interval = parseInt(slider.dataset.interval) || 5000;
            autoplayInterval = setInterval(nextSlide, interval);
        }
        
        function pauseAutoplay() {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
        
        function resetAutoplay() {
            pauseAutoplay();
            startAutoplay();
        }
        
        // Touch handlers
        function handleTouchStart(e) {
            touchStartX = e.changedTouches[0].clientX;
        }
        
        function handleTouchEnd(e) {
            touchEndX = e.changedTouches[0].clientX;
            handleSwipe();
        }
        
        function handleSwipe() {
            const threshold = 50;
            const delta = touchStartX - touchEndX;
            
            if (delta > threshold) {
                nextSlide();
            } else if (delta < -threshold) {
                prevSlide();
            }
        }
        
        // Event listeners
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        
        // Keyboard navigation
        slider.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                e.preventDefault();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                e.preventDefault();
            }
        });
        
        // Initialize
        initSlider();
    });
});