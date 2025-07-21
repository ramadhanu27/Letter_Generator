/**
 * Home Page JavaScript
 * Indonesian PDF Letter Generator
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all home page functionality
    initNavigation();
    initScrollReveal();
    initCounterAnimation();
    initSmoothScrolling();
    initMobileMenu();
    initParallaxEffect();
    initTypingAnimation();
});

// Navigation functionality
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');
    
    // Highlight active navigation link based on scroll position
    function highlightNavigation() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (window.pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }
    
    window.addEventListener('scroll', highlightNavigation);
    highlightNavigation(); // Initial call
}

// Scroll reveal animation
function initScrollReveal() {
    const revealElements = document.querySelectorAll('.feature-card, .template-card, .stats-counter');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('scroll-reveal', 'revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    revealElements.forEach(element => {
        element.classList.add('scroll-reveal');
        revealObserver.observe(element);
    });
}

// Counter animation
function initCounterAnimation() {
    const counters = document.querySelectorAll('.stats-counter');
    let hasAnimated = false;
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !hasAnimated) {
                hasAnimated = true;
                animateCounters();
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    if (counters.length > 0) {
        counterObserver.observe(counters[0].closest('section'));
    }
    
    function animateCounters() {
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            const suffix = counter.textContent.includes('%') ? '%' : '+';
            const increment = target / 100;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target + suffix;
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current) + suffix;
                }
            }, 20);
        });
    }
}

// Smooth scrolling for anchor links
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const mobileMenu = document.getElementById('mobile-menu');
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    toggleMobileMenu();
                }
            }
        });
    });
}

// Mobile menu functionality
function initMobileMenu() {
    window.toggleMobileMenu = function() {
        const menu = document.getElementById('mobile-menu');
        const button = document.querySelector('[onclick="toggleMobileMenu()"]');
        const icon = button.querySelector('i');
        
        menu.classList.toggle('hidden');
        
        // Animate icon
        if (menu.classList.contains('hidden')) {
            icon.className = 'fas fa-bars text-xl';
        } else {
            icon.className = 'fas fa-times text-xl';
        }
    };
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('mobile-menu');
        const button = document.querySelector('[onclick="toggleMobileMenu()"]');
        
        if (!menu.contains(e.target) && !button.contains(e.target) && !menu.classList.contains('hidden')) {
            toggleMobileMenu();
        }
    });
}

// Parallax effect for hero section
function initParallaxEffect() {
    const heroSection = document.querySelector('#home');
    
    if (heroSection) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            heroSection.style.transform = `translateY(${rate}px)`;
        });
    }
}

// Typing animation for hero title
function initTypingAnimation() {
    const heroTitle = document.querySelector('#home h1');
    
    if (heroTitle) {
        const text = heroTitle.textContent;
        heroTitle.textContent = '';
        heroTitle.style.borderRight = '2px solid #FCD34D';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                heroTitle.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 50);
            } else {
                // Remove cursor after typing is complete
                setTimeout(() => {
                    heroTitle.style.borderRight = 'none';
                }, 1000);
            }
        };
        
        // Start typing animation after a short delay
        setTimeout(typeWriter, 500);
    }
}

// Add loading animation for template cards
function addTemplateLoadingEffect() {
    const templateCards = document.querySelectorAll('.template-card');
    
    templateCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Initialize template loading effect when templates section is visible
const templatesSection = document.querySelector('#templates');
if (templatesSection) {
    const templatesObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                addTemplateLoadingEffect();
                templatesObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    
    templatesObserver.observe(templatesSection);
}

// Add hover effects for feature cards
document.querySelectorAll('.feature-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
        
        const icon = this.querySelector('.feature-icon');
        if (icon) {
            icon.style.transform = 'scale(1.1) rotate(5deg)';
        }
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
        
        const icon = this.querySelector('.feature-icon');
        if (icon) {
            icon.style.transform = 'scale(1) rotate(0deg)';
        }
    });
});

// Add click tracking for analytics (placeholder)
function trackClick(element, action) {
    // Placeholder for analytics tracking
    console.log(`Tracked: ${action} on ${element}`);
    
    // Example: Google Analytics event tracking
    // gtag('event', action, {
    //     'event_category': 'engagement',
    //     'event_label': element
    // });
}

// Add click tracking to CTA buttons
document.querySelectorAll('a[href*="register"], a[href*="login"], a[href*="dashboard"]').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.textContent.trim();
        trackClick('CTA Button', action);
    });
});

// Add scroll progress indicator
function addScrollProgress() {
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.body.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        
        progressBar.style.width = scrollPercent + '%';
    });
}

// Initialize scroll progress
addScrollProgress();

// Add keyboard navigation support
document.addEventListener('keydown', function(e) {
    // ESC key closes mobile menu
    if (e.key === 'Escape') {
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu();
        }
    }
    
    // Arrow keys for navigation (optional)
    if (e.key === 'ArrowDown' && e.ctrlKey) {
        e.preventDefault();
        window.scrollBy(0, 100);
    }
    
    if (e.key === 'ArrowUp' && e.ctrlKey) {
        e.preventDefault();
        window.scrollBy(0, -100);
    }
});

// Performance optimization: Lazy load images
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('loading-skeleton');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => {
        img.classList.add('loading-skeleton');
        imageObserver.observe(img);
    });
}

// Initialize lazy loading
initLazyLoading();
