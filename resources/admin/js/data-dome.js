(function($, dd){
    $(document).ready(function(e){
        $("#dd-refresh").on("click", function(e1){
            e1.preventDefault();
            $("#detect-msg").show();
            $("#refresh-form").submit();
        });

        $("#toplevel_page_" + dd["slug"]).find("a[href='__data_dome_1']").attr("href", dd["dashboard"]).attr("target", "_blank");

        $("#dd-submit").on("click", function(e1){
            $("#invalid-key").hide();
            var pattern = new RegExp("^[a-zA-Z0-9]{15}$")
            if(!pattern.test($("#key").val())){
                e1.preventDefault();
                $("#invalid-key").show();
            }

            $("#invalid-js-key").hide();
            var pattern = new RegExp("^[a-zA-Z0-9]{30}$")
            if(!pattern.test($("#jskey").val())){
                e1.preventDefault();
                $("#invalid-js-key").show();
            }

        });
    });
})(jQuery, dd);