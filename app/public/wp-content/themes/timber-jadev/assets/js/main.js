$("#nav-main > ul > li").hover(
  function () {
    $(this).find("#header-submenu-container").css("display", "block");
  },
  function () {
    $(this).find("#header-submenu-container").css("display", "none");
  }
);

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> testimonials
// Wait for DOM to be ready
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded");

  // Wait a bit for Swiper elements to be registered
<<<<<<< HEAD
=======
=======
document.addEventListener("DOMContentLoaded", function () {
>>>>>>> 5d6e91a (Testimonial Carousel and Cards)
>>>>>>> testimonials
  setTimeout(function () {
    initializeSwiper();
  }, 100);
});

function initializeSwiper() {
<<<<<<< HEAD
  console.log("Initializing Swiper");

=======
<<<<<<< HEAD
  console.log("Initializing Swiper");

=======
>>>>>>> 5d6e91a (Testimonial Carousel and Cards)
>>>>>>> testimonials
  const swiperEl = document.querySelector("swiper-container");

  if (!swiperEl) {
    console.error("Swiper container not found");
    return;
  }

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> testimonials
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
<<<<<<< HEAD
=======
=======
  // Define the scale function
  function applyScale() {
    if (!swiperEl.shadowRoot) {
      return;
    }

    const slides = swiperEl.querySelectorAll("swiper-slide");

    slides.forEach((slide, index) => {
      slide.style.transform = "";

      if (slide.classList.contains("swiper-slide-active")) {
        slide.style.transform = "scale(1)";
      } else if (
        slide.classList.contains("swiper-slide-prev") ||
        slide.classList.contains("swiper-slide-next")
      ) {
        slide.style.transform = "scale(0.8)";
>>>>>>> 5d6e91a (Testimonial Carousel and Cards)
>>>>>>> testimonials
      } else {
        slide.style.transform = "scale(0.8)";
      }
    });
  }

  // Set parameters
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> testimonials
  swiperEl.params = {
    slidesPerView: 3,
  };

  // Add event listeners
  swiperEl.addEventListener("init", function () {
    console.log("Swiper initialized");
    setTimeout(applyScale, 100);
  });

  swiperEl.addEventListener("slideChange", applyScale);
<<<<<<< HEAD
=======
=======
  Object.assign(swiperEl, {
    loop: true,
    slidesPerView: 3,
    spaceBetween: 10,
    centeredSlides: true,
    initialSlide: 1,
  });

  swiperEl.addEventListener("swiperinit", function (e) {
    setTimeout(applyScale, 200);

    const swiper = e.detail[0];

    document
      .querySelector(".slider-button-prev")
      .addEventListener("click", () => {
        swiper.slidePrev();
      });

    document
      .querySelector(".slider-button-next")
      .addEventListener("click", () => {
        swiper.slideNext();
      });
  });

  // lide change event triggered
  swiperEl.addEventListener("swiperslidechange", function () {
    setTimeout(applyScale, 50);
  });

  // Slide transition end event triggered"
  swiperEl.addEventListener("swiperslidetransitionend", function () {
    applyScale();
  });

  // Touch end event triggered
  swiperEl.addEventListener("swipertouchend", function () {
    console.log("Touch end event triggered");
    setTimeout(applyScale, 50);
  });
>>>>>>> 5d6e91a (Testimonial Carousel and Cards)
>>>>>>> testimonials

  // Initialize the swiper
  swiperEl.initialize();
}
