// Wait for the DOM to load
// document.addEventListener("DOMContentLoaded", () => {
//   const header = document.querySelector("header > div");

//   window.addEventListener("scroll", () => {
//     if (window.scrollY > 0) {
//       header.classList.add("header-sticky");
//     } else {
//       header.classList.remove("header-sticky");
//     }
//   });
// });

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

function initializeTestimonialSwiper() {
  const swiperEl = document.querySelector("swiper-container[data-carousel-type='testimonial']");
  const container = swiperEl?.closest('.wrapper');
  const prevBtn = container?.querySelector(".testimonial-slider-prev");
    const nextBtn = container?.querySelector(".testimonial-slider-next");

  if (!swiperEl) {
    // No swiper on this page, skip initialization
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
    slidesPerView: 3,
    spaceBetween: 10,
    centeredSlides: true,
    initialSlide: 0,
    breakpoints: {
      0: {
        slidesPerView: 1, // applies to all widths below 768px
      },
      768: {
        slidesPerView: 3, // applies to 768px and up
      },
    },
  });

  function updateButtonState(swiper) {
      if (!swiper) return;
      prevBtn?.classList.toggle('slider-button-disabled', swiper.isBeginning);
      nextBtn?.classList.toggle('slider-button-disabled', swiper.isEnd);
    }

  swiperEl.addEventListener("swiperinit", function (e) {
    setTimeout(applyScale, 200);

    const swiper = e.detail[0];
    updateButtonState(swiper);

    document
      .querySelector(".testimonial-slider-prev")
      .addEventListener("click", () => {
        swiper.slidePrev();
      });

    document
      .querySelector(".testimonial-slider-next")
      .addEventListener("click", () => {
        swiper.slideNext();
      });
  });

  // Slide change event triggered
  swiperEl.addEventListener("swiperslidechange", function (e) {
    const swiper = e.detail[0];
    setTimeout(applyScale, 50);
    updateButtonState(swiper);
  });

  // Slide transition end event triggered"
  swiperEl.addEventListener("swiperslidetransitionend", function () {
    applyScale();
  });

  // Touch end event triggered
  swiperEl.addEventListener("swipertouchend", function () {
    setTimeout(applyScale, 50);
  });

  // Initialize the swiper
  swiperEl.initialize();
} 

function initializeBannerSwiper(){
  const swiperEls = document.querySelectorAll("swiper-container[data-carousel-type='banner']");

  if (!swiperEls.length) {
    return;
  }

  swiperEls.forEach(function(swiperEl) {
    const container = swiperEl.closest('.wrapper');
    const progressFill = container?.querySelector(".banner-progress-fill");
    const prevBtn = container?.querySelector(".banner-slider-prev");
    const nextBtn = container?.querySelector(".banner-slider-next");

    Object.assign(swiperEl, {
      slidesPerView: 1.1,
      spaceBetween: 24,
      centeredSlides: false,
      initialSlide: 0,
      breakpoints: {
        0: {
          slidesPerView: 2.2,
        },
        768: {
          slidesPerView: 3.2,
        },
      },
    });

    function updateProgress(swiper) {
      if (!progressFill || !swiper) return;
      const progress = swiper.progress * 100;
      progressFill.style.width = Math.min(Math.max(progress, 0), 100) + '%';
    }

    function updateButtonState(swiper) {
      if (!swiper) return;
      prevBtn?.classList.toggle('slider-button-disabled', swiper.isBeginning);
      nextBtn?.classList.toggle('slider-button-disabled', swiper.isEnd);
    }

    swiperEl.addEventListener("swiperinit", function (e) {
      const swiper = e.detail[0];

      updateProgress(swiper);
      updateButtonState(swiper);

      prevBtn?.addEventListener("click", () => swiper.slidePrev());
      nextBtn?.addEventListener("click", () => swiper.slideNext());
    });

    swiperEl.addEventListener("swiperslidechange", function (e) {
      const swiper = e.detail[0];
      updateProgress(swiper);
      updateButtonState(swiper);
    });

    swiperEl.initialize();
  });
}

function initializeSwiper() {
  initializeTestimonialSwiper();
  initializeBannerSwiper();
  initializeVerticalSliderTabs();
  initializeProductGallery();
}

function initializeProductGallery() {
  const wrapper = document.querySelector('[data-component="product-gallery"]');
  if (!wrapper) return;

  const mainEl    = wrapper.querySelector('swiper-container[data-carousel-type="product-main"]');
  const thumbItems = wrapper.querySelectorAll('.product-thumb-item');
  const prevBtn   = wrapper.querySelector('.product-gallery-prev');
  const nextBtn   = wrapper.querySelector('.product-gallery-next');

  if (!mainEl) return;

  function setActiveThumb(index) {
    thumbItems.forEach(function (thumb, i) {
      thumb.classList.toggle('border-[rgb(17,221,196)]', i === index);
      thumb.classList.toggle('border-transparent', i !== index);
    });
  }

  Object.assign(mainEl, {
    slidesPerView: 1,
    spaceBetween: 0,
    loop: false,
  });

  mainEl.addEventListener('swiperinit', function (e) {
    const swiper = e.detail[0];

    // Thumbnail clicks jump to that slide
    thumbItems.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        swiper.slideTo(parseInt(thumb.dataset.index, 10));
      });
    });

    // Sync active thumbnail when slide changes
    mainEl.addEventListener('swiperslidechange', function (e) {
      setActiveThumb(e.detail[0].activeIndex);
    });

    prevBtn && prevBtn.addEventListener('click', function () { swiper.slidePrev(); });
    nextBtn && nextBtn.addEventListener('click', function () { swiper.slideNext(); });

    // Highlight first thumbnail on load
    setActiveThumb(0);
  });

  mainEl.initialize();
}

// Vertical Slider Tabs - supports multiple instances
function initializeVerticalSliderTabs() {
  const sections = document.querySelectorAll('[data-component="vertical-slider-tabs"]');

  if (!sections.length) return;

  sections.forEach(function (section) {
    const tabItems = section.querySelectorAll('.rct-tab-item');
    const mediaSlides = section.querySelectorAll('.rct-media-slide');
    const tabsScroll = section.querySelector('[data-rct-tabs-scroll]');
    const prevBtn = section.querySelector('.rct-slider-prev');
    const nextBtn = section.querySelector('.rct-slider-next');

    if (!tabItems.length) return;

    let currentIndex = 0;
    let autoPlayTimer = null;
    const autoPlayDelay = 5000;
    const totalTabs = tabItems.length;

    

    // Update button states
    function updateButtonState() {
      if (prevBtn) {
        prevBtn.classList.toggle('slider-button-disabled', currentIndex === 0);
      }
      if (nextBtn) {
        nextBtn.classList.toggle('slider-button-disabled', currentIndex === totalTabs - 1);
      }
    }

    // Set active tab and slide
    function setActiveTab(index) {
      if (index < 0 || index >= totalTabs) return;

      currentIndex = index;

      // Update tabs
      tabItems.forEach((tab, i) => {
        const isActive = i === index;
        tab.classList.toggle('rct-tab-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        tab.setAttribute('tabindex', isActive ? '0' : '-1');
      });

      // Update media slides
      mediaSlides.forEach((slide, i) => {
        slide.classList.remove('rct-slide-active', 'rct-slide-prev', 'rct-slide-next');

        if (i === index) {
          slide.classList.add('rct-slide-active');
          // Play video if exists
          const video = slide.querySelector('video');
          if (video) {
            video.currentTime = 0;
            video.play().catch(() => {});
          }
        } else if (i < index) {
          slide.classList.add('rct-slide-prev');
          // Pause video
          const video = slide.querySelector('video');
          if (video) video.pause();
        } else {
          slide.classList.add('rct-slide-next');
          // Pause video
          const video = slide.querySelector('video');
          if (video) video.pause();
        }
      });

      // Scroll tabs into view (vertical centering)
      if (tabsScroll && window.innerWidth >= 1024) {
        const tabHeight = tabItems[0].offsetHeight;
        const containerHeight = section.querySelector('.rct-tabs-wrapper').offsetHeight;
        const scrollOffset = (index * tabHeight) - (containerHeight / 2) + (tabHeight / 2);
        tabsScroll.style.transform = `translateY(${-Math.max(0, scrollOffset)}px)`;
      }

      updateButtonState();
      resetAutoPlay();
    }

    // Navigate to next/prev
    function goToNext() {
      if (currentIndex < totalTabs - 1) {
        setActiveTab(currentIndex + 1);
      }
    }

    function goToPrev() {
      if (currentIndex > 0) {
        setActiveTab(currentIndex - 1);
      }
    }

    // Auto-play functionality
    function startAutoPlay() {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
      autoPlayTimer = setInterval(() => {
        if (currentIndex < totalTabs - 1) {
          setActiveTab(currentIndex + 1);
        } else {
          setActiveTab(0); // Loop back to start
        }
      }, autoPlayDelay);
    }

    function resetAutoPlay() {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
      startAutoPlay();
    }

    // Event listeners for tabs
    tabItems.forEach((tab, index) => {
      tab.addEventListener('click', () => setActiveTab(index));

      // Keyboard navigation
      tab.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
          e.preventDefault();
          goToNext();
          tabItems[currentIndex]?.focus();
        } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
          e.preventDefault();
          goToPrev();
          tabItems[currentIndex]?.focus();
        } else if (e.key === 'Home') {
          e.preventDefault();
          setActiveTab(0);
          tabItems[0]?.focus();
        } else if (e.key === 'End') {
          e.preventDefault();
          setActiveTab(totalTabs - 1);
          tabItems[totalTabs - 1]?.focus();
        }
      });
    });

    // Navigation button listeners
    if (prevBtn) {
      prevBtn.addEventListener('click', goToPrev);
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', goToNext);
    }

    // Pause autoplay on hover
    section.addEventListener('mouseenter', () => {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
    });

    section.addEventListener('mouseleave', () => {
      startAutoPlay();
    });

    // Touch/swipe support for mobile
    let touchStartY = 0;
    let touchEndY = 0;

    section.addEventListener('touchstart', (e) => {
      touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    section.addEventListener('touchend', (e) => {
      touchEndY = e.changedTouches[0].screenY;
      handleSwipe();
    }, { passive: true });

    function handleSwipe() {
      const swipeThreshold = 50;
      const diff = touchStartY - touchEndY;

      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          goToNext(); // Swipe up
        } else {
          goToPrev(); // Swipe down
        }
      }
    }

    // Initialize
    setActiveTab(0);
    startAutoPlay();
  });
}

// Video play/pause functionality - supports multiple instances
document.addEventListener("DOMContentLoaded", function () {
  const videoSections = document.querySelectorAll(".video-section");

  videoSections.forEach(function (section) {
    const video = section.querySelector("video");
    const playButton = section.querySelector('button[aria-label="Play the video"]');

    if (!video || !playButton) return;

    const playIcon = playButton.querySelector(".play-icon");
    const pauseIcon = playButton.querySelector(".pause-icon");

    playButton.addEventListener("click", function () {
      if (video.paused) {
        video.play();
        playButton.setAttribute("aria-label", "Pause the video");
        playIcon.classList.add("hidden");
        pauseIcon.classList.remove("hidden");
      } else {
        video.pause();
        playButton.setAttribute("aria-label", "Play the video");
        playIcon.classList.remove("hidden");
        pauseIcon.classList.add("hidden");
      }
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach(function(item){
    const faqButton = item.querySelector("button");
    const faqTexts = item.querySelector(".faq-texts");

    if (!faqButton || !faqTexts) return;

    faqButton.addEventListener("click", function(){
      const isExpanded = faqButton.getAttribute("aria-expanded") === "true";

      if (isExpanded) {
        faqButton.setAttribute("aria-expanded", "false");
        faqTexts.style.maxHeight = null;
      } else {
        faqButton.setAttribute("aria-expanded", "true");
        faqTexts.style.maxHeight = faqTexts.scrollHeight + "px";
      }
    });
  });
});

// -------------------------------------------------------
// Delivery zip code lookup
// -------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const submitBtn = document.getElementById('delivery-zipcode-submit');
    const input     = document.getElementById('delivery-zipcode');
    const result    = document.getElementById('delivery-result');

    if (!submitBtn || !input || !result) return;

    function lookup() {
        const zip = input.value.trim();

        if (!/^\d{5}(-\d{4})?$/.test(zip)) {
            result.innerHTML = '<span class="text-red-500">Please enter a valid 5-digit zip code.</span>';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Checking...';
        result.innerHTML = '';

        const body = new URLSearchParams({
            action:  'jadev_check_shipping',
            nonce:   jadevAjax.nonce,
            zipcode: zip,
        });

        fetch(jadevAjax.url, { method: 'POST', body })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const lines = data.data.rates.map(r =>
                        `<span>${r.label}: <strong>${r.cost}</strong></span>`
                    ).join('<br>');
                    result.innerHTML = `<span class="text-green-600">${lines}</span>`;
                } else {
                    result.innerHTML = `<span class="text-red-500">${data.data.message}</span>`;
                }
            })
            .catch(() => {
                result.innerHTML = '<span class="text-red-500">Something went wrong. Please try again.</span>';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit';
            });
    }

    submitBtn.addEventListener('click', lookup);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') lookup();
    });
});

function openCoachModal(btn) {
  var modal = document.getElementById('coach-modal');
  document.getElementById('coach-modal-title').textContent = btn.dataset.coachTitle;
  document.getElementById('coach-modal-style').textContent = btn.dataset.coachStyle;
  document.getElementById('coach-modal-content').innerHTML = btn.dataset.coachContent;
  document.getElementById('coach-modal-social').textContent = btn.dataset.coachSocial;
  const isGuest = btn.dataset.coachIsGuest;

  if(isGuest === "yes"){
    document.getElementById('coach-modal-is-guest').classList.remove('hidden');
  }
  
  var img = document.getElementById('coach-modal-image');
  if (btn.dataset.coachImage) {
      img.src = btn.dataset.coachImage;
      img.alt = btn.dataset.coachTitle;
      img.classList.remove('hidden');
  } else {
      img.classList.add('hidden');
  }
  modal.showModal();
}

// ── Favorites ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var STORAGE_KEY = 'jadev_favorites';
    var isLoggedIn  = typeof jadevFavorites !== 'undefined' && jadevFavorites.isLoggedIn;

    function getFavorites() {
        if (isLoggedIn) {
            return (jadevFavorites.favorites || []).map(Number);
        }
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]').map(Number);
        } catch (e) {
            return [];
        }
    }

    function saveFavoritesLocally(favorites) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(favorites));
        } catch (e) {}
    }

    function setButtonState(btn, isFavorited) {
        var outline = btn.querySelector('.fav-icon-outline');
        var filled  = btn.querySelector('.fav-icon-filled');

        btn.setAttribute('aria-pressed', isFavorited ? 'true' : 'false');
        btn.setAttribute('aria-label', isFavorited ? 'Remove from favorites' : 'Add to favorites');
        btn.classList.toggle('is-favorited', isFavorited);

        if (isFavorited) {
            outline && outline.classList.add('hidden');
            filled  && filled.classList.remove('hidden');
            btn.classList.add('text-red-500');
            btn.classList.remove('text-gray-500');
        } else {
            outline && outline.classList.remove('hidden');
            filled  && filled.classList.add('hidden');
            btn.classList.remove('text-red-500');
            btn.classList.add('text-gray-500');
        }
    }

    function applyFavoriteStates(favorites) {
        document.querySelectorAll('.favorite-btn').forEach(function (btn) {
            var id = parseInt(btn.dataset.productId, 10);
            setButtonState(btn, favorites.includes(id));
        });
    }

    function toggleFavorite(btn) {
        var productId = parseInt(btn.dataset.productId, 10);
        if (!productId) return;

        if (isLoggedIn) {
            var body = new URLSearchParams({
                action:     'jadev_toggle_favorite',
                nonce:      jadevFavorites.nonce,
                product_id: productId,
            });

            fetch(jadevAjax.url, { method: 'POST', body: body })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        jadevFavorites.favorites = data.data.favorites;
                        applyFavoriteStates(data.data.favorites);
                        dispatchFavoritesUpdated(data.data.favorites);
                    }
                })
                .catch(function () {});
        } else {
            var favorites = getFavorites();
            var idx = favorites.indexOf(productId);

            if (idx !== -1) {
                favorites.splice(idx, 1);
            } else {
                favorites.push(productId);
            }

            saveFavoritesLocally(favorites);
            applyFavoriteStates(favorites);
            dispatchFavoritesUpdated(favorites);
        }
    }

    function dispatchFavoritesUpdated(favorites) {
        var grid = document.getElementById('favorites-products-grid');
        if (grid) {
            grid.dispatchEvent(new CustomEvent('favorites-updated', { detail: favorites }));
        }
    }

    // Event delegation — works for dynamically-added buttons too
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.favorite-btn');
        if (!btn) return;
        e.preventDefault();
        toggleFavorite(btn);
    });

    // Set initial states on page load
    applyFavoriteStates(getFavorites());
});

// ── Favorites page — initial empty-state (logged-in view) ─────────────────
document.addEventListener('DOMContentLoaded', function () {
    var grid    = document.getElementById('favorites-products-grid');
    var empty   = document.getElementById('favorites-empty');
    var loading = document.getElementById('favorites-loading'); // only exists in guest view

    // Only run for the logged-in view (no loading spinner present)
    if (!grid || !empty || loading) return;

    var hasCards = grid.querySelectorAll(':scope > .flex.flex-col').length > 0;
    if (!hasCards) {
        empty.classList.remove('hidden');
    }

    // Remove a card live when the user un-favorites it while on this page.
    // dispatchFavoritesUpdated() in Block 1 fires this event after every toggle.
    grid.addEventListener('favorites-updated', function (e) {
        var currentIds = (e.detail || []).map(Number);

        grid.querySelectorAll('.favorite-btn[data-product-id]').forEach(function (btn) {
            var id = parseInt(btn.dataset.productId, 10);
            if (!currentIds.includes(id)) {
                btn.closest('.flex.flex-col').remove();
            }
        });

        if (!grid.querySelectorAll(':scope > .flex.flex-col').length) {
            grid.classList.add('hidden');
            empty.classList.remove('hidden');
        }
    });
});

// ── Favorites page — guest product loader ─────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var grid    = document.getElementById('favorites-products-grid');
    var loading = document.getElementById('favorites-loading');
    var empty   = document.getElementById('favorites-empty');

    // Only run on the guest view — the loading spinner only exists in the guest section.
    // On the logged-in view there is no #favorites-loading, so bail out here to avoid
    // overwriting the server-rendered empty state.
    if (!grid || !loading) return;

    var STORAGE_KEY = 'jadev_favorites';
    var ids = [];
    try {
        ids = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]').map(Number).filter(Boolean);
    } catch (e) {}

    if (!ids.length) {
        loading && loading.classList.add('hidden');
        empty   && empty.classList.remove('hidden');
        return;
    }

    // Fetch product data for saved IDs
    var body = new URLSearchParams({ action: 'jadev_get_favorites_products', nonce: jadevFavorites.nonce });
    ids.forEach(function (id) { body.append('product_ids[]', id); });

    fetch(jadevAjax.url, { method: 'POST', body: body })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            loading && loading.classList.add('hidden');

            if (!data.success || !data.data.products.length) {
                empty && empty.classList.remove('hidden');
                return;
            }

            data.data.products.forEach(function (p) {
                var card = document.createElement('div');
                card.className = 'flex flex-col gap-4';
                card.innerHTML =
                    '<a href="' + escHtml(p.product_url) + '" class="w-full block">' +
                        '<div class="relative w-full" style="padding-bottom:102.77%;">' +
                            (p.is_new ? '<div class="absolute left-4 top-4 z-2"><span class="rounded-full font-medium uppercase tracking-wide px-3 py-1.5 text-sm text-white bg-[rgb(28,28,26)]">New</span></div>' : '') +
                            '<div class="absolute right-3 top-3 z-2">' +
                                '<button class="favorite-btn w-9 h-9 flex items-center justify-center rounded-full bg-white/80 hover:bg-white text-red-500 transition-colors shadow-sm is-favorited cursor-pointer" data-product-id="' + p.product_id + '" type="button" aria-label="Remove from favorites" aria-pressed="true">' +
                                    '<svg class="fav-icon-outline w-5 h-5 hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                                    '<svg class="fav-icon-filled w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>' +
                                '</button>' +
                            '</div>' +
                            '<img src="' + escHtml(p.image_src) + '" alt="' + escHtml(p.image_alt) + '" width="1000" height="1034" loading="lazy" class="absolute inset-0 w-full h-full object-contain rounded-general">' +
                        '</div>' +
                    '</a>' +
                    '<div class="flex flex-col gap-2">' +
                        '<div class="richtext-prose"><h4>' + escHtml(p.card_title) + '</h4></div>' +
                        (p.price_html ? '<div class="text-body2">' + p.price_html + '</div>' : '') +
                        (p.card_description ? '<div class="richtext-prose text-eyebrow"><p>' + escHtml(p.card_description) + '</p></div>' : '') +
                    '</div>' +
                    '<div class="flex flex-wrap gap-3 mt-auto">' +
                        '<a href="' + escHtml(p.product_url) + '" class="button button--secondary">See Details</a>' +
                        '<a href="' + escHtml(p.add_to_cart_url) + '" class="button">Add to Cart</a>' +
                    '</div>';

                grid.appendChild(card);
            });

            grid.classList.remove('hidden');

            // Remove unfavorited cards in real time
            grid.addEventListener('favorites-updated', function (e) {
                var currentIds = e.detail || [];
                grid.querySelectorAll('.favorite-btn[data-product-id]').forEach(function (btn) {
                    var id = parseInt(btn.dataset.productId, 10);
                    if (!currentIds.includes(id)) {
                        btn.closest('.flex.flex-col').remove();
                    }
                });
                if (!grid.querySelectorAll('.flex.flex-col').length) {
                    grid.classList.add('hidden');
                    empty && empty.classList.remove('hidden');
                }
            });
        })
        .catch(function () {
            loading && loading.classList.add('hidden');
            empty   && empty.classList.remove('hidden');
        });

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});

// ── Sidebar Cart ─────────────────────────────────────────────────────────

function openSidebarCart() {
  var sidebar = document.getElementById('sidebar-cart');
  var overlay = document.getElementById('sidebar-cart-overlay');
  var toggle  = document.getElementById('sidebar-cart-toggle');
  if (!sidebar || !overlay) return;

  sidebar.setAttribute('aria-hidden', 'false');
  overlay.classList.remove('pointer-events-none');
  // Double rAF ensures the browser paints before the transition starts
  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      sidebar.classList.remove('translate-x-full');
      overlay.classList.remove('opacity-0');
      overlay.classList.add('opacity-100');
    });
  });
  document.body.classList.add('overflow-hidden');
  if (toggle) toggle.setAttribute('aria-expanded', 'true');
}

function closeSidebarCart() {
  var sidebar = document.getElementById('sidebar-cart');
  var overlay = document.getElementById('sidebar-cart-overlay');
  var toggle  = document.getElementById('sidebar-cart-toggle');
  if (!sidebar || !overlay) return;

  sidebar.classList.add('translate-x-full');
  overlay.classList.remove('opacity-100');
  overlay.classList.add('opacity-0');
  document.body.classList.remove('overflow-hidden');
  if (toggle) toggle.setAttribute('aria-expanded', 'false');

  setTimeout(function () {
    overlay.classList.add('pointer-events-none');
    sidebar.setAttribute('aria-hidden', 'true');
  }, 300);
}

document.addEventListener('DOMContentLoaded', function () {
  var toggle   = document.getElementById('sidebar-cart-toggle');
  var closeBtn = document.getElementById('sidebar-cart-close');
  var overlay  = document.getElementById('sidebar-cart-overlay');

  if (toggle)   toggle.addEventListener('click', openSidebarCart);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebarCart);
  if (overlay)  overlay.addEventListener('click', closeSidebarCart);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSidebarCart();
  });
});

// Auto-open sidebar when a product is added via WooCommerce AJAX
jQuery(document.body).on('added_to_cart', openSidebarCart);

// AJAX remove item from sidebar cart
jQuery(document.body).on('click', '#sidebar-cart .sidebar-cart-remove', function (e) {
  e.preventDefault();
  var $link = jQuery(this);
  var $item = $link.closest('li');
  $item.css({ opacity: 0.5, pointerEvents: 'none' });

  jQuery.post(jadevAjax.url, {
    action:        'jadev_remove_cart_item',
    nonce:         jadevAjax.cart_nonce,
    cart_item_key: $link.data('cart-item-key'),
  }, function (response) {
    if (response.success && response.data.fragments) {
      jQuery.each(response.data.fragments, function (selector, html) {
        jQuery(selector).replaceWith(html);
      });
    }
  });
});

// ── YITH info-button tooltip ──────────────────────────────────────────────
// Intercept in the capture phase so YITH's bubble-phase option-selection
// handler never sees the click when the user hits the info icon.
(function () {
    var activeTooltip = null;

    function removeTooltip() {
        if (activeTooltip) {
            activeTooltip.remove();
            activeTooltip = null;
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.icon-info[data-tooltip]');

        // Any click outside the button closes the tooltip
        if (!btn) {
            removeTooltip();
            return;
        }

        // Stop YITH's option-selection handler from seeing this click
        e.stopPropagation();
        e.stopImmediatePropagation();

        // Toggle: second click on same button closes it
        if (activeTooltip) {
            removeTooltip();
            return;
        }

        // Build tooltip and position it above the button via fixed coords
        var rect = btn.getBoundingClientRect();
        var tip  = document.createElement('div');
        tip.textContent = btn.dataset.tooltip;

        Object.assign(tip.style, {
            position:     'fixed',
            left:         Math.round(rect.left + rect.width / 2) + 'px',
            top:          Math.round(rect.top - 8) + 'px',
            transform:    'translate(-50%, -100%)',
            background:   'rgb(28,28,26)',
            color:        'rgb(244,244,244)',
            fontSize:     '0.75rem',
            lineHeight:   '1.4',
            padding:      '0.375rem 0.625rem',
            borderRadius: '0.25rem',
            whiteSpace:   'nowrap',
            pointerEvents:'none',
            zIndex:       '9999',
        });

        document.body.appendChild(tip);
        activeTooltip = tip;
    }, true); // ← capture phase
}());

// ── Cart Coupon ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var applyBtn = document.getElementById('cart-coupon-apply');
    if (!applyBtn) return; // not on cart page

    var input  = document.getElementById('cart-coupon-input');
    var msgEl  = document.getElementById('cart-coupon-message');

    function showMessage(text, isError) {
        msgEl.innerHTML = '<span class="' + (isError ? 'text-red-500' : 'text-green-600') + '">' + text + '</span>';
    }

    function updateSummary(summary) {
        var subtotalEl  = document.getElementById('cart-summary-subtotal');
        var totalEl     = document.getElementById('cart-summary-total');
        var discountRow = document.getElementById('cart-discount-row');
        var discountAmt = document.getElementById('cart-discount-amount');

        if (subtotalEl && summary.subtotal)  subtotalEl.innerHTML = summary.subtotal;
        if (totalEl    && summary.total)     totalEl.innerHTML    = summary.total;

        if (discountRow && discountAmt) {
            if (summary.discount_total) {
                discountAmt.innerHTML = '-' + summary.discount_total;
                discountRow.classList.remove('hidden');
            } else {
                discountRow.classList.add('hidden');
            }
        }

        renderAppliedCoupons(summary.applied_coupons || []);
    }

    function renderAppliedCoupons(coupons) {
        var container = document.getElementById('cart-applied-coupons');
        if (!container) return;

        container.innerHTML = coupons.map(function (c) {
            return '<div class="flex items-center justify-between py-2 text-sm text-green-700">' +
                '<span>' + c.label +
                ' <button class="coupon-remove-btn text-xs underline ml-2 hover:no-underline" data-code="' + c.code + '">Remove</button>' +
                '</span>' +
                '<span>-' + c.discount + '</span>' +
                '</div>';
        }).join('');
    }

    function removeCoupon(code) {
        var body = new URLSearchParams({
            action:      'jadev_remove_coupon',
            nonce:       jadevAjax.coupon_nonce,
            coupon_code: code,
        });

        fetch(jadevAjax.url, { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    showMessage(data.data.message, false);
                    updateSummary(data.data.summary);
                }
            })
            .catch(function () { showMessage('Something went wrong.', true); });
    }

    function applyCoupon() {
        var code = input.value.trim();
        if (!code) {
            showMessage('Please enter a coupon code.', true);
            return;
        }

        applyBtn.disabled    = true;
        applyBtn.textContent = 'Applying…';

        var body = new URLSearchParams({
            action:      'jadev_apply_coupon',
            nonce:       jadevAjax.coupon_nonce,
            coupon_code: code,
        });

        fetch(jadevAjax.url, { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                showMessage(data.data.message, !data.success);
                if (data.success) {
                    input.value = '';
                    updateSummary(data.data.summary);
                }
            })
            .catch(function () { showMessage('Something went wrong.', true); })
            .finally(function () {
                applyBtn.disabled    = false;
                applyBtn.textContent = 'Apply';
            });
    }

    applyBtn.addEventListener('click', applyCoupon);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') applyCoupon();
    });

    // Event delegation for dynamically-rendered remove buttons
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.coupon-remove-btn');
        if (btn) removeCoupon(btn.dataset.code);
    });
});