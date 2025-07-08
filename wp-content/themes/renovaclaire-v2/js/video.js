App.modules.video = (() => {
    let Video, Play, Player, Modale;

    /**
     * Définit le rapport d'aspect d'un élément iframe basé sur ses attributs width et height.
     * @param {HTMLElement} iframe - L'élément iframe dont le rapport d'aspect doit être défini.
     */
    function getIframeAspectRatio(iframe) {
        if (iframe.style.aspectRatio) return;
        const width = iframe.getAttribute('width');
        const height = iframe.getAttribute('height');
        const aspectRatio = width / height;
        iframe.style.aspectRatio = `${aspectRatio}`;
        return aspectRatio;
    }

    return {
        ready() {
            Video = document.querySelector('.video');
            if (!Video) return;

            Play = Video.querySelector('[data-action="play"]');
            Modale = Video.querySelector('dialog');
            Player = Video.querySelector('video');
            Iframe = Video.querySelector('iframe');
            Video.addEventListener('click', () => {
                Modale.showModal();
                if (Player) Player.play()
                if (Iframe) {
                    const ar = getIframeAspectRatio(Iframe);
                    Video.querySelector('div').style.aspectRatio = ar;
                    const separator = Iframe.src.includes('?') ? '&' : '?';
                    Iframe.src += `${separator}autoplay=1`;
                }

                Modale.addEventListener('close', e => {
                    if (Player) Player.pause()
                    if (Iframe) Iframe.src = Iframe.src.replace('autoplay=1', '')
                }, { once: true })
            })
        }

    }
})();