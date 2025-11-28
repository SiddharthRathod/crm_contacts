function startLoaderAjax(){
    $("body").append("<div id='ajax_loader_42160' style='background:black;opacity:0.7;position:fixed;top:0;bottom:0;width:100%;height:100%;z-index:99999999;display:none;'><div style='margin:200px auto; width:300px;'><img width='300' src='"+SITE_IMG_JS+"/loading.gif' alt=''></div></div>");
    $("#ajax_loader_42160").show();
}
function stopLoaderAjax(){
    $("#ajax_loader_42160").hide();
    $("#ajax_loader_42160").remove();
}
function ajaxCall(url,param){
    startLoaderAjax();
    var tmpData="";
    $.ajax({
        type: "POST",
        url: url,
        async:false,
        data: param,
        processData: false,
        contentType: false,
        success: function(data){
            try {
                tmpData = typeof data === 'string' ? JSON.parse(data) : data;
            } catch(e) {
                console.error('JSON parse error:', e);
                tmpData = data;
            }
        }
    });
    stopLoaderAjax();
    return tmpData;
}
