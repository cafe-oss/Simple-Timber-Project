$('#nav-main > ul > li').hover(
  function () {
    $(this).find('#header-submenu-container').css('display', 'block');
  },
  function () {
    $(this).find('#header-submenu-container').css('display', 'none');
  }
);