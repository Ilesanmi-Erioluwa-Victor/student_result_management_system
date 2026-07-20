document.addEventListener('DOMContentLoaded', function () {
    const printBtn = document.getElementById('print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    const deleteLinks = document.querySelectorAll('.delete-confirm');
    deleteLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    const alertBoxes = document.querySelectorAll('.alert');
    alertBoxes.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 5000);
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
            const btns = this.querySelectorAll('button[type="submit"]');
            btns.forEach(function (btn) {
                btn.disabled = true;
                btn.classList.add('loading');
            });
        });
    });
});
