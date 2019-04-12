/**
 * @author: effone
 * @license: MIT
 */

$(function () {
    var data = {
            "error": "Unable to locate.",
            "ajax": parseInt(use_xmlhttprequest),
            "secure": location.protocol == "https:" ? true : false,
            "fields": "country,regionName,city,district,zip,lat,lon,timezone,isp,as,reverse,mobile,proxy,query,status,message",
        },
        field = {
            "country": "Country",
            "city": "City",
            "regionName": "Region",
            "district": "District",
            "timezone": "Timezone",
            "zip": "Zip Code",
            "lat": "Latitude",
            "lon": "Longitude",
            "isp": "Service Provider",
            "reverse": "Reverse DNS",
            "as": "Autonomous System Number and Name",
            "mobile": "Cellular Connection",
            "proxy": "Proxy"
        };

    if (!data.secure || (data.secure && data.ajax)) {
        $('.iplocate').show();
        $('.iplocate').on('click', function (e) {
            e.preventDefault();
            var target = $(this);
            var ip = $.trim(target.prev('.ip').text());

            console.log(localStorage.getItem(ip));
            if ($('#location').attr("data-ip") != ip) {
                $('#location').slideUp('fast');
                var bank = data.secure ? rootpath + "/xmlhttp.php?action=get_iplocation&ip=" + ip + "&my_post_key=" + my_post_key + "&fields=" + data.fields : "http://ip-api.com/json/" + ip + "?fields=" + data.fields;

                ajaxCall(bank, function (result) {
                    if (typeof result.errors === 'undefined' && result.status === "success") {
                        //var message = (typeof result == 'object') ? (result.country + ', ' + result.regionName + ', ' + result.city) : data.error;
                        //target.before('[' + message + ']').remove();
                        var data = ""

                        $.each(field, function (param, datum) {
                            if ($.trim(result[param]) != "") data += '<div><span style="color: #888;">' + datum + ': </span>' + result[param] + '</div>';
                        });
                        $('#ipdata').html(data);
                        $('#map').html('<iframe src="//maps.google.com/maps?q=' + result.lat + ',' + result.lon + '&z=10&output=embed" style="width: 100%; height: 100%; border: none;"></iframe></div>');
                        $('#location').attr("data-ip", result.query);
                        localStorage.setItem(result.query, JSON.stringify(result));
                        $('#location').slideDown('fast');
                    } else {
                        $.each(result.errors, function (i, message) {
                            $.jGrowl("Error" + ' ' + message, {
                                theme: 'jgrowl_error'
                            });
                        });
                    }
                });
            }
        });

        function ajaxCall(bank, callback) {
            $.ajax({
                url: bank,
                dataType: "json",
                cache: true,
                success: function (result) {
                    callback(result);
                }
            });
        }
    }
});