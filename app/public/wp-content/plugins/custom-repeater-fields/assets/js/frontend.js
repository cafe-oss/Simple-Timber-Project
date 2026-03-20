document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.crf-dropdown-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var item = this.closest('.crf-dropdown-item');
            var body = item.querySelector('.crf-dropdown-body');
            var icon = item.querySelector('.crf-dropdown-icon');
            var isOpen = body.style.maxHeight && body.style.maxHeight !== '0px';

            if (isOpen) {
                body.style.maxHeight = '0px';
                icon.style.transform = 'rotate(0deg)';
            } else {
                body.style.maxHeight = body.scrollHeight + 'px';
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });
});
