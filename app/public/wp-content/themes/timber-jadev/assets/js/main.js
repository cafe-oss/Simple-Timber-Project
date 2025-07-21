// Wait for the DOM to load
document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector("header > div");

  window.addEventListener("scroll", () => {
    if (window.scrollY > 0) {
      header.classList.add("header-sticky");
    } else {
      header.classList.remove("header-sticky");
    }
  });
});

let hideTimeout;

jQuery(document).ready(function ($) {
  $("#nav-main > ul > li").hover(
    function () {
      clearTimeout(hideTimeout);
      const submenu = $(this).find("#header-submenu-container");
      submenu.removeClass("hidden opacity-0").addClass("opacity-100");
    },
    function () {
      const submenu = $(this).find("#header-submenu-container");
      submenu.removeClass("opacity-100").addClass("opacity-0");
      hideTimeout = setTimeout(() => {
        submenu.addClass("hidden");
      }, 300);
    }
  );
});

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(function () {
    initializeSwiper();
  }, 100);
});

function initializeSwiper() {
  const swiperEl = document.querySelector("swiper-container");

  if (!swiperEl) {
    console.error("Swiper container not found");
    return;
  }

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
      } else {
        slide.style.transform = "scale(0.8)";
      }
    });
  }

  // Set parameters
  Object.assign(swiperEl, {
    loop: true,
    slidesPerView: 3,
    spaceBetween: 10,
    centeredSlides: true,
    initialSlide: 1,
    breakpoints: {
      0: {
        slidesPerView: 1, // applies to all widths below 768px
      },
      768: {
        slidesPerView: 3, // applies to 768px and up
      },
    },
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

  // Initialize the swiper
  swiperEl.initialize();
}
