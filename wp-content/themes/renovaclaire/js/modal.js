App.modules.modal = (() => {
    let Modales;
    function handleOptions(options) {

        const then = options.then || false;
        if (then) {
            if (then == 'reload') {
                document.location.reload()
            } else if (isCallable(then)) {
                then()
            }
        }

        const go = options.go || false;
        if (go) {
            return window.open(go, '_self');
        }

    }
    return {
        ready() {
            Modales = document.querySelectorAll('dialog:not(.modal');
            if (!Modales) return;
            document.addEventListener("keyup", (e) => {
                const key = e.key;
                if (key == 'Escape') {
                    Modales.forEach(Modale => Modale.close());
                }
            });

            Modales.forEach(Modale => {
                Modale.addEventListener('click', e => {
                    if (e.target == Modale) {
                        setTimeout(() => Modale.close(), 100)
                    }
                })
            })

        },
        alert(titre, message = '', options = {}) {
            let modal = document.querySelector('.modal.alert');
            if (!modal) {
                modal = document.createElement('dialog');
                modal.classList.add('modal', 'alert')
                modal.innerHTML = `<div></div><div class="boutons"><button class="bouton">Ok</button></div>`;
                document.body.append(modal);
            } else {
                if (modal.open) {
                    return setTimeout(() => this.alert(titre, message, options), 1000)
                };
            }
            modal.addEventListener('click', e => {
                const bouton = e.target.closest('.bouton');
                if (!bouton) return;
                handleOptions(options)
                modal.close()
            }, { once: true })

            document.addEventListener("keyup", (e) => {
                const key = e.key;
                if (key == 'Enter') {
                    handleOptions(options)
                    modal.close()
                }
            }, { once: true });

            modal.querySelector('div').innerHTML = `<h2>${titre}</h2><p>${message.replaceAll('\n', '<br><br>')}</p>`;
            modal.showModal()
        }
    }
})()

Modal = App.modules.modal;