$("#nav-main > ul > li").hover(
  function () {
    $(this).find("#header-submenu-container").css("display", "block");
  },
  function () {
    $(this).find("#header-submenu-container").css("display", "none");
  }
);

$(".slider-button-pause").on("click", function () {
  const video = $("video").get(0);

  if (video && !video.paused) {
    video.pause();
  }
});
