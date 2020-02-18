jQuery(function($) {

$(document).ready(function() {
    
    $('.open-popup-link').click(function(e){
        e.preventDefault();
        var gallery_title = $(this).data('slidertitle');
        var gallery_post_id = $(this).data('loadslider');

        $.magnificPopup.open({
            mainClass: 'mfp-fade tds-gallery',
            items: {
                type: 'ajax',
                src: ajaxfrontendurl.ajax_url
            },
            ajax: {
                settings: {
                    type: 'POST',
                    data: {
                        action: 'tds_load_slider_images',
                        gallery_title: gallery_title,
                        gallery_post_id: gallery_post_id,
                    }
                }
            },
            callbacks: {
                parseAjax: function(mfpResponse) {
                    console.log(mfpResponse.data);
                    mfpResponse.data = mfpResponse.data.html;
                }, 
                ajaxContentAdded: function() {
                    initializeFlexSlider(gallery_post_id);
                }
            }
        });

    });

    function initializeFlexSlider(gallery_id) {
        // The slider being synced must be initialized first

        $('#carousel-' + gallery_id).flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: true,
            slideshow: false,
            itemWidth: 100,
            itemMargin: 5,
            asNavFor: '#gallery-' + gallery_id
        });
        
        $('#gallery-' + gallery_id).flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: true,
            slideshow: false,
            sync: '#carousel-' + gallery_id
        });
    }

    $('.open-iframe-link').click(function(e){
        e.preventDefault();
        var iframe_src = $(this).attr('href');

        var iframe_options = {
            type: 'iframe',
            items: {
                src: iframe_src,
            },
            iframe: {
                markup: '<div class="tds-packages-iframe block-review">'+
                        '<div class="mfp-close"></div>'+
                        '<iframe align="center" class="mfp-iframe" width="90%" height="90%" frameborder="0"></iframe>'+
                        '</div>',
            },
            mainClass: 'mfp-custom-iframe',
        }
        $.magnificPopup.open(iframe_options);

        $('.open-iframe-link').magnificPopup(option);
    });


}); //end $(document).ready(function()


});
