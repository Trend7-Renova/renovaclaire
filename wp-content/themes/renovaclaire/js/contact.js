App.modules.contact = (() => {
    let Form, Fields, AutoSaveFields, valeurs = {}

    function erreur() {
        const email = document.querySelector('a[href*="mailto:"]').outerHTML;
        Modal.alert('Erreur', `Impossible d'envoyer le message pour l'instant.\nNous vous invitions à essayer à nouveau dans quelques minutes, ou à nous contacter directement par mail à ${email}.`)
    }
    function activerAutosave() {
        chargerAutosave();
        AutoSaveFields.forEach(Field => {
            Field.addEventListener('input', e => {
                valeurs[Field.id] = Field.value;
                console.log(valeurs)
                localStorage.setItem('valeurs', JSON.stringify(valeurs));
            })
        })
    }
    function chargerAutosave() {
        valeurs = JSON.parse(localStorage.getItem('valeurs') || '{}');
        for (const [id, valeur] of Object.entries(valeurs)) {
            document.getElementById(id).value = valeur;
        }
    }
    return {
        ready() {
            Form = document.querySelector('form#contact');
            if (!Form) return;

            Form.addEventListener('submit', e => {
                e.preventDefault();
                const payload = {}
                Fields.forEach(Field => {
                    payload[Field.id] = Field.value;
                })
                fetch('/wp-json/custom/v1/contact', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                }).then(response => response.json()).then(data => {
                    localStorage.setItem('valeurs', '');
                    if (data) {
                        Modal.alert('Message envoyé', 'Nous répondrons à votre message le plus rapidement possible.\nMerci !', { go: '/contact?ok' })
                    } else {
                        erreur()
                    }
                }).catch(() => {
                    erreur()
                })


            })
            AutoSaveFields = Form.querySelectorAll('[data-autosave]')
            activerAutosave()
            Fields = Form.querySelectorAll('[id]')
        }

    }
})();