App.modules.estimation = (() => {
    let Form, Data = {}, Bonus;

    function gererEtapes() {
        const etape = etapeActive();
        console.log(etape.dataset.etape)
        const step = etape.dataset.etape;
        if (step == 1) {
            Data.personnes = Number(Form.querySelector('#personnes').value);
            setRfr(Data.personnes);
            setEtape(2);
        }
        if (step == 2) {
            Data.rfr = Number(Form.querySelector('#rfr').value)
            Data.categorie = calculCategorie(Data.personnes, Data.rfr)
            Data.nomCategorie = nomCategorie(Data.categorie)
            setEtape(3)
        }
        if (step == 3) {
            Data.classe = Form.querySelector('#classe').value
            App.modules.jauge.setClasse(Data.classe);
            Data.nomClasseEnergetique = nomClasseEnergetique(Data.classe)
            Data.messageClasseEnergetique = ''
            setEtape(5)
        }
        if (step == 4) {
            Data.annee = Number(Form.querySelector('#annee').value)
            Data.classe = classeparAnnee(Data.annee)
            App.modules.jauge.setClasse(Data.classe);
            Data.nomClasseEnergetique = nomClasseEnergetique(Data.classe)
            Data.messageClasseEnergetique = `Estimation de la classe énergétique effectuée à partir de l'année de construction. Cette information est purement théorique et ne peut remplacer un diagnostic.`
            setEtape(5)
        }
        if (step == 5) {
            if (Data.classe == 'A' || Data.classe == 'B') {
                setEtape(6)
            } else {
                App.modules.jauge.animer(Data.classe);
                Data.montantAides = calculAides(Data.categorie, Data.classe)
                setEtape(7)

            }
        }
        console.log(Data)

    }
    function setRfr(personnes) {
        const valeurs = calculCategorie(personnes)
        const options = ['<option disabled selected hidden>Choisir une tranche de revenus</option>'];
        let valeurPrec;
        valeurs.forEach((valeur, index) => {
            let lib, v;
            if (index == 0) {
                lib = 'Moins de ' + formatToEuro(valeur);
                v = valeur - 1;
            } else if (index == valeurs.length - 1) {
                lib = 'Plus de ' + formatToEuro(valeur);
                v = valeur + 1;
            } else {
                lib = 'Entre ' + formatToEuro(valeurPrec) + ' et ' + formatToEuro(valeur);
                v = valeur - 1;
            }
            options.push(`<option value="${v}">${lib}</option>`)
            valeurPrec = valeur;
        })
        Form.querySelector('#rfr').innerHTML = options.join('');
    }
    function calculAides(categorie, classe) {
        console.log({ categorie, classe })
        for (const ligne of estimation.aides) {
            if (ligne.categorie == categorie.toUpperCase()) {
                for (const item of ligne.montants) {
                    console.log(item)
                    if (item.classe_energetique == classe) {
                        return formatToEuro(item.montant)
                    }
                }

            }
        }
    }
    function nomCategorie(acronyme) {
        for (const categorie of estimation.categories) {
            if (acronyme && acronyme.toUpperCase() == categorie.acronyme) {
                return categorie.nom + ' (' + categorie.acronyme + ')';
            }
        }
    }

    function fermerEtape(nb) {
        let etape = Form.querySelector(`[data-etape="${nb}"]`);
        do {
            if (etape.dataset.active) {
                const required = etape.querySelectorAll('[required]')
                required.forEach(item => {
                    item.removeAttribute('required');
                    item.dataset.required = true;
                })

                delete etape.dataset.active
            }
            nb++;
            etape = Form.querySelector(`[data-etape="${nb}"]`);
        } while (etape);
    }

    function setEtape(nb, now = false) {
        charger(() => {
            fermerEtape(nb);
            const etape = Form.querySelector(`[data-etape="${nb}"]`);
            const required = etape.querySelectorAll('[data-required]')
            required.forEach(item => {
                item.setAttribute('required', true);
                delete item.dataset.required;
            })
            const items = etape.querySelectorAll('[data-parse]')
            items.forEach(item => {
                let code = 'item.innerHTML = `' + item.dataset.parse + '`';
                eval(code);
            })
            etape.dataset.active = true;
            Form.dataset.etape = etape.dataset.etape
            scrollToBottomOfElement(Form)
        }, Form, now)
    }

    function etapeActive() {

        const etapes = Form.querySelectorAll('[data-etape][data-active]');
        return etapes[etapes.length - 1];
    }
    function classeparAnnee(annee) {
        if (annee >= 2020) {
            return 'B';
        }

        if (annee >= 2012) {
            return 'C';
        }

        if (annee >= 2005) {
            return 'D';
        }

        if (annee >= 1990) {
            return 'E';
        }

        if (annee >= 1974) {
            return 'F';
        }

        return 'G'
    }
    function nomClasseEnergetique(lettre) {
        for (const classe of estimation.classes_energetiques) {
            if (classe.lettre == lettre) {
                return classe.lettre + ': ' + classe.description + '<br><small>' + classe.nom + '</small>';
            }
        }
    }
    function calculCategorie(personnes, rfr = false) {

        for (let i = 1; i <= personnes; i++) {
            let last = estimation.donnees[estimation.donnees.length - 1];
            if (typeof estimation.donnees[i - 1] == 'undefined') {
                // console.log('Ajout de ', i)
                const nouvelle = Object.assign({}, last)
                for (const [categorie, valeur] of Object.entries(Bonus)) {
                    if (categorie == 'nombre_de_personnes') {
                        nouvelle[categorie] = i;
                    } else {
                        nouvelle[categorie] += Bonus[categorie];
                    }
                }
                estimation.donnees.push(nouvelle)
                last = nouvelle

            }
        }
        if (!rfr) {
            out = [];
            for (const ligne of estimation.donnees) {
                if (ligne.nombre_de_personnes == personnes) {
                    for (const [categorie, valeur] of Object.entries(ligne)) {
                        if (categorie == 'nombre_de_personnes') continue;
                        out.push(valeur)
                    }
                }
            };
            return out;
        }
        for (const ligne of estimation.donnees) {
            if (!ligne) continue;
            if (ligne.nombre_de_personnes == personnes) {
                for (const [categorie, valeur] of Object.entries(ligne)) {
                    if (categorie == 'nombre_de_personnes') continue;
                    if (rfr <= valeur) {
                        return categorie
                    } else if (categorie == 'sup') {
                        return categorie;
                    }
                }
            }
        }
    }


    function gestionEvenements() {

        Form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (Form.ariaBusy == 'true') return;
            Form.ariaBusy = 'true';
            gererEtapes()
        });

        ['input', 'keyup'].forEach(eventName => Form.querySelector('#annee').addEventListener(eventName, e => {
            const annee = e.target.value;
            console.log(annee)
            if (annee.length == 4) {
                setEtape(4, true);
                Form.requestSubmit()
            }
        }));

        Form.querySelector('#personnes').addEventListener('input', e => {
            setRfr(e.target.value)
        })
        Form.querySelector('#rfr').addEventListener('input', e => {
            Form.requestSubmit()
        })

        Form.querySelector('#classe').addEventListener('input', e => {
            fermerEtape(4)
            Form.querySelector('#annee').value = '';
            if (e.target.value == 'nsp') {
                setEtape(4, true);
            } else {
                Data.classe = e.target.value;
                App.modules.jauge.setClasse(Data.classe);
                gererEtapes()
            }
        })
        Form.querySelector('[data-action="recommencer"]').addEventListener('click', e => {
            fermerEtape(2)
            Data = {}
            Form.querySelector('#classe').selectedIndex = 0
            Form.querySelector('#annee').value = ''
            Form.querySelector('#rfr').value = ''
            Form.querySelector('#personnes').value = 2
            delete Form.dataset.etape;
        })
    }
    return {
        calculAides,
        ready() {
            Form = document.querySelector('#estimation');
            if (!Form) return;

            Bonus = estimation.donnees[estimation.donnees.length - 1];
            if (Bonus.nombre_de_personnes == '*') {
                estimation.donnees.pop();
            }
            gestionEvenements()


        }
    }
})();