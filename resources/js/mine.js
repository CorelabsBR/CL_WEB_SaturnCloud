// Carrossel Minecraft Java
class MinecraftCarousel {
    constructor() {
        this.track = document.getElementById('carouselTrack');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.dotsContainer = document.getElementById('carouselDots');
        this.cards = this.track.children;
        this.currentIndex = 0;
        this.cardWidth = 220 + 24; // 220px + 1.5rem gap
        this.maxIndex = this.cards.length - 1;
        
        this.init();
    }
    
    init() {
        this.createDots();
        this.updateCarousel();
        this.bindEvents();
    }
    
    createDots() {
        for (let i = 0; i < this.cards.length; i++) {
            const dot = document.createElement('div');
            dot.className = 'minecraftjava-dot';
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => this.goToIndex(i));
            this.dotsContainer.appendChild(dot);
        }
    }
    
    bindEvents() {
        this.prevBtn.addEventListener('click', () => this.prev());
        this.nextBtn.addEventListener('click', () => this.next());
        
        // Drag para mobile
        let startX = 0;
        this.track.addEventListener('mousedown', (e) => {
            startX = e.clientX;
            this.track.style.cursor = 'grabbing';
        });
        this.track.addEventListener('mouseup', (e) => {
            const diff = startX - e.clientX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) this.next();
                else this.prev();
            }
            this.track.style.cursor = 'grab';
        });
    }
    
    updateCarousel() {
        this.track.style.transform = `translateX(-${this.currentIndex * this.cardWidth}px)`;
        this.updateDots();
    }
    
    updateDots() {
        const dots = this.dotsContainer.children;
        for (let i = 0; i < dots.length; i++) {
            dots[i].classList.toggle('active', i === this.currentIndex);
        }
    }
    
    next() {
        if (this.currentIndex < this.maxIndex) {
            this.currentIndex++;
            this.updateCarousel();
        }
    }
    
    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.updateCarousel();
        }
    }
    
    goToIndex(index) {
        this.currentIndex = Math.max(0, Math.min(index, this.maxIndex));
        this.updateCarousel();
    }
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    new MinecraftCarousel();
});