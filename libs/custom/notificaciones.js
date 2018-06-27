$().ready(function () {
    $.ajax({
        url: "/notificacion/getNotificaciones",
        dataType: 'json',
        success: function (data) {
            if (data.length > 0) {
                var cant = 0;
                $.each(data, function (i, noti) {
                    if (noti.estado == 1) {
                        cant++;
                    }
                });
                if (cant > 0) {
                    $("#cantNotif").show()
                    $("#cantNotif").text(cant)
                }
            }
            $.each(data, function (index, noti) {
                var liTag = $('<li>').data("idNoti", noti.id);
                if (noti.estado == 1) {
                    liTag.addClass("noVista");
                    liTag.addClass("list-group-item-info");
                }
                var aTag = $('<a>').html(noti.mensaje);;
                var iTag = $('<i>').addClass("fa fa-circle-o text-" + noti.color);
                aTag.prepend(iTag);
                liTag.append(aTag);
                $("#menuNotif").append(liTag);
            });
        }
    });

    $("#notifications-menu").click(function () {
        $("#cantNotif").hide();
        var data = [];
        $(".noVista").each(function (i, li) {
            data.push($(li).data("idNoti"));
            $(li).removeClass("noVista");
        });
        var cant = 0;
        $.each(function (i, noti) {
            if (noti.estado == 1) {
                cant++;
            }
        });
        if (data.length > 0) {
            $.ajax({
                url: "/notificacion/limpiarNotificaciones",
                dataType: 'json',
                type: "post",
                data: {data: data}
            });
        }

    });
});
