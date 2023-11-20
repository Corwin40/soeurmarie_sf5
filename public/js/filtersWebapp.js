const FiltersForms = document.getElementById('filters');
document.querySelectorAll('#filters input').forEach(input => {
    input.addEventListener('change', () => {
        // Détection si nature ou catégorie et son name
        const NatorCat = document.getElementById('liste')
        let type = NatorCat.dataset.type
        let name = NatorCat.dataset.name
        console.log(type, name)
        // j'intercepte les clics et ses données.
        const Form = new FormData(FiltersForms);
        // construction de la "QueryString"
        const Params = new URLSearchParams();
        // alimentation de la "QueryString"
        Form.forEach((value,key) => {
            Params.append(key, value);
        })

        // Construction de l'adresse url pour le controlleur et transmission des paramètres
        const url = '/gestapp/product/filterwebapp';
        axios
            .get(url + "?" + type + '=' + name + '&' + Params.toString())
            .then(response => {
                // rafraichissement du tableau
                const liste = document.getElementById('liste').innerHTML = response.data.liste;
                // Ajout d'un event sur Bouton de suppression dans la fenêtre modale
                document.querySelectorAll('a.page-link').forEach(function(link){
                    link.addEventListener('click', onClickPage)
                })
            })

    });
});

// Construction de la fonction OnClickBtnMenu
function onClickPage(event){
    event.preventDefault()
    const url = this.href;
    const Params = url.split('?')[1]
    const page = url.split('?page=')[1]

    const NatorCat = document.getElementById('liste')
    let type = NatorCat.dataset.type
    let name = NatorCat.dataset.name
    // Construction de l'adresse url pour le controlleur et transmission des paramètres
    const axiosURL = '/gestapp/product/filterwebapp';

    if(page === undefined){
        axios
            .get(axiosURL + "?" + type + '=' + name + '&' + Params.toString())
            .then(response => {
                // rafraichissement du tableau
                const liste = document.getElementById('liste').innerHTML = response.data.liste;
                // Ajout d'un event sur Bouton de suppression dans la fenêtre modale
                document.querySelectorAll('a.page-link').forEach(function(link){
                    link.addEventListener('click', onClickPage)
                })
            })
    }else{
        axios
            .get(axiosURL + "?" + type + '=' + name + '&' + Params.toString())
            .then(response => {
                // rafraichissement du tableau
                const liste = document.getElementById('liste').innerHTML = response.data.liste;
                // Ajout d'un event sur Bouton de suppression dans la fenêtre modale
                document.querySelectorAll('a.page-link').forEach(function(link){
                    link.addEventListener('click', onClickPage)
                })
            })
    }
}

// Ajout d'un event sur le lien de pagination
document.querySelectorAll('a.page-link').forEach(function(link){
    link.addEventListener('click', onClickPage)
})


