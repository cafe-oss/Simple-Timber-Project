$("#nav-main > ul > li").hover(
  function () {
    $(this).find("#header-submenu-container").css("display", "block");
  },
  function () {
    $(this).find("#header-submenu-container").css("display", "none");
  }
);

// Wait for DOM to be ready
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded");

  // Wait a bit for Swiper elements to be registered
  setTimeout(function () {
    initializeSwiper();
  }, 100);
});

function initializeSwiper() {
  console.log("Initializing Swiper");

  const swiperEl = document.querySelector("swiper-container");

  if (!swiperEl) {
    console.error("Swiper container not found");
    return;
  }

  console.log("Swiper element found:", swiperEl);

  // Define the scale function
  function applyScale() {
    console.log("applyScale called");

    if (!swiperEl.shadowRoot) {
      console.log("Shadow root not available yet");
      return;
    }

    const slides = swiperEl.shadowRoot.querySelectorAll(".swiper-slide");
    console.log("Found slides:", slides.length);

    slides.forEach((slide) => {
      if (slide.classList.contains("swiper-slide-active")) {
        slide.style.transform = "scale(1)";
        console.log("check - active slide found");
      } else {
        slide.style.transform = "scale(0.8)";
      }
    });
  }

  // Set parameters
  swiperEl.params = {
    slidesPerView: 3,
  };

  // Add event listeners
  swiperEl.addEventListener("init", function () {
    console.log("Swiper initialized");
    setTimeout(applyScale, 100);
  });

  swiperEl.addEventListener("slideChange", applyScale);

  // Initialize the swiper
  swiperEl.initialize();
}
