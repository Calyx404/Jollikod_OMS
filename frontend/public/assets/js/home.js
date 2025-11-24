// Smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Header scroll effect
const header = document.getElementById("header");
let lastScroll = 0;

window.addEventListener("scroll", () => {
  const currentScroll = window.pageYOffset;

  if (currentScroll > 100) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }

  lastScroll = currentScroll;
});

// Parallax effect for deal cards
const dealCards = document.querySelectorAll(".deal-card");

window.addEventListener("scroll", () => {
  dealCards.forEach((card) => {
    const speed = card.dataset.parallax || 0.05;
    const rect = card.getBoundingClientRect();
    const scrolled = window.pageYOffset;
    const yPos = -(rect.top + scrolled) * speed;

    if (rect.top < window.innerHeight && rect.bottom > 0) {
      card.style.transform = `translateY(${yPos}px)`;
    }
  });
});

// Timeline animation on scroll
const timelineItems = document.querySelectorAll(".timeline-item");

const observerOptions = {
  threshold: 0.2,
  rootMargin: "0px 0px -100px 0px",
};

const timelineObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry, index) => {
    if (entry.isIntersecting) {
      setTimeout(() => {
        entry.target.classList.add("visible");
      }, index * 150);
    }
  });
}, observerOptions);

timelineItems.forEach((item) => {
  timelineObserver.observe(item);
});

// Feature cards animation on scroll
const featureCards = document.querySelectorAll(".feature-card");

const featureObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry, index) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }, index * 100);
      }
    });
  },
  {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }
);

featureCards.forEach((card) => {
  card.style.opacity = "0";
  card.style.transform = "translateY(30px)";
  card.style.transition = "all 0.6s ease";
  featureObserver.observe(card);
});

// Floating food parallax effect
const floatingFood = document.querySelectorAll(".floating-food");

window.addEventListener("mousemove", (e) => {
  const mouseX = e.clientX / window.innerWidth;
  const mouseY = e.clientY / window.innerHeight;

  floatingFood.forEach((food, index) => {
    const speed = (index + 1) * 20;
    const x = (mouseX - 0.5) * speed;
    const y = (mouseY - 0.5) * speed;

    food.style.transform = `translate(${x}px, ${y}px)`;
  });
});

// Enhanced scroll-based parallax for hero section
const hero = document.querySelector(".hero");
const heroContent = document.querySelector(".hero-content");

window.addEventListener("scroll", () => {
  const scrolled = window.pageYOffset;
  const heroHeight = hero.offsetHeight;

  if (scrolled < heroHeight) {
    // Parallax effect for hero content
    heroContent.style.transform = `translateY(${scrolled * 0.4}px)`;
    heroContent.style.opacity = 1 - (scrolled / heroHeight) * 1.5;

    // Parallax for floating food
    floatingFood.forEach((food, index) => {
      const speed = 0.2 + index * 0.1;
      food.style.transform = `translateY(${scrolled * speed}px)`;
    });
  }
});

// Deal cards hover effect enhancement
dealCards.forEach((card) => {
  card.addEventListener("mouseenter", function () {
    this.style.transition = "all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)";
  });

  card.addEventListener("mouseleave", function () {
    this.style.transition = "all 0.3s ease";
  });
});

// Smooth entrance animation for hero elements
window.addEventListener("load", () => {
  const heroElements = document.querySelectorAll(
    ".hero h1, .hero p, .hero-buttons"
  );
  heroElements.forEach((el, index) => {
    setTimeout(() => {
      el.style.opacity = "1";
      el.style.transform = "translateY(0)";
    }, index * 200);
  });
});

// Button ripple effect
const buttons = document.querySelectorAll(".btn");

buttons.forEach((button) => {
  button.addEventListener("click", function (e) {
    const ripple = document.createElement("span");
    const rect = this.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + "px";
    ripple.style.left = x + "px";
    ripple.style.top = y + "px";
    ripple.classList.add("ripple");

    this.appendChild(ripple);

    setTimeout(() => {
      ripple.remove();
    }, 600);
  });
});

// Add ripple styles dynamically
const style = document.createElement("style");
style.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Lazy loading images
const images = document.querySelectorAll("img[data-src]");
const imageObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      const img = entry.target;
      img.src = img.dataset.src;
      img.removeAttribute("data-src");
      imageObserver.unobserve(img);
    }
  });
});

images.forEach((img) => imageObserver.observe(img));

// Performance optimization: Throttle scroll events
function throttle(func, delay) {
  let timeoutId;
  let lastExecTime = 0;

  return function (...args) {
    const currentTime = Date.now();
    const timeSinceLastExec = currentTime - lastExecTime;

    clearTimeout(timeoutId);

    if (timeSinceLastExec > delay) {
      func.apply(this, args);
      lastExecTime = currentTime;
    } else {
      timeoutId = setTimeout(() => {
        func.apply(this, args);
        lastExecTime = Date.now();
      }, delay - timeSinceLastExec);
    }
  };
}

// Apply throttle to scroll handlers
const throttledScroll = throttle(() => {
  // Scroll handlers here
}, 16); // ~60fps

window.addEventListener("scroll", throttledScroll);

// Add loading animation
window.addEventListener("load", () => {
  document.body.classList.add("loaded");
});

// Easter egg: Konami code
let konamiCode = [];
const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];

document.addEventListener("keydown", (e) => {
  konamiCode.push(e.keyCode);
  if (konamiCode.length > 10) konamiCode.shift();

  if (konamiCode.join(",") === konamiSequence.join(",")) {
    // Trigger special animation
    floatingFood.forEach((food) => {
      food.style.animation =
        "float 1s ease-in-out infinite, spin 2s linear infinite";
    });

    setTimeout(() => {
      floatingFood.forEach((food) => {
        food.style.animation = "float 6s ease-in-out infinite";
      });
    }, 5000);
  }
});

// Add spin animation
const spinStyle = document.createElement("style");
spinStyle.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(spinStyle);

// Mobile menu toggle (if needed for responsive design)
const createMobileMenu = () => {
  if (window.innerWidth <= 768) {
    // Mobile menu logic can be added here
    console.log("Mobile view active");
  }
};

window.addEventListener("resize", throttle(createMobileMenu, 250));
createMobileMenu();

// Preload critical images for better performance
const preloadImages = [
  "https://images.unsplash.com/photo-1568901346375-23c9450c58cd",
  "https://images.unsplash.com/photo-1565299624946-b28f40a0ae38",
  "https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9",
];

preloadImages.forEach((src) => {
  const img = new Image();
  img.src = src + "?w=600&h=400&fit=crop";
});
