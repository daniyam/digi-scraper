jQuery(function($) {

    /*var viewportWidth = $(window).width();

    if (viewportWidth > 1400) {

        $('#wrapper').load('/ajax/largeScreen.php');

    }*/
//jQuery time
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches
    var progress_checker;
    var step_check = 0;

    $(".next").click(function(){

        current_fs = $(this).parent();
        next_fs = $(this).parent().next();
        fs_height = next_fs.height();
        fs_height = fs_height + 44 + 100 ;
        next_fs.parent('form').css('height',fs_height);

        data_section_attr = $(this).parent().attr("data-section");
        ajurl = DGscraper_js_object.wait_msg;
        if(data_section_attr == 'scraping_links')
        {
           var p_single_links = $('#p-single-links').val();
           var p_list_links = $('#p-list-links').val();
           var nonce = $('#scraping_link_nonce').val();
            var data = {
                'action': 'digi_get_user_scraping_links',
                'p_single_links': p_single_links,
                'p_list_links': p_list_links,
                'nonce': nonce,
            };

            $.ajax({
                dataType: 'JSON',
                url: DGscraper_js_object.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function(){
                    // Show image container
                    $('.overlay_scraping_links').css('display','block');
                },
                success: function (response) {

                    if( response.success )
                    {
                        if(response.data.single_invalid == 1)
                        {
                            alert( 'حداقل یکی از لینک های محصول وارد شده نا معتبر است!' );
                        }
                        if(response.data.list_invalid == 1)
                        {
                            alert( 'حداقل یکی از لینک های لیست محصولات وارد شده نا معتبر است!' );
                        }
                        if(response.data.links_is_ok == 1)
                        {
                            if(animating) return false;
                            animating = true;
                            //activate next step on progressbar using the index of next_fs
                            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

                            //show the next fieldset
                            next_fs.show();
                            //hide the current fieldset with style
                            current_fs.animate({opacity: 0}, {
                                step: function(now, mx) {
                                    //as the opacity of current_fs reduces to 0 - stored in "now"
                                    //1. scale current_fs down to 80%
                                    scale = 1 - (1 - now) * 0.2;
                                    //2. bring next_fs from the right(50%)
                                    left = (now * 50)+"%";
                                    //3. increase opacity of next_fs to 1 as it moves in
                                    opacity = 1 - now;
                                    current_fs.css({'transform': 'scale('+scale+')'});
                                    next_fs.css({'left': left, 'opacity': opacity});
                                },
                                duration: 800,
                                complete: function(){
                                    current_fs.hide();
                                    animating = false;
                                },
                                //this comes from the custom easing plugin
                                easing: 'easeInOutBack'
                            });
                        }
                    }
                    else
                    {
                        alert( 'شما هیچگونه لینک معتبری را وارد نکردید!' );
                    }

                },
                error: function (data) {
                    alert('error');
                },
                complete:function(data){
                    $('.overlay_scraping_links').css('display','none');
                }
            });
        }
        if(data_section_attr == 'scraping_config')
        {
            var index_image_opt = $('#scrap-index-image').val();
            var gallery_image_opt = $('#scrap-gallery-image').val();
            var count_of_gallery_image_opt = $('#scrap-count-of-gallery-image').val();
            var remove_watermark_image_opt = $('#remove-watermark-image').val();
            var product_price_opt = $('#scrap-product-price').val();
            var short_description_opt = $('#scrap-short-description').val();
            var description_opt = $('#scrap-description').val();
            var properties_tab_opt = $('#scrap-properties-tab').val();
            var important_properties_opt = $('#scrap-important-properties').val();
            var properties_opt = $('#scrap-properties').val();
            var product_code_opt = $('#scrap-product-code').val();
            var update_future_price_opt = $('#possibility-update-future-price').val();
            var nonce = $('#scraping_conf_nonce').val();
            var data = {
                'action': 'digi_get_user_scraping_config',
                'nonce': nonce,
                'index_image_opt': index_image_opt,
                'gallery_image_opt': gallery_image_opt,
                'count_of_gallery_image_opt': count_of_gallery_image_opt,
                'remove_watermark_image_opt': remove_watermark_image_opt,
                'product_price_opt': product_price_opt,
                'short_description_opt': short_description_opt,
                'description_opt': description_opt,
                'properties_tab_opt': properties_tab_opt,
                'important_properties_opt': important_properties_opt,
                'properties_opt': properties_opt,
                'product_code_opt': product_code_opt,
                'update_future_price_opt': update_future_price_opt,
            };

            $.ajax({
                dataType: 'JSON',
                url: DGscraper_js_object.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function(){
                    // Show image container
                    $('#scraping_config').css('display','block');
                },
                success: function (response) {

                    if( response.success )
                    {
                            if(animating) return false;
                            animating = true;
                            //activate next step on progressbar using the index of next_fs
                            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

                            //show the next fieldset
                            next_fs.show();
                            //hide the current fieldset with style
                            current_fs.animate({opacity: 0}, {
                                step: function(now, mx) {
                                    //as the opacity of current_fs reduces to 0 - stored in "now"
                                    //1. scale current_fs down to 80%
                                    scale = 1 - (1 - now) * 0.2;
                                    //2. bring next_fs from the right(50%)
                                    left = (now * 50)+"%";
                                    //3. increase opacity of next_fs to 1 as it moves in
                                    opacity = 1 - now;
                                    current_fs.css({'transform': 'scale('+scale+')'});
                                    next_fs.css({'left': left, 'opacity': opacity});
                                },
                                duration: 800,
                                complete: function(){
                                    current_fs.hide();
                                    animating = false;
                                },
                                //this comes from the custom easing plugin
                                easing: 'easeInOutBack'
                            });
                    }

                },
                error: function (data) {
                    alert('error');
                },
                complete:function(data){
                    $('#scraping_config').css('display','none');
                }
            });

        }
        /*
        $('.vpe-ajax-loader').show();
        $('.vpe-ajax-loader-message').text('Loading Variants....');
        $('html,body').animate({
            scrollTop: $('.vpe_table_responsive').offset().top - 90
        }, 1000);*/




    });

    $(".previous").click(function(){
        if(animating) return false;
        animating = true;

        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();
        fs_height = previous_fs.height();
        fs_height = fs_height + 44 + 100 ;
        previous_fs.parent('form').css('height',fs_height);

        //de-activate current step on progressbar
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

        //show the previous fieldset
        previous_fs.show();
        //hide the current fieldset with style
        current_fs.animate({opacity: 0}, {
            step: function(now, mx) {
                //as the opacity of current_fs reduces to 0 - stored in "now"
                //1. scale previous_fs from 80% to 100%
                scale = 0.8 + (1 - now) * 0.2;
                //2. take current_fs to the right(50%) - from 0%
                left = ((1-now) * 50)+"%";
                //3. increase opacity of previous_fs to 1 as it moves in
                opacity = 1 - now;
                current_fs.css({'left': left});
                previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
            },
            duration: 800,
            complete: function(){
                current_fs.hide();
                animating = false;
            },
            //this comes from the custom easing plugin
            easing: 'easeInOutBack'
        });
    });


    $('.submit').click( function() {
        data_section_attr = $(this).parent().attr("data-section");
        if(data_section_attr == 'scraping_import')
        {
            var marketing_enable = $('#scrap-marketing-enable').val();
            var show_featured_image_directly = $('#show-featured-image-directly').val();
            var show_gallery_image_directly = $('#show-gallery-image-directly').val();
            var marketing_link_value = $('#marketing_link_value').val();
            var selected_category = $('#product_insert_to_categories').val();
            var products_status = $('#products-status').val();
            var import_speed = $('#import-speed').val();
            var nonce = $('#scraping_imp_nonce').val();
            var before_title = $('#before_title').val();
            var after_title = $('#after_title').val();
            var dgs_scrape_title_regex_finds  = $('input[name="dgs_scrape_title_regex_finds[]"]').map(function(){return $(this).val();}).get();
            var dgs_scrape_title_regex_replaces  = $('input[name="dgs_scrape_title_regex_replaces[]"]').map(function(){return $(this).val();}).get();
            var dgs_product_tags = $('#dgs-product-tags').val();
            var dgs_scrape_content_regex_finds  = $('input[name="dgs_scrape_content_regex_finds[]"]').map(function(){return $(this).val();}).get();
            var dgs_scrape_content_regex_replaces  = $('input[name="dgs_scrape_content_regex_replaces[]"]').map(function(){return $(this).val();}).get();

            var data = {
                'action': 'digi_start_scrap',
                'nonce': nonce,
                'marketing_enable': marketing_enable,
                'marketing_link_value': marketing_link_value,
                'show_featured_image_directly': show_featured_image_directly,
                'show_gallery_image_directly': show_gallery_image_directly,
                'selected_category': selected_category,
                'products_status': products_status,
                'import_speed': import_speed,
                'before_title': before_title,
                'after_title': after_title,
                'dgs_scrape_title_regex_finds': dgs_scrape_title_regex_finds,
                'dgs_scrape_title_regex_replaces': dgs_scrape_title_regex_replaces,
                'dgs_product_tags': dgs_product_tags,
                'dgs_scrape_content_regex_finds': dgs_scrape_content_regex_finds,
                'dgs_scrape_content_regex_replaces': dgs_scrape_content_regex_replaces,
            };

            $.ajax({
                dataType: 'JSON',
                url: DGscraper_js_object.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function(){
                    // Show image container
                    $('.overlay_scraping_links').css('display','block');
                },
                success: function (response) {

                    if( response.success )
                    {
                      $('.verfi-excute').css('display','block');
                      $('.verfi-excute').next().css('display','none');

                    }

                },
                error: function (data) {
                    alert('error');
                },
                complete:function(data){
                }
            });

        }

    });

    $.fn.scrap_finish = function () {
        $('.pop-up').fadeOut(700);
        $('#overlay').removeClass('blur-in');
        $('#overlay').addClass('blur-out');
        $('#info-msg').css('display','block');
        $('.action-btn').css('display','block');
        $('.progress-text').css('display','block');
        $('#success-msg').css('display','none');
        if(progress_checker)
        {
            clearInterval(progress_checker);
        }
    }

    $.fn.progress_status = function () {
        step_check++;
        var nonce = $('#scraping_imp_nonce').val();
        var data = {
            'action': 'digi_progress_status_checker',
            'nonce': nonce,
            'step_check': step_check,
        };

        $.ajax({
            dataType: 'JSON',
            url: DGscraper_js_object.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function(){

            },
            success: function (response) {

                if( response.success )
                {
                    var percent = response.data.rate_of_progress;
                    $('.horizontal .progress-fill').css('width', percent);
                    $('.horizontal .progress-fill span').html(percent);
                    $('.progress_product_name').html(response.data.importing_product_name);
                    if(response.data.progress_runinng == 0)
                    {
                        percent = '100%';
                        $('.horizontal .progress-fill').css('width', percent);
                        $('.horizontal .progress-fill span').html(percent);
                        $('#info-msg').css('display','none');
                        $('.action-btn').css('display','none');
                        $('.progress-text').css('display','none');
                        $('#success-msg').css('display','block');
                        clearInterval(progress_checker);
                        setTimeout($(this).scrap_finish , 6000);
                    }
                    $('.overlay_scraping_links').css('display','none');
                     $('.pop-up').fadeIn(700);
                     $('#overlay').addClass('blur-in');
                     $('#overlay').removeClass('blur-out');


                }

            },
            error: function (data) {
                alert('error');
            },
            complete:function(data){

            }
        });
    }

    var progress_state = $('#progress_state').val();

    if(progress_state == 1)
    {
        progress_checker = setInterval($(this).progress_status,20000);
    }

    $('.execute-cancel').click(function () {
        $('.verfi-excute').css('display','none');
        $('.overlay_scraping_links').css('display','none');
        $('.verfi-excute').next().css('display','block');
    });

    $('.execute-script').click(function () {

            var nonce = $('#scraping_imp_nonce').val();
            var data = {
                'action': 'digi_start_import',
                'nonce': nonce,
            };

            $.ajax({
                dataType: 'JSON',
                url: DGscraper_js_object.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function(){
                    // Show image container
                    $('.verfi-excute').css('display','none');
                    $('.verfi-excute').next().css('display','block');
                    $('.overlay_scraping_links').css('display','block');
                   // setTimeout($(this).progress_status,30000);
                },
                success: function (response) {

                    if( response.success )
                    {
                    }

                },
                error: function (data) {
                    alert('error');
                },
                complete:function(data){
                    $(this).progress_status();
                   progress_checker = setInterval($(this).progress_status,20000);

                }
            });



    });

    $('#stop-import').click(function (e) {

        var nonce = $('#scraping_imp_nonce').val();
        var data = {
            'action': 'digi_stop_import',
            'nonce': nonce,
        };

        $.ajax({
            dataType: 'JSON',
            url: DGscraper_js_object.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function(){
                return confirm("آیا از متوقف کردن فرآیند اسکراپ و درون ریزی اطمینان دارید؟");
            },
            success: function (response) {

                if( response.success )
                {
                }

            },
            error: function (data) {
                alert('error');
            },
            complete:function(data){

                $(this).scrap_finish();
                alert('برای شروع و یا از سرگیری یک اسکراپ جدید حداقل دو دقیقه صبر کنید ، تا فرایند توقف در پس زمینه کامل شود!');

            }
        });
    });

    $(".scrap-remove").click(function(e) {
        e.preventDefault();
        var scrap_name = $(this).attr('data-scrap');

        var data = {
            'action': 'digi_remove_scrap',
            'scrap_name': scrap_name,
        };

        $.ajax({
            dataType: 'JSON',
            url: DGscraper_js_object.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function(){
            },
            success: function (response) {

                if( response.success )
                {
                   var scrap_row = '#'+scrap_name;
                    $(scrap_row).css('display','none');
                }

            },
            error: function (data) {
                alert('error');
            },
            complete:function(data){
            }
        });
    });

    $(".scrap-resume").click(function(e) {
        e.preventDefault();
        var scrap_name = $(this).attr('data-resume');

        var data = {
            'action': 'digi_resume_scrap',
            'scrap_name': scrap_name,
        };

        $.ajax({
            dataType: 'JSON',
            url: DGscraper_js_object.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function(){
                $('.ajax-load').css('display','block');
            },
            success: function (response) {

                if( response.success )
                {
                    if(response.data.other_task_running == 1)
                    {
                       alert('هم اکنون اسکراپ دیگری در حال انجام است !');
                    }
                    else
                    {
                        window.location.href = response.data.redirect_link;
                    }

                }

            },
            error: function (data) {
                alert('error');
            },
            complete:function(data){
                $('.ajax-load').css('display','none');
            }
        });
    });



    container_advance_tab = $('.container_advance_tab');
    $.fn.toggleShowAdvanceTab = function () {
       if(container_advance_tab.hasClass("slide-down"))
       {
           $('#dgs-advance').removeClass("fa fa-check");
           $('#dgs-advance').empty();
           $('#dgs-advance').text('پیشرفته');
           container_advance_tab.removeClass("slide-down");
       }
       else
       {
           $('#dgs-advance').addClass('fa fa-check');
           $('#dgs-advance').empty();
           $('#dgs-advance').text('تایید');
           container_advance_tab.addClass("slide-down");

       }
    }
    $('#dgs-advance').on("click", $(this).toggleShowAdvanceTab);



});

jQuery(document).ready(function ($) {

    $('#scrap-marketing-enable').on('click', function() {
        if ($(this).is(':checked')) {
            $(this).attr('value',1);
           // $('.marketing_link_value').css('display','block');
        }
        else {
            $(this).attr('value',0);
           // $('.marketing_link_value').css('display','none');
        }
    });
    $('.form-check-input').on('click', function() {
        if ($(this).is(':checked')) {
            $(this).attr('value',1);
        }
        else {
            $(this).attr('value',0);
        }
    });

});

jQuery(document).ready(function(){

    jQuery(".chzn-select").chosen({
        no_results_text: "نتیجه ای یافت نشد!",
        width: "100%",
        rtl: true
    })

    jQuery.switcher();

});

angular
    .module('dgsApp', [])
    .controller('dgsCtrl', function(
        $scope,
        $compile,
        $timeout
    ){
        $scope.add_field = function($event, type) {

            if (type == 'title_regex') {
                var content = $compile(
                    '<div class="form-group">' +
                    '<div class="col-sm-12">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon">' + 'پیدا کن' + '</div>' +
                    '<input type="text" name="dgs_scrape_title_regex_finds[]" placeholder="' + 'مثال یافتن' + '" class="form-control">' +
                    '<span class="input-group-btn"><button type="button" class="btn btn-primary btn-block" ng-click="remove_field($event)"><i class="fa fa-trash"></i></button></span>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-sm-12">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon">' + 'جایگزین کردن' + '</div>' +
                    '<input type="text" name="dgs_scrape_title_regex_replaces[]" placeholder="' + 'مثال جایگزین کردن' + '" class="form-control">' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                )($scope);

                $event.target.closest('.form-group').before(content[0]);
            }

            if (type == 'content_regex') {

                var content = $compile(
                    '<div class="form-group">' +
                    '<div class="col-sm-12">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon">' + 'پیدا کن' + '</div>' +
                    '<input type="text" name="dgs_scrape_content_regex_finds[]" placeholder="' + 'مثال یافتن' + '" class="form-control">' +
                    '<span class="input-group-btn"><button type="button" class="btn btn-primary btn-block" ng-click="remove_field($event)"><i class="fa fa-trash"></i></button></span>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-sm-12">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon">' + 'جایگزین کردن' + '</div>' +
                    '<input type="text" name="dgs_scrape_content_regex_replaces[]" placeholder="' + 'مثال جایگزین کردن' + '" class="form-control">' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                )($scope);

                $event.target.closest('.form-group').before(content[0]);
            }
        };

        $scope.remove_field = function($event) {
            $event.target.closest('.form-group').remove();
        };


    });

