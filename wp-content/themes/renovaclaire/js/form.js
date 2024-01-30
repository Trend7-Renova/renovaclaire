App.modules.form = (() => {
    let Aides;
    return {
        ready() {
            Aides = document.querySelectorAll('form fieldset [data-action="aide"]');
            if (!Aides) return;

            Aides.forEach(Aide => Aide.addEventListener('click', e => {
                const fieldset = e.target.closest('fieldset');
                const modale = fieldset.querySelector('dialog');
                modale.showModal();
            }))
        }
    }
})();