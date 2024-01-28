App.modules.jauge = (() => {
    /**
     * Generates an array of letters in descending order up to the given letter.
     * @param {string} letter - A single letter to define the end of the range.
     * @return {string[]} An array of letters sorted in descending order up to the given letter.
     */
    function lettersBefore(letter) {
        const startCharCode = 'A'.charCodeAt(0);
        const endCharCode = letter.toUpperCase().charCodeAt(0);
        const lettersArray = [];

        for (let i = endCharCode; i >= startCharCode; i--) {
            lettersArray.push(String.fromCharCode(i).toLocaleLowerCase());
        }

        return lettersArray;
    }
    return {
        lettersBefore,
        animer(lettre) {
            this.setClasse(lettre);
            const delai = 100;
            let time = 0;

            for (const l of lettersBefore(lettre)) {
                time += delai;
                setTimeout(() => {
                    console.log(l)
                    this.setClasse(l);
                }, time)
            }
            // setTimeout(() => {
            //     demarrer("d");
            //     setTimeout(() => {
            //         demarrer("c");
            //         setTimeout(() => {
            //             demarrer("b");
            //             setTimeout(() => {
            //                 demarrer("a");
            //             }, delai);
            //         }, delai);
            //     }, delai);
            // }, delai);

        },
        setClasse(lettre) {
            lettre = lettre.toLocaleLowerCase();
            const jauge = document.querySelector(".jauge");
            const jaugeIn = jauge.querySelector("div");
            const spanLettre = jaugeIn.querySelector("span");
            jaugeIn.setAttribute(
                "style",
                "--bg:var(--" + lettre + "); --color:var(--color-" + lettre + "); --width:var(--w-" + lettre + ")"
            );
            console.log(lettre)
            spanLettre.innerHTML = lettre.toUpperCase();
        }
    }
})();