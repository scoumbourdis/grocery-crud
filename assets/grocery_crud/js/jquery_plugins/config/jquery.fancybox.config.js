$(function() {
    if ($('.image-thumbnail').length > 0) {
        $('.image-thumbnail').fancybox({
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 600,
            'speedOut': 200,
            'overlayShow': false
        });
    }
});	