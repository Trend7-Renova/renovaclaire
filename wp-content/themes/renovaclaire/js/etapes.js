App.modules.etapes = (() => {

    return {
        ready() {
            const items = document.querySelectorAll('.etapes ul');

            if (!items) return;

            items.forEach(item => {
                // item.addEventListener('mouseover', () => {
                //     console.log('ok')
                //     item.dataset.mouseover = true;
                // })
                // item.addEventListener('mouseout', () => {
                //     item.dataset.mouseover = false;
                // })
                // setInterval(() => {
                //     if (item.dataset.mouseover === true) return;
                //     const li = item.querySelector('li.selected');
                //     if (li) {
                //         li.classList.remove('selected');
                //         const next = li.nextElementSibling || item.querySelector('li')
                //         console.log(next);
                //         next.classList.add('selected')
                //     } else {
                //         item.querySelector('li').classList.add('selected');
                //     }
                // }, 2000)
            })
        }

    }
})();