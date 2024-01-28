App.modules.contact = (() => {
    let Form, Fields, AutoSaveFields, valeurs = {}

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

                        alert(`Merci. Votre message a été envoyé.`);
                        document.location.reload();
                    } else {
                        alert(`Impossible d'envoyer le message`);
                    }
                }).catch(() => {
                    alert(`Impossible d'envoyer le message`);
                })


            })
            AutoSaveFields = Form.querySelectorAll('[data-autosave]')
            activerAutosave()
            Fields = Form.querySelectorAll('[id]')
        }

    }
})();