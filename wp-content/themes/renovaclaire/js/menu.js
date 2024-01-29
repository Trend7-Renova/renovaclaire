App.modules.menu = (() => {
    let Menu, Burger;

    function gererSwipe(left, right, delta = 150) {
        let touchStartX = 0;
        let touchEndX = 0;

        function handleTouchStart(evt) {
            touchStartX = evt.touches[0].clientX;
        }

        function handleTouchMove(evt) {
            touchEndX = evt.touches[0].clientX;
        }

        function end() {
            touchStartX = 0;
            touchEndX = 0;
        }

        function handleTouchEnd() {
            console.log(touchStartX, touchEndX)
            if (touchStartX && touchEndX) {
                if (touchStartX - touchEndX > delta) { // Swipe left
                    console.log('Swiped left');
                    left()
                    end()
                } else if (touchEndX - touchStartX > delta) { // Swipe right
                    console.log('Swiped right');
                    right()
                    end()
                }
            }
        }

        document.addEventListener('touchstart', handleTouchStart, false);
        document.addEventListener('touchmove', handleTouchMove, false);
        document.addEventListener('touchend', handleTouchEnd, false);

    }
    function fermerMenu() {
        document.body.classList.remove('menu-mobile-ouvert')
    }
    function ouvrirMenu() {
        document.body.classList.add('menu-mobile-ouvert')
        setTimeout(() => {

            Menu.addEventListener('click', e => {
                if (e.target.id == 'menu') {
                    fermerMenu()
                }
            }, { once: true })

            document.body.addEventListener('click', e => {
                console.log(e.target)
                if (e.target.closest('#menu')) return;
                fermerMenu()
            }, { once: true })
        }, 100)
    }
    return {
        ready() {
            Menu = document.querySelector('#menu');
            if (!Menu) {
                return;
            }
            Burger = document.querySelector('.burger[data-action="ouvrir-menu"]')
            Burger.addEventListener('click', e => {
                ouvrirMenu();
            })

            gererSwipe(fermerMenu, ouvrirMenu);
        }
    }
})();