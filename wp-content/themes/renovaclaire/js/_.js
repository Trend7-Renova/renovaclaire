const App = { modules: {} };
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