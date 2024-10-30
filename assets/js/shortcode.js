jQuery(document).ready($ => {
    $('.products-added-list-block-wrapper.style-4').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        respondTo: 'slider',
        responsive: [{

            breakpoint: 1030,
            settings: {
                slidesToShow: 4,
                slidesToScroll: 1,
                infinite: true
            }

        }, {

            breakpoint: 770,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
                infinite: true
            }

        }, {

            breakpoint: 510,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
                infinite: true
            }

        }, {

            breakpoint: 250,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true
            }

        }, {
            breakpoint: 0,
            settings: "unslick" // destroys slick
        }],
    });

});