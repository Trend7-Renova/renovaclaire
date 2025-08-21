App.modules.scroll = (() => {

    return {
        ready() {
            window.addEventListener('scroll', e => {
                if (window.scrollY > 0) {
                    document.body.dataset.scrolled = true;
                } else {
                    document.body.dataset.scrolled = false;
                }
            })
        }

    }
})();