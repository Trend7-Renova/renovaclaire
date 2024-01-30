App.modules.video = (() => {
    let Video, Play, Player, Modale;


    return {
        ready() {
            Video = document.querySelector('.video');
            if (!Video) return;

            Play = Video.querySelector('[data-action="play"]');
            Modale = Video.querySelector('dialog');
            Player = Video.querySelector('video');
            Play.addEventListener('click', () => {
                Modale.showModal();
                Player.play()
                Modale.addEventListener('close', e => {
                    Player.pause()
                }, { once: true })
            })
        }

    }
})();