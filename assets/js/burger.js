const burger = document.querySelector("#burger")
const menuMobile = document.querySelector("#menu")
const linksMenu = document.querySelectorAll('#menu nav ul li a')
console.log(linksMenu)

// event button burger
burger.addEventListener('click',()=>{
    burger.classList.toggle('open-burger')
    menuMobile.classList.toggle('open-menu')
})

// event links menu
linksMenu.forEach(link =>{
    link.addEventListener('click',()=>{
        burger.classList.remove('open-burger')
        menuMobile.classList.remove('open-menu')
    })
})