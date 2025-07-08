App.modules.heroLarge = (() => {
    function Heros() {
        return document.querySelectorAll('.hero-large');
    }

    return {
        ready() {
            if (!Heros()) return;


            Heros().forEach(hero => {
                if (hero.querySelectorAll('.photos figure').length < 2) return;
                console.log({ hero })
                setInterval(() => {
                    const first = hero.querySelector('.photos figure');
                    const photo = hero.querySelector('.photos figure.selected') || first;
                    const next = photo.nextElementSibling || first;
                    hero.querySelectorAll('.photos figure.selected')?.forEach(item => item.classList.remove('selected'))
                    next.classList.add('selected');
                }, 3000)
            })
        }
    }
})();