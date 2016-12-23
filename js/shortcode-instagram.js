/**
 * Created by mak on 22/12/16.
 */
jQuery(document).ready( function($) {

    function adjustImageSize(ref,height,width) {
        var dimensions = {
            height: ref + 'px',
            margin: 'auto',
            'max-height': ref + 'px',
            'max-width': ref + 'px',
            width: ref + 'px'
        };
        if(width > height) {
            var w = (width * ref) / height;
            var m = (w - ref) / 2;
            dimensions = {
                height: ref + 'px',
                margin: '0 ' + m + 'px 0 -' + m + 'px',
                'max-height': ref + 'px',
                'max-width': w + 'px',
                width: w + 'px'
            }
        } else if(height > width) {
            var h = (height * ref) / width;
            var m = (h - ref) / 2;
            dimensions = {
                height: h + 'px',
                margin: '-' + m + 'px 0 ' + m + 'px 0',
                'max-height': h + 'px',
                'max-width': ref + 'px',
                width: ref + 'px'
            }
        }
        return dimensions;
    }

    function set_height() {
        var ref = $('.maks-media-section').width();
        $('.maks-media-body').height( ref + 'px' );
        $('.maks-media-image').each( function() {
            var height = $(this).height();
            var width = $(this).width();
            var adj = adjustImageSize( ref, height, width );
            $(this).css({
                height: adj['height'],
                margin: adj['margin'],
                'max-height': adj['max-height'],
                'max-width': adj['max-width'],
                width: adj['width']
            });
        });
        $('.maks-media-video').each( function() {

        });
    }

    var findAttrAndRemove = function(attr){
        var tag = $('['+attr+']');
        tag.removeAttr(attr);
        return tag;
    };

    $('[maks-json-header]').each(function(){
        var json = $(this).text();
        if(json) {
            $(this).remove();
            var header = $.parseJSON(json);

            var profile_picture = findAttrAndRemove('maks-profile-picture');
            profile_picture.attr({
                alt: header['full_name'],
                height: 152,
                src: header['profile_picture'],
                width: 152
            });
            findAttrAndRemove('maks_username').text(header['username']);
            findAttrAndRemove('maks-counts-media').text(header['counts']['media']);
            findAttrAndRemove('maks-counts-followed_by').text(header['counts']['followed_by']);
            findAttrAndRemove('maks-counts-follows').text(header['counts']['follows']);
            findAttrAndRemove('maks-full_name').text(header['full_name']);
            findAttrAndRemove('maks-bio').text(header['bio']);
            findAttrAndRemove('maks-website').attr('href', header['website']).text(header['website'].replace(/^https?\:\/\//, ''));

            profile_picture.on('load',function () {
                findAttrAndRemove('maks-header').removeAttr('style');
            }).error(function () {
                console.log('profile picture not load');
            });
        }
    });

    var media_template = $('.maks-media-section');
    var media_html = '';
    var thumbnail_class = '.maks-media-thumbnail';

    $('[maks-json-media]').each( function(index) {

        if(index % 3 == 0 || index == 0){
            media_html += '<div class="row">';
        }

        var id   = $(this).attr('id');
        var json = $(this).text();

        if(json && id) {
            var data = $.parseJSON(json);
            if(index == 0) {console.log(data);} // TODO REMOVE

            var template = media_template.clone();
            template.attr( 'id', id );

            var thumbnail = template.find(thumbnail_class);
            thumbnail.attr( 'src', data['images']['thumbnail']['url'] );

            thumbnail.load( function() {
                var section = $( '#' + id );
                var thumbnail = section.find(thumbnail_class);
                thumbnail.css( 'visibility', 'visible' );
                var caption_text = data['caption']['text'];
                section.find('.maks-media-caption-text').text(caption_text);
                var date = moment.unix( data['created_time'] ).format('dddd, MMMM Do YYYY, h:mm:ss a');
                section.find('.maks-media-created-time').text( date );
                section.find('.maks-media-likes-count').after( data['likes']['count'] );
                section.find('.maks-media-comments-count').after( data['comments']['count'] );
                var body = section.find('.maks-media-body');
                var image = section.find('.maks-media-image');
                var video = section.find('.maks-media-video');
                var ref = section.width();
                body.height( ref + 'px' );
                body.mouseenter(function () {
                    $(this).addClass('focus');
                });
                body.mouseleave(function () {
                    $(this).removeClass('focus');
                });

                if( data['type'] == 'image' ) {
                    video.remove();
                    var adj_image = adjustImageSize(
                        ref,
                        data['images']['standard_resolution']['height'],
                        data['images']['standard_resolution']['width']
                    );
                    image.attr({
                        alt: caption_text,
                        src: data['images']['standard_resolution']['url'],
                    }).css({
                        height: adj_image['height'],
                        margin: adj_image['margin'],
                        'max-height': adj_image['max-height'],
                        'max-width': adj_image['max-width'],
                        width: adj_image['width']
                    });
                    image.load( function() {
                        thumbnail.remove();
                        body.fadeIn();
                    });

                } else if( data['type'] == 'video' ) {
                    image.remove();

                    var adj_video = adjustImageSize(
                        ref,
                        data['videos']['standard_resolution']['height'],
                        data['videos']['standard_resolution']['width']
                    );
                    video.attr(
                        'src', data['videos']['standard_resolution']['url']
                    ).css({
                        height: adj_video['height'],
                        margin: adj_video['margin'],
                        'max-height': adj_video['max-height'],
                        'max-width': adj_video['max-width'],
                        width: adj_video['width']
                    });
                    video.on('play', function () {
                        thumbnail.remove();
                        body.fadeIn();
                    });
                }
            }).error( function() {
                // DO REMOVE pic => UPDATE
                // $('#'+id+' [maks-thumbnail-background]')
                console.log('cannot load, id: '+id);
            });

            media_html += template[0].outerHTML;
        }

        if((index + 1) % 3 == 0){
            media_html += '</div>';
        }
    });

    $('.maks-media').html(media_html).removeAttr('style');

    $(window).resize(set_height);

    var url_update = $('[maks-json-config]').text();
    $.getJSON( url_update+'/update.php?instagram=true',function(data) {
        console.log(data);
    });
});