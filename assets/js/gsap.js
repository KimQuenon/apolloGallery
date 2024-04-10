gsap.registerPlugin(ScrollTrigger);

        document.addEventListener('DOMContentLoaded', () => {
            const contentHolderHeight = document.querySelector('.content-holder').offsetHeight;
            const imgHolderHeight = window.innerHeight;
            const additionalScrollHeight = window.innerHeight;

            const totalBodyHeight = contentHolderHeight + imgHolderHeight + additionalScrollHeight;
            document.body.style.height = `${totalBodyHeight}px`;
        });

        ScrollTrigger.create({
            trigger: ".website-content",
            start: "-0.1% top",
            end: "bottom bottom",
            onEnter: () =>{
                gsap.set(".website-content", {position: 'absolute', top:"200%"});
            },
            onLeaveBack: () =>{
                gsap.set(".website-content", {position: 'fixed', top:"0"});
            },
        })

        gsap.to(".header .letters:first-child",{
            x:()=> -innerWidth *3,
            scale: 10,
            ease: "power2.inOut",
            scrollTrigger: {
                start: "top top",
                end: `+=200%`,
                scrub:1
            }
        });

        gsap.to(".scroll", {
            y: () => window.innerHeight * 3,
            scale: 1,
            ease: "power2.inOut",
            scrollTrigger: {
                trigger: ".scroll",
                start: "bottom bottom",
                end: `+=50%`,
                scrub: 1,
            }
        });

        // gsap.to(".header .letters:last-child",{
        //     x:()=> innerWidth *3,
        //     scale: 10,
        //     ease: "power2.inOut",
        //     scrollTrigger: {
        //         start: "top top",
        //         end: `+=200%`,
        //         scrub:1
        //     }
        // });

        gsap.to(".img-holder",{
            scale: 2.5,
            clipPath: 'polygon(0 0, 100% 0, 100% 100%, 0 100%)',
            ease: "power2.inOut",
            scrollTrigger: {
                start: "top top",
                end: `+=200%`,
                scrub:1
            }   
        });

        gsap.to(".img-holder img",{
            scale: 1,
            ease: "power2.inOut",
            scrollTrigger: {
                start: "top top",
                end: `+=200%`,
                scrub:1
            }   
        });
        