App.modules.fairy = (() => {

    return {
        ready() {
            items = document.querySelectorAll('.fairy');
            if (!items) return;

            items.forEach(item => {
                setInterval(() => {
                    animatedShuffleChildren(item.querySelector('article>div'));
                }, 30000);
            });
        }

    }
})();