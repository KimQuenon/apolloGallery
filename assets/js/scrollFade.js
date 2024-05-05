document.addEventListener("DOMContentLoaded", function() {

    const observerOptions = {
      root: null,
      rootMargin: "0px",
      threshold: 0.1,
    };
  
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("showX");
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);
  
    const elements = document.querySelectorAll(".right, .left");
    elements.forEach((element) => observer.observe(element));
  });