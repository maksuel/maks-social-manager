/**
 * Created by mak on 07/10/16.
 */

// tooltip adjustments
document.addEventListener("DOMContentLoaded", function() {
    maks_igf();
    var e = document.querySelectorAll("#maks-sm .tooltiptext");
    for(var i = 0; i < e.length; i++) {
        var left = e[i].offsetWidth / 2;
        e[i].style.marginLeft = "-"+left+"px";
    }
});

window.addEventListener('resize', maks_igf);

function maks_igf() {
    var sz = document.querySelector("#maks-sm main section div").offsetWidth;

    var div = document.querySelectorAll("#maks-sm main section div");
    var vid = document.querySelectorAll("#maks-sm main video");
    var img = document.querySelectorAll("#maks-sm main img");

    for(var i = 0; i < div.length; i++) {
        div[i].style.height = sz+"px";
    }

    maks_rsz(vid, sz);
    maks_rsz(img, sz);
}
function maks_rsz(obj, sz) {
    for(var i = 0; i < obj.length; i++) {
        var w = obj[i].offsetWidth;
        var h = obj[i].offsetHeight;
        if(w >= h) {
            var nw = (sz*w)/h;
            obj[i].style.marginLeft = "-"+(nw-sz)/2+"px";
            obj[i].style.width = nw+"px";
            obj[i].style.height = sz+"px";
        } else {
            var nh = (sz*h)/w;
            obj[i].style.marginTop = "-"+(nh-sz)/2+"px";
            obj[i].style.width = sz+"px";
            obj[i].style.height = nh+"px";
        }
    }
}