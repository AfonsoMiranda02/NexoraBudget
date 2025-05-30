document.addEventListener('DOMContentLoaded', function() {
    // Pricing toggle functionality
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const monthlyPrices = document.querySelectorAll('.price.monthly');
    const yearlyPrices = document.querySelectorAll('.price.yearly');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            if (period === 'monthly') {
                monthlyPrices.forEach(p => p.classList.remove('hidden'));
                yearlyPrices.forEach(p => p.classList.add('hidden'));
            } else {
                monthlyPrices.forEach(p => p.classList.add('hidden'));
                yearlyPrices.forEach(p => p.classList.remove('hidden'));
            }
        });
    });

    // Offer cards hover effect
    const offerCards = document.querySelectorAll('.offer-card');
    
    offerCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.2)';
            this.style.border = '2px solid #1B1B3E';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            this.style.border = 'none';
        });
    });
}); 