<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true" id="pswpElement">
    <!-- Background of PhotoSwipe. 
         It's a separate element as animating opacity is faster than rgba(). -->
    <div class="pswp__bg"></div>

    <!-- Slides wrapper with overflow:hidden. -->
    <div class="pswp__scroll-wrap">

        <!-- Container that holds slides. 
            PhotoSwipe keeps only 3 of them in the DOM to save memory.
            Don't modify these 3 pswp__item elements, data is added later on. -->
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>

        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
        <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

                <!--  Controls are self-explanatory. Order can be changed. -->

                <div class="pswp__counter"></div>

                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

                <button class="pswp__button pswp__button--share" title="Share"></button>

                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                <!-- Preloader demo https://codepen.io/dimsemenov/pen/yyBWoR -->
                <!-- element will get class pswp__preloader--active when preloader is running -->
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div> 
            </div>

            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>

            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>

            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>

        </div>

    </div>
</div>
@push('after_styles')
<link rel="stylesheet" href="{{asset('packages/photoswipe/photoswipe.css')}}"> 
<link rel="stylesheet" href="{{asset('packages/photoswipe/default-skin/default-skin.css')}}"> 
@endpush
@push('after_scripts')
<script src="{{asset('packages/photoswipe/photoswipe.min.js')}}"></script> 
<script src="{{asset('packages/photoswipe/photoswipe-ui-default.min.js')}}"></script> 
<script>
    //  $(document).on('onCloseAfter.lg', function(event) {
    //     $(document).data('lightGallery').destroy(true);
    // })
    $('#pswpElement').appendTo($('body'));
    $('body').on('click', 'div.image-preview', function(e){
        e.stopImmediatePropagation();
        var path = $(this).find('img').attr('src');
        // console.log(path);
        var slides = [
            {
                src: path,
                w: 0,
                h: 0
            }
        ];
        var pswpElement = document.getElementById("pswpElement");
        var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, slides, {closeOnScroll: false, closeOnVerticalDrag: false, pinchToClose: false, 
        shareButtons: [{id:'download', label:'Download image', url:'@{{raw_image_url}}', download:true}]});
        gallery.listen('imageLoadComplete', function (index, item) {
            if (item.h < 1 || item.w < 1) {
                let img = new Image()
                img.onload = () => {
                item.w = img.width
                item.h = img.height
                gallery.invalidateCurrItems()
                gallery.updateSize(true)
                }
                img.src = item.src
            }
        });
        gallery.listen('close', function(){
            $('body').css('overflow-y', 'auto');
        });
        gallery.listen('afterInit', function(){
            $('body').css('overflow-y', 'hidden');
        })
        gallery.init();
    });
</script>
@endpush