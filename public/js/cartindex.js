const listCart = document.getElementById("listCart");
const adressCart = document.getElementById("adressCart");
const btnNextStep = document.getElementById("nextStep");
const btnPreviousStep = document.getElementById("previousStep");
const inputQtySigMarq = document.getElementById("inputQtySigMarq").value
const btnConfPurchase = document.getElementById("BtnConfPurchase")

btnNextStep.onclick = function(event){
    event.preventDefault()
    adressCart.classList.remove("d-none")
    listCart.classList.add("d-none")
    if(btnConfPurchase != null){
        btnConfPurchase.classList.remove("d-none")
    }
    btnPreviousStep.removeAttribute("disabled")
    btnNextStep.classList.add("d-none")
}

btnPreviousStep.onclick = function(event){
    event.preventDefault()
    listCart.classList.remove("d-none");
    adressCart.classList.add("d-none")
    if(btnConfPurchase != null){
    btnConfPurchase.classList.add("d-none")
    }
    btnPreviousStep.setAttribute("disabled", true)
    btnNextStep.classList.remove("d-none")
}

if(btnConfPurchase != null){
    btnConfPurchase.onclick = function(event){
        event.preventDefault()
        let PhoneContact = document.getElementById('cart_confirmation_phoneContact')
        if(PhoneContact.value !== ''){
            let formPurchase = document.getElementById("cart_confirmation")
            let urlPurchase = formPurchase.action
            let dataPurchase = new FormData(formPurchase)
            formPurchase.submit()
        }else{
            alert("Veuillez renseigner un Numéro de contact pour recevoir le colis en cas d'abscence.")
        }
    }
}


// Edition de la personnalisation d'un produit
function customEdit(event){
    event.preventDefault()
    let formCustom = this.parentElement.parentElement.parentElement.parentElement.parentElement
    let urlCustom = formCustom.action
    let data = new FormData(formCustom)
    const formCustomdata = JSON.stringify(Object.fromEntries(data));
    axios
        .post(urlCustom, formCustomdata)
        .then(function(response){
            // préparation du toaster
            var option = {
                animation: true,
                autohide: true,
                delay: 3000,
            };
            // initialisation du toaster
            var toastHTMLElement = document.getElementById("toaster");
            var message = response.data.message;
            var toastBody = toastHTMLElement.querySelector('.toast-body') // selection de l'élément possédant le message
            toastBody.textContent = message;
            var toastElement = new bootstrap.Toast(toastHTMLElement, {animation: true, autohide: true, delay: 3000});
            toastElement.show();
            // Appel de la fonction d'incrémentation d'un produit
            document.querySelectorAll('a.js-increment').forEach(function (link){
                link.addEventListener('click', onClickBtnIncrement);
            })
            // Appel de la fonction de décrementation d'un produit
            document.querySelectorAll('a.js-decrement').forEach(function (link){
                link.addEventListener('click', onClickBtnDecrement);
            })
            // Appel de la fonction de duplication d'un produit
            document.querySelectorAll('a.duplicate').forEach(function (link){
                link.addEventListener('click', onClickDuplicate);
            })
            // Appel de la fonction de duplication d'un produit
            document.querySelectorAll('button.FormCustomization').forEach(function (link){
                link.addEventListener('click', customEdit);
            })
        })
}

// Construction de la fonction "onClickBtnIncrement" : incrémentation d'un produit
function onClickBtnIncrement(event){
    event.preventDefault();
    const url = this.href;
    axios
        .post(url)
        .then(function(response) {
            window.location.reload()
        })
}

// Construction de la fonction "onClickBtnIncrement" : incrémentation d'un produit
function onClickBtnDecrement(event){
    event.preventDefault();
    const url = this.href;
    axios
        .post(url)
        .then(function(response) {
            window.location.reload()
        })
}


// fonction de duplication de la ligne
function onClickDuplicate(event){
    event.preventDefault();
    const url = this.href;
    axios
        .post(url)
        .then(function(response) {
            window.location.reload()
        });
}

// Appel de la fonction d'incrémentation d'un produit
document.querySelectorAll('a.js-increment').forEach(function (link){
    link.addEventListener('click', onClickBtnIncrement);
})
// Appel de la fonction de décrementation d'un produit
document.querySelectorAll('a.js-decrement').forEach(function (link){
    link.addEventListener('click', onClickBtnDecrement);
})
// Appel de la fonction de duplication d'un produit
document.querySelectorAll('a.duplicate').forEach(function (link){
    link.addEventListener('click', onClickDuplicate);
})
// Appel de la fonction de duplication d'un produit
document.querySelectorAll('button.FormCustomization').forEach(function (link){
    link.addEventListener('click', customEdit);
})