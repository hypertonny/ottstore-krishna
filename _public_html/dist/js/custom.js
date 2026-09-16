$( document ).ready(function() {
	$('.portfolio-slider').owlCarousel({
        loop:false,
		center:false,
        margin:100,
		autoplay:true,
        items:2,
        dots: false,
        nav:false,
        responsiveClass:true,
        responsive:{
            0:{
                items:2,
                nav:false
            },
            600:{
                items:2,
                nav:false
            },
            991:{
                items:2,
                nav:false
            },
            1024:{
                items:2,
                nav:false
            }
        }
    });

    $('.testimonial').owlCarousel({
        loop:true,
		center:true,
		autoplay:true,
        items:1,
        dots: false,
        margin:10,
        nav:false,
        responsiveClass:true,
        responsive:{
            0:{
                items:1,
                nav:false
            },
            600:{
                items:1,
                nav:false
            },
            991:{
                items:1,
                nav:false
            },
            1024:{
                items:1,
                nav:false
            }
        }
    });

    $('.trusted-logos').owlCarousel({
        loop:false,
        items:4,
        dots: false,
        autoplay:false,
        autoplayHoverPause:true,
        nav:false,
        responsiveClass:true,
        responsive:{
            0:{
                items:2,
                nav:false
            },
            600:{
                items:3,
                nav:false
            },
            991:{
                items:3,
                nav:false
            },
            1024:{
                items:4,
                nav:false
            }
        }
    });

    

    $(window).scroll(function() {
		if ($(this).scrollTop() > 50){  
			$('header').addClass("sticky");
		}
		else{
			$('header').removeClass("sticky");
		}
	});
   // copyrights Year Auto-update
     function newDate() {
     return new Date().getFullYear();
     }
     document.onload = document.getElementById("autodate").innerHTML =  + newDate();

});

(function() {
    try {
        console.log(
            "%c ✨ Site Craft & Architecture %c Crafted by @hypertonny ",
            "background: #6366f1; color: #ffffff; font-size: 12px; font-weight: 700; padding: 4px 8px; border-radius: 4px 0 0 4px;",
            "background: #0f172a; color: #38bdf8; font-size: 12px; font-weight: 700; padding: 4px 8px; border-radius: 0 4px 4px 0; border: 1px solid #334155;"
        );
        console.log(
            "%c🐙 GitHub:   %c@hypertonny (https://github.com/hypertonny)\n%c✈️ Telegram: %c@bnh_02 (https://t.me/bnh_02)",
            "color: #a855f7; font-weight: bold; font-size: 12px;",
            "color: #94a3b8; font-size: 12px;",
            "color: #0ea5e9; font-weight: bold; font-size: 12px;",
            "color: #94a3b8; font-size: 12px;"
        );
        console.log("The site is crafted by @hypertonny | GitHub: @hypertonny | Telegram: @bnh_02");
    } catch (e) {}
})();

