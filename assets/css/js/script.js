document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-close alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Currency pair selection sync
    var baseCurrency = document.getElementById('base_currency');
    var targetCurrency = document.getElementById('target_currency');
    
    if (baseCurrency && targetCurrency) {
        baseCurrency.addEventListener('change', function() {
            if (this.value === targetCurrency.value) {
                // Find another currency to select
                for (var i = 0; i < targetCurrency.options.length; i++) {
                    if (targetCurrency.options[i].value !== this.value && targetCurrency.options[i].value !== '') {
                        targetCurrency.value = targetCurrency.options[i].value;
                        break;
                    }
                }
            }
        });
        
        targetCurrency.addEventListener('change', function() {
            if (this.value === baseCurrency.value) {
                // Find another currency to select
                for (var i = 0; i < baseCurrency.options.length; i++) {
                    if (baseCurrency.options[i].value !== this.value && baseCurrency.options[i].value !== '') {
                        baseCurrency.value = baseCurrency.options[i].value;
                        break;
                    }
                }
            }
        });
    }
    
    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});

// Add intersection observer for scroll animations
document.addEventListener('DOMContentLoaded', function() {
    // Dark mode toggle handler
    document.getElementById('themeToggle')?.addEventListener('change', function() {
        toggleTheme();
    });
    
    // Scroll animations
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                element.classList.add('pop-in');
            }
        });
    };
    
    // Initialize scroll animations
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on page load
});