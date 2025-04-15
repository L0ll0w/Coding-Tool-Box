document.addEventListener('DOMContentLoaded', function () {
    const modalToggleButtons = document.querySelectorAll('[data-modal-toggle]');
    modalToggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            console.log("ça clique ou quoi");
            const modalSelector = button.getAttribute('data-modal-toggle'); // par ex. "#qcmModal"
            const modalEl = document.querySelector(modalSelector);

            const options = {
                backdropClass: 'transition-all duration-300 fixed inset-0 bg-gray-900 opacity-25',
                backdrop: true,
                disableScroll: true,
                persistent: true
            };

            // Initialize object
            const modal = new KTModal(modalEl, options);
        });
    });
});
