const App = {
    modules: {}
};

document.addEventListener("DOMContentLoaded", () => {
    document.body.dataset.js = true;
    for (const module in App.modules) {
        if (App.modules[module].ready) {
            App.modules[module].ready();
        }
    }
});

window.addEventListener("load", () => {
    document.body.dataset.loaded = true;
    for (const module in App.modules) {
        if (App.modules[module].load) {
            App.modules[module].load();
        }
    }
});

function scrollToBottomOfElement(element) {
    const elementRect = element.getBoundingClientRect();
    const elementBottom = elementRect.bottom;
    const viewportHeight = window.innerHeight;
    if (elementBottom > viewportHeight) {
        const offset = elementBottom - viewportHeight;
        window.scrollBy({
            top: offset,
            behavior: "smooth"
        });
    }
}

function scrollToMiddle(element) {
    element.scrollIntoView({
        behavior: "smooth",
        block: "center"
    });
}

function isCallable(variable) {
    return typeof variable === "function";
}

function charger(code, load, now = false) {
    if (now) {
        return code();
    }
    const delay = .5 + Math.random() * 1.3;
    if (load) {
        load.ariaBusy = "true";
    }
    setTimeout(() => {
        code();
        if (load) {
            load.ariaBusy = "false";
        }
    }, delay * 1e3);
}

function formatToEuro(number, decimal = true) {
    const formatter = new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "EUR"
    });
    let ret = formatter.format(number);
    if (!decimal) {
        ret = ret.replaceAll(",00", "");
    }
    return ret;
}

App.modules.heroLarge = (() => {
    function Heros() {
        return document.querySelectorAll(".hero-large");
    }
    return {
        ready() {
            if (!Heros()) return;
            Heros().forEach(hero => {
                if (hero.querySelectorAll(".photos figure").length < 2) return;
                console.log({
                    hero: hero
                });
                setInterval(() => {
                    const first = hero.querySelector(".photos figure");
                    const photo = hero.querySelector(".photos figure.selected") || first;
                    const next = photo.nextElementSibling || first;
                    hero.querySelectorAll(".photos figure.selected")?.forEach(item => item.classList.remove("selected"));
                    next.classList.add("selected");
                }, 3e3);
            });
        }
    };
})();

App.modules.video = (() => {
    let Video, Play, Player, Modale;
    function getIframeAspectRatio(iframe) {
        if (iframe.style.aspectRatio) return;
        const width = iframe.getAttribute("width");
        const height = iframe.getAttribute("height");
        const aspectRatio = width / height;
        iframe.style.aspectRatio = `${aspectRatio}`;
        return aspectRatio;
    }
    return {
        ready() {
            Video = document.querySelector(".video");
            if (!Video) return;
            Play = Video.querySelector('[data-action="play"]');
            Modale = Video.querySelector("dialog");
            Player = Video.querySelector("video");
            Iframe = Video.querySelector("iframe");
            Video.addEventListener("click", () => {
                Modale.showModal();
                if (Player) Player.play();
                if (Iframe) {
                    const ar = getIframeAspectRatio(Iframe);
                    Video.querySelector("div").style.aspectRatio = ar;
                    const separator = Iframe.src.includes("?") ? "&" : "?";
                    Iframe.src += `${separator}autoplay=1`;
                }
                Modale.addEventListener("close", e => {
                    if (Player) Player.pause();
                    if (Iframe) Iframe.src = Iframe.src.replace("autoplay=1", "");
                }, {
                    once: true
                });
            });
        }
    };
})();

App.modules.modal = (() => {
    let Modales;
    function handleOptions(options) {
        const then = options.then || false;
        if (then) {
            if (then == "reload") {
                document.location.reload();
            } else if (isCallable(then)) {
                then();
            }
        }
        const go = options.go || false;
        if (go) {
            return window.open(go, "_self");
        }
    }
    return {
        ready() {
            Modales = document.querySelectorAll("dialog:not(.modal");
            if (!Modales) return;
            document.addEventListener("keyup", e => {
                const key = e.key;
                if (key == "Escape") {
                    Modales.forEach(Modale => Modale.close());
                }
            });
            Modales.forEach(Modale => {
                Modale.addEventListener("click", e => {
                    if (e.target == Modale) {
                        setTimeout(() => Modale.close(), 100);
                    }
                });
            });
        },
        alert(titre, message = "", options = {}) {
            let modal = document.querySelector(".modal.alert");
            if (!modal) {
                modal = document.createElement("dialog");
                modal.classList.add("modal", "alert");
                modal.innerHTML = `<div></div><div class="boutons"><button class="bouton">Ok</button></div>`;
                document.body.append(modal);
            } else {
                if (modal.open) {
                    return setTimeout(() => this.alert(titre, message, options), 1e3);
                }
            }
            modal.addEventListener("click", e => {
                const bouton = e.target.closest(".bouton");
                if (!bouton) return;
                handleOptions(options);
                modal.close();
            }, {
                once: true
            });
            document.addEventListener("keyup", e => {
                const key = e.key;
                if (key == "Enter") {
                    handleOptions(options);
                    modal.close();
                }
            }, {
                once: true
            });
            modal.querySelector("div").innerHTML = `<h2>${titre}</h2><p>${message.replaceAll("\n", "<br><br>")}</p>`;
            modal.showModal();
        }
    };
})();

Modal = App.modules.modal;

App.modules.jauge = (() => {
    function lettersBefore(letter) {
        const startCharCode = "A".charCodeAt(0);
        const endCharCode = letter.toUpperCase().charCodeAt(0);
        const lettersArray = [];
        for (let i = endCharCode; i >= startCharCode; i--) {
            lettersArray.push(String.fromCharCode(i).toLocaleLowerCase());
        }
        return lettersArray;
    }
    return {
        lettersBefore: lettersBefore,
        animer(lettre) {
            this.setClasse(lettre);
            const delai = 100;
            let time = 0;
            for (const l of lettersBefore(lettre)) {
                time += delai;
                setTimeout(() => {
                    console.log(l);
                    this.setClasse(l);
                }, time);
            }
        },
        setClasse(lettre) {
            lettre = lettre.toLocaleLowerCase();
            const jauge = document.querySelector(".jauge");
            const jaugeIn = jauge.querySelector("div");
            const spanLettre = jaugeIn.querySelector("span");
            jaugeIn.setAttribute("style", "--bg:var(--" + lettre + "); --color:var(--color-" + lettre + "); --width:var(--w-" + lettre + ")");
            console.log(lettre);
            spanLettre.innerHTML = lettre.toUpperCase();
        }
    };
})();

App.modules.estimation = (() => {
    let Form, Data = {}, Bonus, Personnes, Rfr, Classe, Annee;
    function gererEtapes() {
        const etape = etapeActive();
        console.log(etape.dataset.etape);
        const step = etape.dataset.etape;
        if (step == 1) {
            Data.personnes = Number(Personnes.value);
            setRfr(Data.personnes);
            setEtape(2);
        }
        if (step == 2) {
            Data.rfr = Number(Rfr.value);
            Data.categorie = calculCategorie(Data.personnes, Data.rfr);
            Data.nomCategorie = nomCategorie(Data.categorie);
            setEtape(3);
        }
        if (step == 3) {
            Data.classe = Classe.value;
            Data.nomClasseEnergetique = nomClasseEnergetique(Data.classe);
            Data.messageClasseEnergetique = "";
            afficherResultats();
        }
        if (step == 4) {
            Data.annee = Number(Annee.value);
            Data.classe = classeparAnnee(Data.annee);
            Data.nomClasseEnergetique = nomClasseEnergetique(Data.classe);
            Data.messageClasseEnergetique = `Estimation de la classe énergétique effectuée à partir de l'année de construction. Cette information est purement théorique et ne peut remplacer un diagnostic.`;
            afficherResultats();
        }
        console.log(Data);
    }
    function afficherResultats() {
        Data.montantAides = calculAides(Data.categorie, Data.classe);
        const resultat = Form.querySelector(".resultat");
        resultat.dataset.classe = Data.classe;
        resultat.dataset.aides = Data.montantAides ? true : false;
        parseAll(resultat);
        resultat.classList.remove("hidden");
        scrollToMiddle(resultat);
    }
    function setRfr(personnes) {
        const valeurs = calculCategorie(personnes);
        let valeurPrec;
        const boutons = [];
        valeurs.forEach((valeur, index) => {
            let lib, v;
            if (index == 0) {
                lib = "Moins de " + formatToEuro(valeur, false);
                v = valeur - 1;
            } else if (index == valeurs.length - 1) {
                lib = "Plus de " + formatToEuro(valeur, false);
                v = valeur + 1;
            } else {
                lib = "Entre " + formatToEuro(valeurPrec, false) + " et " + formatToEuro(valeur, false);
                v = valeur - 1;
            }
            boutons.push(`<button class="bouton alt" type="button" data-rfr="${v}">${lib}</bouton>`);
            valeurPrec = valeur;
        });
        Form.querySelector("#boutons-rfr").innerHTML = boutons.join("");
    }
    function calculAides(categorie, classe) {
        console.log({
            categorie: categorie,
            classe: classe
        });
        for (const ligne of estimation.aides) {
            if (ligne.categorie == categorie.toUpperCase()) {
                for (const item of ligne.montants) {
                    console.log(item);
                    if (item.classe_energetique == classe) {
                        return formatToEuro(item.montant, false);
                    }
                }
            }
        }
    }
    function nomCategorie(acronyme) {
        for (const categorie of estimation.categories) {
            if (acronyme && acronyme.toUpperCase() == categorie.acronyme) {
                return categorie.nom + " (" + categorie.acronyme + ")";
            }
        }
    }
    function fermerEtape(nb) {
        let etape = Form.querySelector(`[data-etape="${nb}"]`);
        if (!etape) return;
        do {
            if (etape.dataset.active) {
                delete etape.dataset.active;
            }
            nb++;
            etape = Form.querySelector(`[data-etape="${nb}"]`);
        } while (etape);
    }
    function setEtape(nb, later = false) {
        charger(() => {
            fermerEtape(nb);
            const etape = Form.querySelector(`[data-etape="${nb}"]`);
            parseAll(etape);
            etape.dataset.active = true;
            Form.dataset.etape = etape.dataset.etape;
            scrollToMiddle(lastStep());
        }, null, !later);
    }
    function parseAll(obj) {
        const items = obj.querySelectorAll("[data-parse]");
        items.forEach(item => {
            let code = "item.innerHTML = `" + item.dataset.parse + "`";
            eval(code);
        });
    }
    function lastStep() {
        const etapes = Form.querySelectorAll("[data-etape][data-active]");
        if (etapes.length > 0) {
            return etapes[etapes.length - 1];
        }
    }
    function etapeActive() {
        const etapes = Form.querySelectorAll("[data-etape][data-active]");
        return etapes[etapes.length - 1];
    }
    function classeparAnnee(annee) {
        for (const item of estimation.classes_par_annees) {
            console.log(item);
            if (annee >= item.annee) {
                return item.classe;
            } else if (!item.annee) {
                return item.classe;
            }
        }
    }
    function nomClasseEnergetique(lettre) {
        for (const classe of estimation.classes_energetiques) {
            if (classe.lettre == lettre) {
                return classe.lettre + ": " + classe.description + "<br><small>" + classe.nom + "</small>";
            }
        }
    }
    function calculCategorie(personnes, rfr = false) {
        for (let i = 1; i <= personnes; i++) {
            let last = estimation.donnees[estimation.donnees.length - 1];
            if (typeof estimation.donnees[i - 1] == "undefined") {
                const nouvelle = Object.assign({}, last);
                for (const [ categorie, valeur ] of Object.entries(Bonus)) {
                    if (categorie == "nombre_de_personnes") {
                        nouvelle[categorie] = i;
                    } else {
                        nouvelle[categorie] += Bonus[categorie];
                    }
                }
                estimation.donnees.push(nouvelle);
                last = nouvelle;
            }
        }
        if (!rfr) {
            out = [];
            for (const ligne of estimation.donnees) {
                if (ligne.nombre_de_personnes == personnes) {
                    for (const [ categorie, valeur ] of Object.entries(ligne)) {
                        if (categorie == "nombre_de_personnes") continue;
                        out.push(valeur);
                    }
                }
            }
            return out;
        }
        for (const ligne of estimation.donnees) {
            if (!ligne) continue;
            if (ligne.nombre_de_personnes == personnes) {
                for (const [ categorie, valeur ] of Object.entries(ligne)) {
                    if (categorie == "nombre_de_personnes") continue;
                    if (rfr <= valeur) {
                        return categorie;
                    } else if (categorie == "sup") {
                        return categorie;
                    }
                }
            }
        }
    }
    function gestionEvenements() {
        Form.addEventListener("submit", e => {
            e.preventDefault();
            gererEtapes();
        });
        [ "input", "keyup" ].forEach(eventName => Annee.addEventListener(eventName, e => {
            const annee = e.target.value;
            console.log(annee);
            if (annee.length == 4) {
                setEtape(4);
                Form.requestSubmit();
            }
        }));
        Form.querySelector("#boutons-rfr").addEventListener("click", e => {
            const bouton = e.target.closest("[data-rfr]");
            if (!bouton) return;
            const prec = Form.querySelector("[data-rfr][data-selected]");
            if (prec) {
                fermerEtape(3);
                delete prec.dataset.selected;
            }
            const rfr = bouton.dataset.rfr;
            bouton.dataset.selected = true;
            Rfr.value = rfr;
            Form.requestSubmit();
        });
        Form.querySelectorAll("[data-personnes]").forEach(bouton => bouton.addEventListener("click", e => {
            const personnes = e.target.dataset.personnes;
            const prec = Form.querySelector("[data-personnes][data-selected]");
            if (prec) {
                fermerEtape(2);
                delete prec.dataset.selected;
            }
            e.target.dataset.selected = true;
            Personnes.value = personnes;
            Form.requestSubmit();
        }));
        Form.querySelectorAll("[data-classe]").forEach(bouton => bouton.addEventListener("click", e => {
            const classe = e.target.dataset.classe;
            const prec = Form.querySelector("[data-classe][data-selected]");
            if (prec) {
                fermerEtape(4);
                delete prec.dataset.selected;
            }
            e.target.dataset.selected = true;
            Classe.value = classe;
            if (classe == "nsp") {
                setEtape(4);
            } else {
                Form.requestSubmit();
            }
        }));
        Form.querySelectorAll("[data-annee]").forEach(bouton => bouton.addEventListener("click", e => {
            const annee = e.target.dataset.annee;
            const prec = Form.querySelector("[data-annee][data-selected]");
            if (prec) {
                fermerEtape(5);
                delete prec.dataset.selected;
            }
            e.target.dataset.selected = true;
            Annee.value = annee;
            Form.requestSubmit();
        }));
        Personnes.addEventListener("input", e => {
            setRfr(e.target.value);
        });
    }
    return {
        calculAides: calculAides,
        ready() {
            Form = document.querySelector("#estimation");
            if (!Form) return;
            Personnes = Form.querySelector("#personnes");
            Rfr = Form.querySelector("#rfr");
            Classe = Form.querySelector("#classe");
            Annee = Form.querySelector("#annee");
            Bonus = estimation.donnees[estimation.donnees.length - 1];
            if (Bonus.nombre_de_personnes == "*") {
                estimation.donnees.pop();
            }
            gestionEvenements();
        }
    };
})();

App.modules.contact = (() => {
    let Form, Fields, AutoSaveFields, valeurs = {};
    function erreur() {
        const email = document.querySelector('a[href*="mailto:"]').outerHTML;
        Modal.alert("Erreur", `Impossible d'envoyer le message pour l'instant.\nNous vous invitions à essayer à nouveau dans quelques minutes, ou à nous contacter directement par mail à ${email}.`);
    }
    function activerAutosave() {
        chargerAutosave();
        AutoSaveFields.forEach(Field => {
            Field.addEventListener("input", e => {
                valeurs[Field.id] = Field.value;
                console.log(valeurs);
                localStorage.setItem("valeurs", JSON.stringify(valeurs));
            });
        });
    }
    function chargerAutosave() {
        valeurs = JSON.parse(localStorage.getItem("valeurs") || "{}");
        for (const [ id, valeur ] of Object.entries(valeurs)) {
            document.getElementById(id).value = valeur;
        }
    }
    return {
        ready() {
            Form = document.querySelector("form#contact");
            if (!Form) return;
            Form.addEventListener("submit", e => {
                e.preventDefault();
                const payload = {};
                Fields.forEach(Field => {
                    payload[Field.id] = Field.value;
                });
                fetch("/wp-json/custom/v1/contact", {
                    method: "POST",
                    body: JSON.stringify(payload)
                }).then(response => response.json()).then(data => {
                    localStorage.setItem("valeurs", "");
                    if (data) {
                        Modal.alert("Message envoyé", "Nous répondrons à votre message le plus rapidement possible.\nMerci !", {
                            go: "/contact?ok"
                        });
                    } else {
                        erreur();
                    }
                }).catch(() => {
                    erreur();
                });
            });
            AutoSaveFields = Form.querySelectorAll("[data-autosave]");
            activerAutosave();
            Fields = Form.querySelectorAll("[id]");
        }
    };
})();

App.modules.form = (() => {
    let Aides;
    return {
        ready() {
            Aides = document.querySelectorAll('form fieldset [data-action="aide"]');
            if (!Aides) return;
            Aides.forEach(Aide => Aide.addEventListener("click", e => {
                const fieldset = e.target.closest("fieldset");
                const modale = fieldset.querySelector("dialog");
                modale.showModal();
            }));
        }
    };
})();

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
            console.log(touchStartX, touchEndX);
            if (touchStartX && touchEndX) {
                if (touchStartX - touchEndX > delta) {
                    console.log("Swiped left");
                    left();
                    end();
                } else if (touchEndX - touchStartX > delta) {
                    console.log("Swiped right");
                    right();
                    end();
                }
            }
        }
        document.addEventListener("touchstart", handleTouchStart, false);
        document.addEventListener("touchmove", handleTouchMove, false);
        document.addEventListener("touchend", handleTouchEnd, false);
    }
    function fermerMenu() {
        document.body.classList.remove("menu-mobile-ouvert");
    }
    function ouvrirMenu() {
        document.body.classList.add("menu-mobile-ouvert");
        setTimeout(() => {
            Menu.addEventListener("click", e => {
                if (e.target.id == "menu") {
                    fermerMenu();
                }
            }, {
                once: true
            });
            document.body.addEventListener("click", e => {
                console.log(e.target);
                if (e.target.closest("#menu")) return;
                fermerMenu();
            }, {
                once: true
            });
        }, 100);
    }
    return {
        ready() {
            Menu = document.querySelector("#menu");
            if (!Menu) {
                return;
            }
            Burger = document.querySelector('.burger[data-action="ouvrir-menu"]');
            Burger.addEventListener("click", e => {
                ouvrirMenu();
            });
            gererSwipe(fermerMenu, ouvrirMenu);
        }
    };
})();